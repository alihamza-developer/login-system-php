<?php

namespace Email;

use \Mailjet\Resources;
use \DB\Database;

require_once _DIR_ . "includes/inc/emails-data.php";

class Emails extends Database
{
    private $db;
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    // Get Email Headers
    public function getHeaders()
    {
        $from = SITE_EMAIL;
        $site_name = SITE_NAME;
        $br = "\r\n";

        $headers = [
            "Reply-To" => $from,
            "Return-Path" => $from,
            "From" => $from,
            "Organization" => '',
            "MIME-Version" => '1.0',
            "Content-type" => 'text/html; charset=utf-8',
            "X-Priority" => '3',
            "X-Mailer" => "PHP" . phpversion(),
        ];

        $headers_str = "";

        foreach ($headers as $key => $value) {
            $headers_str .= "{$key} {$site_name} <\"$value\">";
        }

        return $headers_str;
    }
    // Create button
    public function createBtn($text, $href)
    {
        return '<a href="' . $href . '" style="text-align:center;background: #17a2b8;color:#fff;text-decoration:none;padding:15px 20px;display:inline-block;">' . $text . '</a>';
    }

    // Replace Variables from string
    function replace_email_vars($str, $vars = [], $is_email_body = false)
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

    // Read Email File
    function get_data_from_file($filename, $vars = [])
    {
        $filepath = _DIR_ . "includes/Classes/templates/" . $filename;
        if (!is_file($filepath))
            return null;

        $file_data = file_get_contents($filepath);
        $vars = array_merge([
            'site_url' => SITE_URL,
            'site_name' => SITE_NAME,
            'site_email' => SITE_EMAIL,
            'site_phone' => SITE_PHONE,
            'site_phone_image' => url("images/png-icons/phone.png"),
            'site_mail_image' => url("images/png-icons/mail.png"),
            'site_url_image' => url("images/png-icons/globe.png"),
            'www_site_url' => get_www_url(SITE_URL),
            // 'email_header_image' => merge_path(SITE_URL, 'images/email-header.jpg'),
            // 'email_footer_image' => merge_path(SITE_URL, 'images/email-footer.jpg'),
        ], $vars);
        $file_data = $this->replace_email_vars($file_data, $vars);
        return $file_data;
    }

    // Get Email Structures
    function get_email_structure()
    {
        return $this->get_data_from_file('email_structure.html');
    }

    // Read Template file
    public function readTemplateFile($str, $vars = [])
    {
        $email_body = $this->replace_email_vars($str, $vars);
        $vars['email_body'] = $email_body;
        // Get Email Structure
        $email_structure = $this->get_email_structure();
        $file_data = $this->replace_email_vars($email_structure, $vars, true);
        return $file_data;
    }
    
    # Send Email
    public function send($options)
    {
        $template = arr_val($options, 'template');
        $to = $options['to'];
        if ($template) {
            if (!isset(EMAILS[$template])) return;
            // Read Template file
            $name = $template;
            $template = EMAILS[$template];
            $email_template  = $this->db->select_one("email_templates", '*', ['name' => $name]);
            if (!$email_template) return;

            $subject = $email_template['subject'];
            $body = html_entity_decode(htmlspecialchars_decode($email_template['body']));

            $vars = arr_val($options, 'vars', []);
            $vars = array_merge($vars, [
                'site_name' => SITE_NAME,
                'site_url' => SITE_URL,
                'login_url' => merge_path(SITE_URL, "login"),
                'site_email' => CONTACT_EMAIL,
                'site_logo_url' => url('images/logo-with-name.png?v=1.0'),
                'site_initial' => strtoupper(substr(SITE_NAME, 0, 1)),
            ]);
            // User Data
            $user = $this->db->select_one("users", '*', ['email' => $to]);
            $vars['user_firstname'] = arr_val($user, 'fname', '');
            $vars['user_lastname'] = arr_val($user, 'lname', '');
            $vars['user_name'] = arr_val($user, 'name', '');
            $vars['user_email'] = arr_val($user, 'email', '');
            // Read template file
            $data = $this->readTemplateFile($body, $vars);
            $subject_ = $this->replace_email_vars($subject, $vars);

            // Return Html
            if (arr_val($options, 'return_html', false))
                return $data;

            // Send Email
            return $this->sendEmailTo([
                'to' => $to,
                'body' => $data,
                'subject' => $subject_
            ]);
        }
        return false;
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
