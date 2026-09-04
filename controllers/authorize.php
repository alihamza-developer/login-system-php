<?php
define('DIR', '../');
require_once(DIR . 'includes/db.php');

# Every post must carry the csrf token
$_guard->verify_csrf();
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

	# The account exists either way, so say which happened
	$sent = json_decode(sendVerifyToken($email), true);
	if (arr_val($sent, 'status') !== 'success')
		returnError('Your account was created, but we could not send the verification email. Try signing in to request a new link.');

	echo success('We sent a verfication link to your email. Please Verify your account');
}
// Login
if (isset($_POST['login'])) {
	$email    = _POST('email');
	$password = _POST('password');

	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
	$key_user = 'login:' . strtolower($email);
	$key_ip   = 'login:ip:' . $ip;

	# Slow down password guessing
	if (!$_guard->throttle($key_user, 5, 900) || !$_guard->throttle($key_ip, 20, 900))
		returnError('Too many sign-in attempts. Please try again in 15 minutes.');

	$user = $_auth->attempt($email, $password);
	if (!$user) {
		$_guard->hit($key_user);
		$_guard->hit($key_ip);

		# Unverified accounts can resend
		if ($_auth->fail_reason() === 'unverified') {
			$resend = 'action?type=verify-email&email=' . urlencode($email);
			returnError('Please verify your account. <a href="' . $resend . '">Click here to resend</a>', ['html' => true]);
		}
		returnError('Email or Password is wrong. Please Try with a valid email and password');
	}

	$_guard->clear($key_user);
	$_guard->clear($key_ip);

	$remember = _POST('remember', ['default' => '']) !== '';
	if (!$_auth->login($user['id'], $remember))
		returnError('We could not sign you in. Please try again.');

	echo success('logged in successfully', [
		'redirect' => 'user/dashboard'
	]);
}
// Send Reset Password Link
if (isset($_POST['send_reset_password_link'])) {
	$email = _POST('email', ['default' => '']);

	# Same reply either way, so the form cannot be used to find accounts
	$generic = 'If that address has an account, a reset link is on its way.';

	# Cap reset emails per address, same reply either way
	$reset_key = 'reset:' . strtolower($email);
	if (!$_guard->throttle($reset_key, 3, 3600)) returnSuccess($generic);
	$_guard->hit($reset_key);

	$user = $db->select_one('users', 'id,email,verify_status', ['email' => $email]);
	if (!$user) returnSuccess($generic);
	if ($user['verify_status'] != 1) returnSuccess($generic);

	# Any earlier link stops working
	$_token->revoke_all('reset', $user['id']);
	$raw = $_token->create('reset', $user['id'], 3600);
	if (!$raw) returnError('We could not create a reset link. Please try again.');

	$_email->send([
		'template' => 'forgot-email',
		'to' => $email,
		'vars' => [
			'token' => $raw,
			'to' => $email,
		]
	]);
	returnSuccess($generic);
}
// Reset Password
if (isset($_POST['reset_password'])) {
	$token            = _POST('token', ['default' => '']);
	$new_password     = _POST('new_password', ['default' => '']);
	$confirm_password = _POST('confirm_password', ['default' => '']);

	if (strlen($new_password) < AUTH_PASSWORD_MIN)
		returnError('Password must be at least ' . AUTH_PASSWORD_MIN . ' characters');

	if ($new_password !== $confirm_password)
		returnError('Password is not matching');

	$row = $_token->verify('reset', $token);
	if (!$row)
		returnError('That reset link is invalid or has expired. Please request a new one.');

	$update = $db->update('users', [
		'password' => password_hash($new_password, AUTH_PASSWORD_ALGO),
	], ['id' => $row['user_id']], ['encodeHtml' => false]);

	if (!$update)
		returnError('We could not change your password. Please try again.');

	# Burn the link and sign out every device
	$_token->consume($row['id']);
	$_session->revoke_all($row['user_id']);

	returnSuccess('Password changed successfully', [
		'redirect' => 'login?success=Password changed successfully'
	]);
}
