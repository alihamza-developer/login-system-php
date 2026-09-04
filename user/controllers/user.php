<?php
define('DIR', '../');
require_once(DIR . 'includes/db.php');

# Every post must carry the csrf token
$_guard->verify_csrf();

# Must be signed in
if (!LOGGED_IN_USER) returnError('You need to be signed in to do that.');

// Update Personal Info
if (isset($_POST['update_personal_information'])) {
	$fname = _POST('fname');
	$lname = _POST('lname');
	$name = "$fname $lname";

	$dbData = [
		'fname' => $fname,
		'lname' => $lname,
		'name' => $name
	];
	// Upload avatar
	$avatar = $_fn->upload_file("avatar", [
		'path' => USERS_PATH,
		'allowed_exts' => IMAGES_EXTS
	]);
	if ($avatar['status'] === 'success')
		$dbData['image'] = $avatar['filename'];

	$update = $db->update('users', $dbData, ['id' => LOGGED_IN_USER['id']]);
	if ($update)
		returnSuccess('Information Updated Successfully');
}
// Change Password
if (isset($_POST['change_password'])) {
	$current_password = _POST('current_password');
	$new_password = _POST('new_password');
	$confirm_password = _POST('confirm_password');

	// Validate
	if (strlen($new_password) < AUTH_PASSWORD_MIN)
		returnError('New password must be at least ' . AUTH_PASSWORD_MIN . ' characters');
	if ($new_password !== $confirm_password)
		returnError('New password is not matching with confirm password. Please try again');
	// Verify current password
	if (!password_verify($current_password, LOGGED_IN_USER['password']))
		returnError('Current password is wrong. Please enter a correct password');
	// Update password
	$new_password = password_hash($new_password, PASSWORD_BCRYPT);
	$update = $db->update('users', ['password' => $new_password], [
		'id' => LOGGED_IN_USER['id']
	], ['encodeHtml' => false]);

	if (!$update) returnError('We could not change your password. Please try again.');
	returnSuccess('Password changed successfully');
}

// Sign out one device
if (isset($_POST['revoke_device'])) {
	$device_id = (int) _POST('device_id', ['default' => 0]);

	# Only sessions on this account
	$mine = false;
	foreach ($_session->devices(LOGGED_IN_USER_ID) as $device) {
		if ((int) $device['id'] === $device_id) $mine = true;
	}
	if (!$mine) returnError('That device is not on your account.');

	$_session->revoke($device_id);
	returnSuccess('Signed out on that device', ['redirect' => 'refresh']);
}

// Sign out everywhere else
if (isset($_POST['revoke_other_devices'])) {
	$current = $_session->current();
	$_session->revoke_all(LOGGED_IN_USER_ID, $current ? $current['id'] : null);
	returnSuccess('Signed out on every other device', ['redirect' => 'refresh']);
}
