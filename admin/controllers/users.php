<?php
define('DIR', '../');
require_once('../includes/db.php');

// Modify User admin role
if (isset($_POST['modifyUserIsAdmin'])) {
    $is_admin = $_POST['modifyUserIsAdmin'] == "true" ? 1 : 0;
    $user_id = _POST('user_id');

    $update = $db->update("users", ['is_admin' => $is_admin], ['id' => $user_id]);
    if ($update)
        returnSuccess();
}
