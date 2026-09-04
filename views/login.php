<?php
$VERIFY_LOGIN = true;
$page_name = 'Login';
require_once "includes/db.php";
$CSS_FILES_[] = 'authorize.css';
require_once "includes/head.php";

# Verify User
if (isset($_GET['verify']) && isset($_GET['token'])) {
    $res = verifyUserWithToken($_GET['token']);
    $res = json_decode($res, true);
    $SCRIPT_ = js_msg($res['status'], $res['data']);
}
if (isset($_GET['success'])) {
    $SCRIPT_ = 'sAlert("' . $_GET['success'] . '", "Congratulations")';
}
?>
<div class="authorize-page">
    <div class="auth-card">

        <div class="auth-brand">
            <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </div>

        <h1 class="auth-heading">Welcome back</h1>
        <p class="auth-sub">Sign in to continue to your account.</p>

        <form action="authorize" method="POST" class="js-form own-target">

            <div class="auth-field form-group">
                <label class="field-label" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("mail") ?></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required autofocus>
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
                <a href="forgot" class="auth-link">Forgot password?</a>
            </div>

            <input type="hidden" name="login" value="true">
            <button type="submit" class="auth-submit">Sign in</button>
        </form>

        <p class="auth-foot">Don't have an account? <a href="register">Create one</a></p>

    </div>
</div>
<?php require_once "includes/foot.php" ?>