<?php

use Core\App;

require_once("./includes/db.php");
// Send Email
if (isset($_GET['type'])) {
    require_once _DIR_ . "vendor/autoload.php";

    $type = _GET('type');
    $email = _GET('email');

    $is_user_verified = $db->select_one("users", "id", ['email' => $email, 'verify_status' => 1]);
    if ($is_user_verified) {
        showMsgPage([
            'type' => 'warning',
            'msg' => 'Your account is already verified.'
        ]);
    }

    // Send Email
    $res = App::auth()->send_verify_token($email);
    $res = json_decode($res, true);
    if ($res['status'] === "success") $res['status'] = "warning";
    showMsgPage([
        'type' => $res['status'],
        'msg' => $res['data']
    ]);
}
