<?php
require_once('includes/db.php');
require_once _DIR_ . "includes/Classes/Emails.php";
$page_name = 'Login';
$reset_password = false;
$alertMsg = '';
// Verify User
if (isset($_GET['reset']) && isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    $user = $db->select_one('users', '*', ['email' => $email]);
    if ($user) {
        $new_forgot_token = md5(time() . rand(9, 9999)) . $user['id'];
        $new_expiry_date = get_date_with("+ 1 days");
        if ($token == $user['password_forgot_token']) {
            $expiry_date = $user['token_expiry_date'];
            $expiry_date = date("Y-m-d h:i:s", strtotime($expiry_date));
            $current_date = date("Y-m-d h:i:s");
            if ($current_date > $expiry_date) {
                $db->update('users', array(
                    'password_forgot_token' => $new_forgot_token,
                    'token_expiry_date' => $new_expiry_date
                ), array('id' => $user['id']));
                $_email->send([
                    'template' => 'forgotEmail',
                    'to' => $user['email'],
                    'vars' => [
                        'token' => $new_forgot_token,
                        'to' => $user['email'],

                    ]
                ]);
                $alertMsg = 'sAlert("Reset Link expired. We sent a new password reset link to your email address. You can reset your account password with in next 24 hours", "Error");';
            } else {
                $reset_password = true;
            }
        } else {
            $db->update('users', array(
                'password_forgot_token' => $new_forgot_token,
                'token_expiry_date' => $new_expiry_date
            ), array('id' => $user['id']));
            $_email->send([
                'template' => 'forgotEmail',
                'to' => $user['email'],
                'vars' => [
                    'token' => $new_forgot_token,
                    'to' => $user['email'],

                ]
            ]);
            $alertMsg = 'sAlert("Reset Link expired. We sent a new password reset link to your email address. You can reset your account password with in next 24 hours", "Error");';
        }
    }
    $SCRIPT_ = $alertMsg;
}

require_once "includes/head.php";
?>
<div class="container h-100 align-center">
    <form action="authorize" method="POST" class="js-form own-target w-100">

        <div class="col-md-6 m-auto login-container">
            <h2 class="heading text-success">Reset Password</h2>
            <div class="col-md-12 mt-5">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("mail")  ?></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                </div>
            </div>
            <div class="col-md-12 content-center flex-column">
                <input type="hidden" name="forgotPassword" value="true">
                <button class="btn w-100 mt-2 py-2" type="submit">Reset Password</button>
                <p class="more my-1 mt-4">OR</p>
                <p class="more">Don't you have an account? <a href="register">Sign Up</a></p>
            </div>

        </div>

    </form>

</div>
<?php require_once "includes/foot.php" ?>