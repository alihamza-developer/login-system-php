<?php
$_auth->logout();
header('Location: ' . url('login'));
exit;
