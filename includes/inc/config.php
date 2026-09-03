<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once "env.php";

define("SITE_NAME", "LOGIN SUITE");
define("SITE_PHONE", "+923286503261");
define("SITE_EMAIL", "alihamzaofficial3536@gmail.com");
define("CONTACT_EMAIL", SITE_EMAIL);


define('MAILJET_API_KEY', 'cbe81f573b2967c57407d29a840950ac');
define('MAILJET_SECRET_KEY', 'b923aa77acd5d4285fd52cb075fd60d0');
define('MAILJET_EMAIL', SITE_EMAIL);


$CSS_FILES_ = [];
$JS_FILES_ = [];
$SCRIPT_ = '';
define('ASSETS_V', "?v=" . (ENV === 'prod' ? '1.0.0' : time()));

define('IMAGES_EXTS', ['jpg', 'png', 'jpeg', 'gif']);

@define('TABLES_WITHOUT_UID', [
    'meta_data'
]);

@define('UPLOAD_PATH', _DIR_ . 'images/uploads/');
