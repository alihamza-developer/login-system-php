<?php

use Core\App;

define('DIR', '../');
require_once('../includes/db.php');

// Save mail settings
if (isset($_POST['save_settings'])) {
	$values = [
		'smtp_host'   => _POST('smtp_host', ['default' => '']),
		'smtp_port'   => _POST('smtp_port', ['default' => '']),
		'smtp_user'   => _POST('smtp_user', ['default' => '']),
		'smtp_secure' => _POST('smtp_secure', ['default' => '']),
	];

	if ($values['smtp_port'] !== '' && !ctype_digit((string) $values['smtp_port']))
		returnError('SMTP port must be a number');

	# Blank keeps the stored secret
	$smtp_pass = _POST('smtp_pass', ['default' => '']);
	if ($smtp_pass !== '') $values['smtp_pass'] = $smtp_pass;

	App::settings()->set_many($values);
	returnSuccess('Settings saved');
}
