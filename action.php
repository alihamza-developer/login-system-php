<?php
require_once("./includes/db.php");

// Send Email
if (isset($_GET['type'])) {
    $type = _GET('type');
    $email = _GET('email');

    # Only verify emails resend
    if ($type !== 'verify-email') errorMsgPage("That link is not valid.");

    # Cap resends per address
    $resend_key = 'resend:' . strtolower($email);
    if (!$_guard->throttle($resend_key, 3, 3600))
        errorMsgPage("You have requested too many links. Please try again later.");
    $_guard->hit($resend_key);

    $res = sendVerifyToken($email);
    $res = json_decode($res, true);
    if ($res['status'] === "success") $res['status'] = "warning";
    showMsgPage([
        'type' => $res['status'],
        'msg' => $res['data']
    ]);
}
