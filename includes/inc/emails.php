<?php
// Send Verify Token to user
function sendVerifyToken($to)
{
    global $db, $_email;
    $user = $db->select_one("users", 'id,email', ['email' => $to]);
    if (!$user)
        return error("user not found!");

    $new_token = getRand(200);
    $new_expiry_date = get_date_with("+ 1 days");
    $db->update('users', [
        'verify_token' => $new_token,
        'token_expiry_date' => $new_expiry_date
    ], ['id' => $user['id']]);

    $email_sent = $_email->send([
        'template' => 'verify-email',
        'to' => $user['email'],
        'vars' => [
            'token' => $new_token,
            'to' => $user['email'],

        ]
    ]);
    if ($email_sent)
        return success("We sent a new verfication link to your email. Please Verify your account with in 24 hours");

    return error("Error in sending email. Please try again or contact the administrator");
}
// Verify User email with token
function verifyUserWithToken($email, $token)
{
    global $db;
    $user = $db->select_one("users", 'id,email,verify_status,token_expiry_date,verify_token', ['email' => $email]);
    if (!$user)
        return error("User not found with this email!");

    if ($user['verify_status'] == '1')
        return success("Already Verified");

    $expiry_date = $user['token_expiry_date'];
    $expiry_date = date("Y-m-d h:i:s", strtotime($expiry_date));
    $current_date = date("Y-m-d h:i:s");

    if ($user['verify_token'] != $token || $current_date > $expiry_date)
        return error("Verification Link expired. We sent a new verfication link to your email. Please Verify your account with in 24 hours");

    $db->update("users", [
        'verify_status' => 1
    ], ['id' => $user['id']]);
    return success("Congratulations! Your account is verified successfully. You can logged in now");
}
