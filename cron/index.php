<?php
# Paths must not depend on the working directory
define('DIR', __DIR__ . '/../');
require_once DIR . "includes/db.php";
require_once __DIR__ . "/includes/Cron.php";
require_once __DIR__ . "/includes/tasks.php";

$ran = $_cron->run_all();

if (!$ran) {
    echo "nothing due\n";
} else {
    foreach ($ran as $name => $status) echo str_pad($status, 6) . $name . "\n";
}
