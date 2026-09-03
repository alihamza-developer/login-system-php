<?php

namespace Email;

use \DB\Database;

require_once _DIR_ . "includes/inc/emails-data.php";

class Emails extends Database
{
    private $db;
    private $base_file;
    private $base_vars;

    # Constructor
    public function __construct()
    {
        global $db;
        $this->db = $db;
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
    function get_template($name)
    {
        if (!isset(EMAILS[$name])) return null;
        $file = _DIR_ . "includes/templates/{$name}.html";
        if (!is_file($file)) return null;
        return file_get_contents($file);
    }

    # Read Template file
    public function read_template_file($str, $vars = [])
    {
        $email_body = $this->replace_vars($str, $vars);
        $vars['email_body'] = $email_body;

        // Get Email Structure
        $file_data = $this->replace_vars($this->get_template($this->base_file), $vars, true);
        return $file_data;
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
        $template = arr_val($options, 'template');
        $to = $options['to'];
        if (!$template) return false;

        if (!isset(EMAILS[$template])) return;

        if ($template === $this->base_file) return;

        $name = $template;
        $template = EMAILS[$template];
        $body = $this->get_template($name);
        if (!$body) return;

        $subject = arr_val($template, 'subject', SITE_NAME);

        $vars = arr_val($options, 'vars', []);

        # Merge Base Variables Global
        $vars = array_merge($vars, $this->base_vars);

        # Merge User Variables
        $vars = array_merge($vars, $this->get_user_data($to));


        # Read Template File
        $body = $this->read_template_file($body, $vars);
        $subject_ = $this->replace_vars($subject, $vars);

        // Return Html
        if (arr_val($options, 'return_html', false)) return $body;

        // Send Email
        return $this->sendEmailTo([
            'to' => $to,
            'body' => $body,
            'subject' => $subject_
        ]);
    }

    # Send Email Main Function
    public function sendEmailTo($data)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->CharSet = 'UTF-8';
            if (SMTP_SECURE) $mail->SMTPSecure = SMTP_SECURE;

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
$_email = new Emails();
require_once _DIR_ . "includes/inc/emails.php";
