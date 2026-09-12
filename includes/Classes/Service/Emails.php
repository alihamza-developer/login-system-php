<?php

namespace Service;

use Core\App;
use Core\Database;

class Emails extends Database
{
    private $db;
    private $base_file;
    private $base_vars;

    # Constructor
    public function __construct()
    {
        $this->db = App::db();
        $this->base_file = "base-structure";
        $this->base_vars = [
            'site_name' => SITE_NAME,
            'site_url' => SITE_URL,
            'login_url' => merge_path(SITE_URL, "login"),
            'site_email' => CONTACT_EMAIL,
            'site_logo_url' => url('images/logo-with-name.png?v=1.0'),
            'site_initial' => strtoupper(substr(SITE_NAME, 0, 1)),
        ];
    }

    # Replace variables in string
    function replace_vars($str, $vars = [], $is_email_body = false)
    {
        foreach ($vars as $var => $value) {
            $var = strtolower($var);
            if (!$is_email_body)
                $value = replaceBreaksToBr($value);
            # templates and admin ui use {{var}}
            $str = str_ireplace("{{" . $var . "}}", $value, $str);
        }
        return $str;
    }

    # Get template from file
    function get_template($name, $vars = [])
    {
        $file = TEMPLATES_PATH . "{$name}.html";
        if (!is_file($file)) return null;

        # Body, then base structure
        $vars['email_body'] = $this->replace_vars(file_get_contents($file), $vars);
        $base = file_get_contents(TEMPLATES_PATH . "{$this->base_file}.html");

        return $this->replace_vars($base, $vars, true);
    }

    # Get User Data
    public function get_user_data($email)
    {
        $user = $this->db->select_one("users", '*', ['email' => $email]);
        if (!$user) return [];
        return [
            'user_firstname' => arr_val($user, 'fname', ''),
            'user_lastname' => arr_val($user, 'lname', ''),
            'user_name' => arr_val($user, 'name', ''),
            'user_email' => arr_val($user, 'email', ''),
        ];
    }

    # Send Email
    public function send($options)
    {
        $return_html = arr_val($options, 'return_html');
        $template = arr_val($options, 'template');
        $subject = arr_val($options, 'subject');
        $vars = arr_val($options, 'vars', []);
        $to = $options['to'];

        if (!$template) return false;

        $user = $this->get_user_data($to);
        $vars = array_merge($vars, $user); # User Info
        $vars = array_merge($vars, $this->base_vars); # Base Variables

        $html = $this->get_template($template, $vars);

        if ($return_html) return $html;

        return $this->send_email_to([
            'to' => $to,
            'body' => $html,
            'subject' => $subject,
            'to_name' => arr_val($user, 'name', '')
        ]);
    }

    # Send Email Main Function
    public function send_email_to($data)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $secure = SMTP_SECURE;

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->CharSet = 'UTF-8';
            if ($secure) $mail->SMTPSecure = $secure;

            $mail->setFrom(CONTACT_EMAIL, SITE_NAME);
            $mail->addAddress($data['to'], arr_val($data, 'to_name', ''));

            $mail->isHTML(true);
            $mail->Subject = $data['subject'];
            $mail->Body = $data['body'];
            $mail->AltBody = strip_tags($data['body']);

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Email failed to ' . $data['to'] . ': ' . $mail->ErrorInfo);
            return false;
        }
    }
}
