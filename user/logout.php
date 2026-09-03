<?php
define('DIR', './');
define('_DIR_', DIR . "../");
require_once _DIR_ . "includes/db.php";

$_auth->logout();
header('Location: ../login');
exit;
