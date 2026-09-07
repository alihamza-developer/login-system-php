<?php
define('_DIR_', '../');
require_once "inc/database.php";

@mkdir(UPLOAD_PATH);

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

# Meta Data Table
$db->query("CREATE TABLE IF NOT EXISTS `meta_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `meta_key` varchar(250) NOT NULL,
    `meta_value` varchar(250) NOT NULL,
    `meta_json` text NOT NULL,
    `time` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB;");

# Users Table
$db->query("CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `fname` varchar(250) NOT NULL,
    `lname` varchar(250) NOT NULL,
    `name` varchar(250) NOT NULL,
    `email` varchar(250) NOT NULL,
    `phone` varchar(32) NULL DEFAULT NULL,
    `image` varchar(250) NOT NULL,
    `password` varchar(250) NOT NULL,
    `role` varchar(50) NOT NULL DEFAULT 'user',
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `phone_verified_at` timestamp NULL DEFAULT NULL,
    `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
    `uid` varchar(250) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`)
  ) ENGINE=InnoDB;");

# Sessions Table
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

# Auth Tokens Table
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

# Auth Attempts Table
$db->query("CREATE TABLE IF NOT EXISTS `auth_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `identifier` varchar(190) NOT NULL,
    `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_lookup` (`identifier`, `attempted_at`)
  ) ENGINE=InnoDB;");

# Cron Table
$db->query("CREATE TABLE IF NOT EXISTS `cron_jobs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(60) NOT NULL,
    `started_at` datetime NULL DEFAULT NULL,
    `last_run_at` datetime NULL DEFAULT NULL,
    `last_status` varchar(10) NOT NULL DEFAULT '',
    `last_message` varchar(250) NOT NULL DEFAULT '',
    `duration_ms` int(11) NOT NULL DEFAULT 0,
    `runs` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_name` (`name`)
  ) ENGINE=InnoDB;");
