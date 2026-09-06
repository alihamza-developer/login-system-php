<?php
# The global bootstrap, not the admin one, or this would redirect to itself
if (!defined('DIR')) define('DIR', './');
if (!defined('_DIR_')) define('_DIR_', DIR . "../");
require_once _DIR_ . "includes/db.php";

# Admins are already where they need to be
$_guard->require_guest(url('admin/dashboard'));

$page_name = 'Admin Login';
$CSS_FILES_[] = url('css/authorize.css');
require_once _DIR_ . "includes/head.php";
?>
<div class="authorize-page">
    <div class="auth-card">

        <div class="auth-brand">
            <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </div>

        <h1 class="auth-heading">Admin panel</h1>
        <p class="auth-sub">This area is restricted to administrators.</p>

        <form action="../controllers/authorize" method="POST" class="js-form own-target">

            <div class="auth-field form-group">
                <label class="field-label" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("envelope") ?></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="username" required autofocus>
                </div>
            </div>

            <div class="auth-field form-group">
                <label class="field-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("lock") ?></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Your password" autocomplete="current-password" required>
                </div>
            </div>

            <div class="auth-meta">
                <input type="checkbox" class="fancy-checkbox" name="remember" value="1" data-label="Remember me">
                <a href="<?= url('forgot') ?>" class="auth-link">Forgot password?</a>
            </div>

            <input type="hidden" name="require_admin" value="1">
            <input type="hidden" name="login" value="true">
            <button type="submit" class="auth-submit">Sign in to admin</button>
        </form>

        <p class="auth-foot"><a href="<?= url('login') ?>">Back to the site</a></p>

    </div>
</div>
<?php require_once _DIR_ . "includes/foot.php" ?>
