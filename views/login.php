<?php
$VERIFY_LOGIN = true;
$page_name = 'Login';
require_once "includes/db.php";
require_once "includes/head.php";

// Verify User
if (isset($_GET['verify']) && isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    $res = verifyUserWithToken($email, $token);
    $res = json_decode($res, true);
    $SCRIPT_ = js_msg($res['status'], $res['data']);
}
if (isset($_GET['success'])) {
    $SCRIPT_ = 'sAlert("' . $_GET['success'] . '", "Congratulations")';
}
?>
<div class="main-login-container">
    <div class="col-md-5 login-container">
        <form action="authorize" method="POST" class="js-form own-target w-100">
            <div class="content-center flex-column">
                <h2 class="heading ">Sign In to <?= SITE_NAME ?></h2>
                <p class="more mb-4">OR Use email account</p>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("mail")  ?></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("lock")  ?></span>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <p class="text-right mt-2">
                        <a href="forgot">Forgot Password?</a>
                    </p>
                </div>
            </div>
            <div class="col-md-12 content-center flex-column">
                <input type="hidden" name="login" value="true">
                <button class="register btn w-100 mt-2 py-2" type="submit">Login</button>
                <p class="more my-1 mt-4">OR</p>
                <p class="more">Don't you have an account? <a href="register">Sign Up</a></p>
            </div>

        </form>
    </div>
</div>
<?php require_once "includes/foot.php" ?>