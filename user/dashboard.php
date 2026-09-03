<?php
require_once('includes/db.php');
$page_name = 'Dashboard';
require_once('./includes/head.php');

$verified = LOGGED_IN_USER['verify_status'] == 1;
$member_since = date('j M Y', strtotime(LOGGED_IN_USER['date_added']));
?>
<div class="page-head">
    <h1 class="page-title">Welcome back, <?= LOGGED_IN_USER['fname'] ?></h1>
    <p class="page-sub">Here's the state of your account.</p>
</div>

<div class="card profile-card">
    <div class="card-body">
        <div class="profile-row">
            <img src="<?= url('images/users/' . LOGGED_IN_USER['image']) ?>" alt="" class="profile-avatar">
            <div class="profile-info">
                <p class="profile-name"><?= LOGGED_IN_USER['name'] ?></p>
                <p class="profile-email"><?= LOGGED_IN_USER['email'] ?></p>
                <div class="pill-row">
                    <?php if ($verified) { ?>
                        <span class="pill pill-ok">Email verified</span>
                    <?php } else { ?>
                        <span class="pill pill-warn">Email not verified</span>
                    <?php } ?>
                    <?php if (IS_ADMIN) { ?>
                        <span class="pill pill-accent">Admin</span>
                    <?php } ?>
                </div>
            </div>
            <a href="setting" class="btn btn-ghost btn-sm profile-edit">Edit profile</a>
        </div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-item">
        <p class="stat-label">Member since</p>
        <p class="stat-value"><?= $member_since ?></p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Account status</p>
        <p class="stat-value"><?= $verified ? 'Active' : 'Pending' ?></p>
        <p class="stat-note"><?= $verified ? 'Everything checks out' : 'Verify your email to finish' ?></p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Role</p>
        <p class="stat-value"><?= IS_ADMIN ? 'Administrator' : 'Member' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <p class="card-title">Account settings</p>
            <p class="card-note">Update your details or change your password.</p>
        </div>
        <a href="setting" class="btn btn-primary btn-sm">Open settings</a>
    </div>
</div>
<?php require_once('./includes/foot.php'); ?>
