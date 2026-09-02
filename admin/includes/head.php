<?php
$page_name .= " - Admin";

add_assets_template('dashboard'); # Dashboard Files

// Global header
require_once global_file("header");
// Sidebar & Navbar
require_once('includes/sidebar.php');
require_once('includes/navbar.php');
?>
<div class="all-content">