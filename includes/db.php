<?php

use Core\App;

require_once __DIR__ . '/core.php';

$timestamp = date('Y-m-d h:i:s');

# helpers
$_fn      = App::functions();

# Auth + Guest
$_session = App::session();
$_token   = App::token();
$_auth    = App::auth();
$_guard   = App::guard();
$_guest   = App::guest();
$_settings = App::settings();

# User + Guest Info
define('LOGGED_IN_USER', $_auth->user());
define('LOGGED_IN_USER_ID', LOGGED_IN_USER ? LOGGED_IN_USER['id'] : null);
define('IS_ADMIN', $_auth->is("admin"));
define('GUEST_ID', $_guest->boot(LOGGED_IN_USER_ID)); # Guest Id

# CSRF
$_guard->csrf_token(); # Set CSRF Token Cookie
$_guard->verify_csrf();  # Every POST, wherever it lands

@define("DIR_TYPE", 'frontend');
