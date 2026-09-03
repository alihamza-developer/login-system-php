<?php
require_once('includes/db.php');
require_once _DIR_ . "includes/Classes/Emails.php";
$page_name = 'Reset Password';
$reset_password = false;
$alertMsg = '';

# Verify reset token
if (isset($_GET['reset']) && isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    $user = $db->select_one('users', '*', ['email' => $email]);
    if ($user) {
        $new_forgot_token = md5(time() . rand(9, 9999)) . $user['id'];
        $new_expiry_date = get_date_with("+ 1 days");
        $expired = true;

        if ($token == $user['password_forgot_token']) {
            $expiry_date = date("Y-m-d H:i:s", strtotime($user['token_expiry_date']));
            $current_date = date("Y-m-d H:i:s");
            $expired = $current_date > $expiry_date;
        }

        if (!$expired) {
            $reset_password = true;
        } else {
            $db->update('users', [
                'password_forgot_token' => $new_forgot_token,
                'token_expiry_date' => $new_expiry_date
            ], ['id' => $user['id']]);

            $_email->send([
                'template' => 'forgot-email',
                'to' => $user['email'],
                'vars' => ['token' => $new_forgot_token, 'to' => $user['email']]
            ]);
            $alertMsg = 'sAlert("That reset link expired. We sent you a new one, valid for 24 hours.", "warning");';
        }
    }
    $SCRIPT_ = $alertMsg;
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
                        <input type="password" id="new_password" name="new_password" class="form-control u_password" placeholder="Create a password" autocomplete="new-password" required autofocus>
                    </div>
                </div>

                <div class="auth-field form-group">
                    <label class="field-label" for="confirm_password">Confirm password</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("lock") ?></span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control u_password" placeholder="Repeat your password" autocomplete="new-password" required>
                    </div>
                </div>

                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'], ENT_QUOTES) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'], ENT_QUOTES) ?>">
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
