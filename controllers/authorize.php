<?php
define('DIR', '../');
require_once(DIR . 'includes/db.php');
require_once _DIR_ . "includes/Classes/Emails.php";
// Sign Up
if (isset($_POST['register_new_user'])) {
	$fname      = _POST('fname', ['default' => '']);
	$lname      = _POST('lname', ['default' => '']);
	$name       = trim("$fname $lname");
	$email      = _POST('email', ['default' => '']);
	$password   = _POST('password', ['default' => '']);
	$c_password = _POST('c_password', ['default' => '']);

	if ($fname === '') returnError('First name is required');
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) returnError('Enter a valid email address');
	if (strlen($password) < AUTH_PASSWORD_MIN) returnError('Password must be at least ' . AUTH_PASSWORD_MIN . ' characters');
	if ($password !== $c_password) returnError('Passwords do not match');

	$check = $db->select_one("users", '*', ['email' => $email]);
	if ($check) returnError('Email Already Exists. Go to Log In Page');

	$add_user = $db->insert('users', [
		'fname' => $fname,
		'lname' => $lname,
		'name' => $name,
		'email' => $email,
		'image' => 'avatar.png',
		'password' => password_hash($password, AUTH_PASSWORD_ALGO),
		'verify_status' => 0,
		'date_added' => $timestamp
	]);
	if (!$add_user) returnError('We could not create your account. Please try again.');

	sendVerifyToken($email);
	echo success('We sent a verfication link to your email. Please Verify your account');
}
// Login
if (isset($_POST['login'])) {
	$email    = _POST('email');
	$password = _POST('password');

	$user = $_auth->attempt($email, $password);
	if (!$user) {
		# Unverified accounts can resend
		if ($_auth->fail_reason() === 'unverified') {
			$resend = 'action?type=verify-email&email=' . urlencode($email);
			returnError('Please verify your account. <a href="' . $resend . '">Click here to resend</a>', ['html' => true]);
		}
		returnError('Email or Password is wrong. Please Try with a valid email and password');
	}

	if (!$_auth->login($user['id']))
		returnError('We could not sign you in. Please try again.');

	echo success('logged in successfully', [
		'redirect' => 'user/dashboard'
	]);
}
// Send Reset Password Link
if (isset($_POST['send_reset_password_link'])) {
	$email = $_POST['email'];
	$user = $db->select_one('users', '*', ['email' => $email]);
	if ($user) {
		if ($user['verify_status'] != 1) {
			echo error('Your account is not verified. First verify you account');
			die();
		}
		$forgot_token = md5(time() . $user['id'] . rand(0, 999));
		$token_expiry_date = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . " + 1 days"));
		$update = $db->update('users', ['password_forgot_token' => $forgot_token, 'token_expiry_date' => $token_expiry_date], ['id' => $user['id']]);
		if ($update) {
			$_email->send([
				'template' => 'forgot-email',
				'to' => $email,
				'vars' => [
					'token' => $forgot_token,
					'to' => $email,

				]
			]);
			echo success('Reset Password link sent to your email. You can reset the password with in 24 hours');
		}
	} else {
		echo error("You've entered the incorrect email address. Please try again.");
	}
}
// Reset Password
if (isset($_POST['reset_password'])) {
	$variables = ['token', 'email', 'new_password', 'confirm_password'];
	foreach ($variables as $value) {
		if (!isset($_POST[$value])) {
			echo error('Something is missing from that request. Please open the reset link again.');
			die();
		}
	}
	$token = $_POST['token'];
	$email = $_POST['email'];
	$new_password = $_POST['new_password'];
	$confirm_password = $_POST['confirm_password'];

	$invalid_link = 'That reset link is invalid or has expired. Please request a new one.';

	$user = $db->select_one('users', '*', ['email' => $email]);
	if (!$user) {
		echo error($invalid_link);
		die();
	}

	# Token must match
	if (!hash_equals((string) $user['password_forgot_token'], (string) $token)) {
		echo error($invalid_link);
		die();
	}

	# Token must not be expired
	$expiry_date = date("Y-m-d H:i:s", strtotime($user['token_expiry_date']));
	$current_date = date("Y-m-d H:i:s");
	if ($current_date >= $expiry_date) {
		echo error($invalid_link);
		die();
	}

	if (strlen($new_password) < AUTH_PASSWORD_MIN)
		returnError('Password must be at least ' . AUTH_PASSWORD_MIN . ' characters');

	if ($new_password !== $confirm_password) {
		echo error('Password is not matching');
		die();
	}

	$password = password_hash($new_password, PASSWORD_BCRYPT);
	$expiry_date = date('Y-m-d H:i:s', strtotime("-3 days"));
	$update = $db->update('users', [
		'password' => $password,
		'token_expiry_date' => $expiry_date,
	], ['id' => $user['id']]);

	if (!$update) {
		echo error('We could not change your password. Please try again.');
		die();
	}

	echo success("Password changed successfully", [
		'redirect' => 'login?success=Password changed successfully'
	]);
	die();
}
