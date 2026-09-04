<?php
require_once('includes/db.php');
require_once _DIR_ . "includes/Classes/Emails.php";
$page_name = 'Reset Password';
$reset_password = false;

# Verify reset token
if (isset($_GET['reset']) && isset($_GET['token'])) {
    $row = $_token->verify('reset', $_GET['token']);
    if ($row) $reset_password = true;
    else $SCRIPT_ = js_msg('error', 'That reset link is invalid or has expired. Please request a new one.');
}

$CSS_FILES_[] = 'authorize.css';
require_once "includes/head.php";
?>
<div class="authorize-page">
    <div class="auth-card">

        <div class="auth-brand">
            <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </div>

        <?php if ($reset_password) { ?>

            <h1 class="auth-heading">Choose a new password</h1>
            <p class="auth-sub">This link can only be used once.</p>

            <form action="authorize" method="POST" class="js-form own-target">

                <div class="auth-field form-group">
                    <label class="field-label" for="new_password">New password</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("lock") ?></span>
                        <input type="password" id="new_password" name="new_password" class="form-control u_password" placeholder="Create a password" autocomplete="new-password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]" autofocus>
                    </div>
                </div>

                <div class="auth-field form-group">
                    <label class="field-label" for="confirm_password">Confirm password</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("lock") ?></span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control u_password" placeholder="Repeat your password" autocomplete="new-password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]">
                    </div>
                </div>

                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'], ENT_QUOTES) ?>">
                <input type="hidden" name="reset_password" value="true">
                <button type="submit" class="auth-submit">Update password</button>
            </form>

            <p class="auth-foot"><a href="login">Back to sign in</a></p>

        <?php } else { ?>

            <h1 class="auth-heading">Reset your password</h1>
            <p class="auth-sub">Enter your email and we'll send you a reset link.</p>

            <form action="authorize" method="POST" class="js-form own-target">

                <div class="auth-field form-group">
                    <label class="field-label" for="email">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("mail") ?></span>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required autofocus>
                    </div>
                </div>

                <input type="hidden" name="send_reset_password_link" value="true">
                <button type="submit" class="auth-submit">Send reset link</button>
            </form>

            <p class="auth-foot">Remembered it? <a href="login">Back to sign in</a></p>

        <?php } ?>

    </div>
</div>
<?php require_once "includes/foot.php" ?>
