<?php
# Emails 
$EMAILS_ = [
    'base-structure' => [
        'title' => 'Email Structure',
        'description' => 'The shared wrapper every email is placed inside.',
        'variables' => ['email_body', 'site_initial'],
        'is_non_user_email' => true
    ],
    'forgot-email' => [
        'title' => 'Forgot Password',
        'subject' => 'Reset your password',
        'description' => 'Sent when someone requests a password reset link.',
        'variables' => ['token', 'to'],
    ],
    'verify-email' => [
        'title' => 'Verify Email',
        'subject' => 'Verify your email address',
        'description' => 'Sent after signup to confirm the email address.',
        'variables' => ['token', 'to'],
    ],
    'contact-email' => [
        'title' => 'Contact Email',
        'subject' => 'New message from {{site_name}}',
        'description' => 'Sent to the site owner from the contact form.',
        'variables' => ['name', 'email', 'subject', 'message'],
        'is_non_user_email' => true
    ]
];

$common_var = ['site_url', 'site_name', 'login_url'];
foreach ($EMAILS_ as $key => $email) {
    $vars = $email['variables'];
    $is_non_user_email = arr_val($email, 'is_non_user_email', false);
    $common_var_ = $common_var;
    if (!$is_non_user_email) {
        array_push($common_var_, 'user_firstname', 'user_lastname', 'user_name', 'user_email');
    }
    $EMAILS_[$key]['variables'] = array_merge($vars, $common_var_);
}

define('EMAILS', $EMAILS_);
unset($EMAILS_);
