<?php
require_once('includes/db.php');
$page_name = 'Settings';

require_once('./includes/head.php');

$verified = LOGGED_IN_USER['verify_status'] == 1;
?>
<div class="page-head">
    <h1 class="page-title">Settings</h1>
    <p class="page-sub">Update your details and password.</p>
</div>

<div class="setting-stack">

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-title">Personal information</p>
                <p class="card-note">This is what other people see.</p>
            </div>
        </div>

        <div class="card-body">
            <form action="user" method="POST" class="ajax_form">

                <div class="setting-row">
                    <div class="avatar-upload">
                        <div class="avatar-preview h-100">
                            <img src="<?= url('images/users/' . LOGGED_IN_USER['image']) ?>" alt="avatar" class="img-fluid avatar-img-preivew">
                        </div>
                        <label class="avatar-upload-overlay" title="Change photo">
                            <input type="file" class="d-none avatar-upload-input file-preview-input" name="avatar" accept="image/*" data-target=".avatar-img-preivew">
                            <?= svg_icon("camera") ?>
                        </label>
                    </div>

                    <div class="setting-fields">
                        <div class="field-row">
                            <div class="form-group">
                                <label class="field-label" for="fname">First name</label>
                                <input type="text" id="fname" class="form-control" name="fname" value="<?= LOGGED_IN_USER['fname'] ?>" required data-length="[1,250]">
                            </div>
                            <div class="form-group">
                                <label class="field-label" for="lname">Last name</label>
                                <input type="text" id="lname" class="form-control" name="lname" value="<?= LOGGED_IN_USER['lname'] ?>" required data-length="[1,250]">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="field-label" for="email_display">Email address</label>
                            <input type="email" id="email_display" class="form-control" value="<?= LOGGED_IN_USER['email'] ?>" disabled>
                            <p class="field-note">
                                <?php if ($verified) { ?>
                                    <span class="pill pill-ok">Verified</span>
                                <?php } else { ?>
                                    <span class="pill pill-warn">Not verified</span>
                                <?php } ?>
                                Your email can't be changed here.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <input type="hidden" name="update_personal_information" value="<?= bc_code(); ?>">
                    <button class="btn btn-primary" type="submit"><?= svg_icon("save") ?> Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-title">Change password</p>
                <p class="card-note">Pick something you don't use anywhere else.</p>
            </div>
        </div>

        <div class="card-body">
            <form action="user" method="POST" class="ajax_form reset">

                <div class="form-group form-group-half">
                    <label class="field-label" for="current_password">Current password</label>
                    <input type="password" id="current_password" class="form-control" name="current_password" required autocomplete="current-password">
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label class="field-label" for="new_password">New password</label>
                        <input type="password" id="new_password" class="form-control u_password" name="new_password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="field-label" for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" class="form-control u_password" name="confirm_password" required data-length="[<?= AUTH_PASSWORD_MIN ?>,250]" autocomplete="new-password">
                    </div>
                </div>

                <div class="form-actions">
                    <input type="hidden" name="change_password" value="<?= bc_code(); ?>">
                    <button class="btn btn-primary" type="submit"><?= svg_icon('save') ?> Update password</button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php require_once('./includes/foot.php'); ?>
