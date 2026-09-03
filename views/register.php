<?php
$VERIFY_LOGIN = true;
$page_name = 'Register';
require_once "includes/db.php";
$CSS_FILES_[] = 'authorize.css';
require_once "includes/head.php";
?>
<div class="authorize-page">
    <div class="auth-card">

        <div class="auth-brand">
            <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </div>

        <h1 class="auth-heading">Create your account</h1>
        <p class="auth-sub">We'll email you a link to confirm your address.</p>

        <form action="authorize" method="POST" class="js-form own-target">

            <div class="auth-row">
                <div class="auth-field form-group">
                    <label class="field-label" for="fname">First name</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("user") ?></span>
                        <input type="text" id="fname" name="fname" class="form-control" placeholder="Ada" autocomplete="given-name" required autofocus>
                    </div>
                </div>
                <div class="auth-field form-group">
                    <label class="field-label" for="lname">Last name</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("user") ?></span>
                        <input type="text" id="lname" name="lname" class="form-control" placeholder="Lovelace" autocomplete="family-name" required>
                    </div>
                </div>
            </div>

            <div class="auth-field form-group">
                <label class="field-label" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("mail") ?></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required>
                </div>
            </div>

            <div class="auth-field form-group">
                <label class="field-label" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("lock") ?></span>
                    <input type="password" id="password" name="password" class="form-control u_password" placeholder="Create a password" autocomplete="new-password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]">
                </div>
            </div>

            <div class="auth-field form-group">
                <label class="field-label" for="c_password">Confirm password</label>
                <div class="input-group">
                    <span class="input-group-text"><?= svg_icon("lock") ?></span>
                    <input type="password" id="c_password" name="c_password" class="form-control u_password" placeholder="Repeat your password" autocomplete="new-password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]">
                </div>
            </div>

            <input type="hidden" name="register_new_user" value="true">
            <button type="submit" class="auth-submit">Create account</button>
        </form>

        <p class="auth-foot">Already have an account? <a href="login">Sign in</a></p>

    </div>
</div>
<?php require_once "includes/foot.php" ?>
