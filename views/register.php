<?php
$VERIFY_LOGIN = true;
$page_name = 'Register';
require_once "includes/db.php";
require_once "includes/head.php";
?>
<div class="main-login-container">
    <div class="col-md-5 login-container">

        <form action="authorize" method="POST" class="js-form own-target w-100">
            <div class="content-center flex-column">
                <h2 class="heading">Sign In to <?= SITE_NAME ?></h2>
                <p class="more mb-4">OR Use email account</p>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("user")  ?></span>
                        <input type="text" name="fname" class="form-control" placeholder="First Name" required>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("user")  ?></span>
                        <input type="text" name="lname" class="form-control" placeholder="Last Name" required>
                    </div>
                </div>
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
                        <input type="password" name="password" class="form-control u_password" placeholder="Password" required>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text"><?= svg_icon("lock")  ?></span>
                        <input type="password" name="c_password" class="form-control u_password" placeholder="Confirm Password" required>
                    </div>
                </div>
            </div>

            <div class="col-md-12 content-center flex-column">
                <input type="hidden" name="register_new_user" value="true">
                <button class="register btn w-100 mt-2 py-2" type="submit">Register</button>
                <p class="more my-1 mt-4">OR</p>
                <p class="more">Already have an account? <a href="login">Log In</a></p>
            </div>
        </form>
    </div>

</div>
<?php require_once "includes/foot.php" ?>