<?php

use Core\App;

define('DIR', '../');
require_once('../includes/db.php');

$_delete = App::delete();

$_delete->set([
	'user' => 'users'
]);

$_delete->init();
