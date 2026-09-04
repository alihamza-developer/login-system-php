<?php
define('DIR', '../');
require_once('../includes/db.php');

# Every post must carry the csrf token
$_guard->verify_csrf();
require_once _DIR_ . "includes/Classes/IconsManager.php";
require_once _DIR_ . "includes/Classes/Delete.php";

$_delete->set([
	'user' => 'users'
]);

$_delete->init();
