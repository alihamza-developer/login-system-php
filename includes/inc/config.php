<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once "env.php";

# Auth settings
define('AUTH_SESSION_IDLE', 7200); # 2 hours if not activitly it will logout 
define('AUTH_SESSION_ABSOLUTE', 604800); # 7 days Session validity
define('AUTH_REMEMBER_LIFETIME', 2592000); # 30 days Max Remember Me cookie lifetime
define('AUTH_PASSWORD_ALGO', PASSWORD_DEFAULT); # Algorithm
define('AUTH_PASSWORD_MIN', 8); # Minimum length
define('AUTH_PERMISSIONS', [
    'admin' => ['*'],
    'user'  => ['profile.edit'],
]);

$CSS_FILES_ = [];
$JS_FILES_ = [];
$SCRIPT_ = '';
define('ASSETS_V', "?v=" . (ENV === 'prod' ? '1.0.0' : time()));
