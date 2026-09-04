<?php global $_guard; # meta can be included inside a function ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0f766e">
<meta name="csrf-token" content="<?= htmlspecialchars((string) $_guard->csrf_token(), ENT_QUOTES) ?>">
<link rel="icon" type="image/svg+xml" href="<?= url('images/favicon.svg') ?>">
<title><?= $page_name . ' - ' . SITE_NAME; ?></title>