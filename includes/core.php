<?php
if (!defined('DIR')) define('DIR', './');
if (!defined('_DIR_')) define('_DIR_', DIR);

# Settings
require_once __DIR__ . '/inc/config.php';

# Plain helper functions
require_once __DIR__ . '/inc/functions.php';

# Route table, index.php and the sitemap both read it
require_once __DIR__ . '/routes.php';

# Composer Autoload
require_once __DIR__ . '/../vendor/autoload.php';

# Database connection
$db = Core\App::db();

# Files that needs DB connection on load
require_once __DIR__ . '/inc/globals.php';
require_once __DIR__ . '/inc/functions2.php';
require_once __DIR__ . '/inc/site-setting.php';
