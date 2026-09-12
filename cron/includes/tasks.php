<?php

# Task name matches the file in tasks/
$_cron->task('auth-cleanup', 'Delete expired sessions, tokens and attempts', 3600);
$_cron->task('guest-cleanup', 'Delete guests past their expiry', 86400);
