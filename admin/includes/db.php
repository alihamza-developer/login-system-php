<?php
if (!defined('DIR')) define('DIR', './');
if (!defined('_DIR_')) define('_DIR_', DIR . "../");
require_once _DIR_ . "includes/db.php";

$_guard->require_role('admin', url('admin/login'));
