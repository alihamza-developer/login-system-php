<?php
// Send Verify Token to user
function sendVerifyToken($to)
{
    global $db, $_token, $_email;
    $user = $db->select_one("users", 'id,email', ['email' => $to]);
    if (!$user)
        return error("user not found!");

    # Old links stop working
    $_token->revoke_all('verify', $user['id']);
    $raw = $_token->create('verify', $user['id'], 86400);
    if (!$raw) return error("We could not create the link. Please try again.");

    $email_sent = $_email->send([
        'template' => 'verify-email',
        'to' => $user['email'],
        'vars' => [
            'token' => $raw,
            'to' => $user['email'],
        ]
    ]);
    if ($email_sent)
        return success("We sent a new verfication link to your email. Please Verify your account with in 24 hours");

    return error("Error in sending email. Please try again or contact the administrator");
}
// Verify User email with token
function verifyUserWithToken($token)
{
    global $db, $_token;
    $row = $_token->verify('verify', $token);
    if (!$row)
        return error("That verification link is invalid or has expired.");

    $user = $db->select_one("users", 'id,verify_status', ['id' => $row['user_id']]);
    if (!$user)
        return error("User not found!");

    $_token->consume($row['id']);
    if ($user['verify_status'] == '1')
        return success("Already Verified");

    $db->update("users", [
        'email_verified_at' => date('Y-m-d H:i:s'),
        'verify_status' => 1
    ], ['id' => $user['id']]);
    return success("Congratulations! Your account is verified successfully. You can logged in now");
}
