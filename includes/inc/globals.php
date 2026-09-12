<?php

# Settings held encrypted in the database
define('SETTINGS_SECRETS', ['smtp_pass']);

# Site
define('SITE_NAME', 'LOGIN SUITE');
define("SITE_PHONE", "+923286503261");
define("SITE_EMAIL", "alihamzaofficial3536@gmail.com");
define("CONTACT_EMAIL", SITE_EMAIL);

# Mail (SMTP)
define('SMTP_HOST', Core\App::settings()->get('smtp_host', ''));
define('SMTP_PORT', Core\App::settings()->get('smtp_port', 587));
define('SMTP_USER', Core\App::settings()->get('smtp_user', ''));
define('SMTP_PASS', Core\App::settings()->get('smtp_pass', ''));
define('SMTP_SECURE', Core\App::settings()->get('smtp_secure', 'tls'));

# Folder paths
define('USERS_PATH', _DIR_ . 'images/users/');
define('UPLOAD_PATH', _DIR_ . 'images/uploads/');
define('TEMPLATES_PATH', _DIR_ . 'includes/templates/');

# Allowed upload types
define('IMAGES_EXTS', ['jpg', 'png', 'jpeg', 'gif']);

# Tables with no uid column
define('TABLES_WITHOUT_UID', [
    'meta_data',
    'sessions',
    'auth_tokens',
    'auth_attempts',
    'guests',
    'cron_jobs'
]);
