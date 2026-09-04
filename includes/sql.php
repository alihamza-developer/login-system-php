<?php
define('_DIR_', '../');
require_once "inc/database.php";

@mkdir(merge_path(_DIR_, "images/uploads"));

// Check if action is already done
function _is($type)
{
    global $db;
    $data = $db->select_one("meta_data", "id", [
        "meta_key" => "tmp_scripts",
        "meta_value" => $type
    ]);
    if ($data) return false;
    $db->insert('meta_data', [
        'meta_key' => 'tmp_scripts',
        'meta_value' => $type
    ]);
    return true;
}

// Meta Data Table
$db->query("CREATE TABLE IF NOT EXISTS `meta_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `meta_key` varchar(250) NOT NULL,
    `meta_value` varchar(250) NOT NULL,
    `meta_json` text NOT NULL,
    `time` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;");
// DB Tables
if (_is("install_db_tables")) {
    $db->query("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `fname` varchar(250) NOT NULL,
        `lname` varchar(250) NOT NULL,
        `name` varchar(250) NOT NULL,
        `email` varchar(250) NOT NULL,
        `image` varchar(250) NOT NULL,
        `password` varchar(250) NOT NULL,
        `is_admin` tinyint(1) NOT NULL DEFAULT 0,
        `verify_status` int(1) NOT NULL DEFAULT 0,
        `verify_token` varchar(250) NOT NULL,
        `password_forgot_token` varchar(250) NOT NULL,
        `token_expiry_date` timestamp NULL DEFAULT NULL,
        `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
        `uid` varchar(250) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB;");
}


// Add column if missing
function _add_column($table, $column, $definition)
{
    global $db;
    $exists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'", ['select_query' => true]);
    if (count($exists)) return false;
    $db->query("ALTER TABLE `$table` ADD `$column` $definition");
    return true;
}
// Add index if missing
function _add_index($table, $name, $definition)
{
    global $db;
    $exists = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$name'", ['select_query' => true]);
    if (count($exists)) return false;
    $db->query("ALTER TABLE `$table` ADD $definition");
    return true;
}

// Drop column if present
function _drop_column($table, $column)
{
    global $db;
    $exists = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'", ['select_query' => true]);
    if (!count($exists)) return false;
    $db->query("ALTER TABLE `$table` DROP COLUMN `$column`");
    return true;
}
// Drop index if present
function _drop_index($table, $name)
{
    global $db;
    $exists = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$name'", ['select_query' => true]);
    if (!count($exists)) return false;
    $db->query("ALTER TABLE `$table` DROP INDEX `$name`");
    return true;
}

// Auth Tables
$db->query("CREATE TABLE IF NOT EXISTS `sessions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token_hash` char(64) NOT NULL,
    `csrf_token` char(64) NOT NULL,
    `remember_hash` char(64) NULL DEFAULT NULL,
    `remember_expires_at` datetime NULL DEFAULT NULL,
    `ip` varchar(45) NULL DEFAULT NULL,
    `user_agent` varchar(255) NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime NOT NULL,
    `revoked_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_token` (`token_hash`),
    KEY `idx_user` (`user_id`, `revoked_at`),
    KEY `idx_remember` (`remember_hash`)
  ) ENGINE=InnoDB;");

$db->query("CREATE TABLE IF NOT EXISTS `auth_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(20) NOT NULL,
    `user_id` int(11) NULL DEFAULT NULL,
    `token_hash` char(64) NOT NULL,
    `attempts` tinyint(4) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime NOT NULL,
    `used_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lookup` (`type`, `token_hash`)
  ) ENGINE=InnoDB;");

$db->query("CREATE TABLE IF NOT EXISTS `auth_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `identifier` varchar(190) NOT NULL,
    `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lookup` (`identifier`, `attempted_at`)
  ) ENGINE=InnoDB;");

// Auth User Columns
_add_column('users', 'role', "varchar(50) NOT NULL DEFAULT 'user'");
_add_column('users', 'phone', "varchar(32) NULL DEFAULT NULL");
_add_column('users', 'email_verified_at', "timestamp NULL DEFAULT NULL");
_add_column('users', 'phone_verified_at', "timestamp NULL DEFAULT NULL");
_add_index('users', 'uq_email', "UNIQUE KEY `uq_email` (`email`)");

// Backfill from the old columns
$db->query("UPDATE `users` SET `role` = 'admin' WHERE `is_admin` = 1 AND `role` <> 'admin';");
$db->query("UPDATE `users` SET `email_verified_at` = `date_added` WHERE `verify_status` = 1 AND `email_verified_at` IS NULL;");

// Drop the unused otp identifier
_drop_index('auth_tokens', 'idx_identifier');
_drop_column('auth_tokens', 'identifier');
