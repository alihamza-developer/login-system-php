<?php
global $db;

# Merged guests stay
# Broken dates never expire
$db->query(
    "DELETE FROM `guests`
     WHERE `expires_at` > '1000-01-01' AND `expires_at` < NOW()
     AND `merged_user_id` IS NULL
     LIMIT 500"
);
$guests = $db->conn->affected_rows;

return "$guests guests removed";
