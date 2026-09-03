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

