<?php
global $db, $_session, $_token, $_guard;

# Session Cleanup
$_session->purge();
$sessions = $db->conn->affected_rows;

# Token Cleanup
$_token->purge();
$tokens = $db->conn->affected_rows;

# Auth Attempts Cleanup
$_guard->purge();
$attempts = $db->conn->affected_rows;

return "$sessions sessions, $tokens tokens, $attempts attempts removed";
