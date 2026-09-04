<?php
define('DIR', '../');
require_once('../includes/db.php');

# Every post must carry the csrf token
$_guard->verify_csrf();

require_once _DIR_ . 'cron/includes/Cron.php';
require_once _DIR_ . 'cron/includes/tasks.php';

// Run one task now
if (isset($_POST['run_task'])) {
	$name = _POST('name', ['default' => '']);
	$tasks = $_cron->all();

	if (!isset($tasks[$name])) returnError('That task does not exist.');

	$status = $_cron->run($name, true);
	if ($status === 'error') returnError('That task failed. See the message below it.', ['redirect' => 'refresh']);

	returnSuccess('Task finished', ['redirect' => 'refresh']);
}
