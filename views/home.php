<?php
require_once('includes/db.php');
$page_name = 'Home';
$CSS_FILES_[] = 'home.css';
require_once('includes/head.php');
require_once global_file('navbar');
?>
<div class="all-content full-width">
    <div class="home-page">

        <section class="home-hero">
            <span class="hero-eyebrow">Authentication starter</span>
            <h1 class="hero-title"><?= SITE_NAME ?></h1>
            <p class="hero-text">
                A small, readable PHP login system you can drop into any project.
                Email verification, password reset, and a clean admin panel out of the box.
            </p>
            <div class="hero-actions">
                <?php if (LOGGED_IN_USER) { ?>
                    <a href="<?= url('user/dashboard') ?>" class="btn btn-primary">Go to dashboard</a>
                    <?php if (IS_ADMIN) { ?>
                        <a href="<?= url('admin/dashboard') ?>" class="btn btn-ghost">Admin panel</a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?= url('register') ?>" class="btn btn-primary">Create an account</a>
                    <a href="<?= url('login') ?>" class="btn btn-ghost">Sign in</a>
                <?php } ?>
            </div>
        </section>

        <section class="home-features">
            <div class="feature-item">
                <span class="feature-icon"><?= svg_icon("lock") ?></span>
                <h3 class="feature-title">Hashed passwords</h3>
                <p class="feature-text">
                    Passwords are stored with PHP's own hashing, never in plain text,
                    and re-hashed automatically when the algorithm changes.
                </p>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><?= svg_icon("mail") ?></span>
                <h3 class="feature-title">Verified by email</h3>
                <p class="feature-text">
                    New accounts confirm their address before signing in, and password
                    resets run on expiring single-use links.
                </p>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><?= svg_icon("users") ?></span>
                <h3 class="feature-title">Two panels ready</h3>
                <p class="feature-text">
                    A user area for profile and password, and an admin area for managing
                    accounts and email templates.
                </p>
            </div>
        </section>

    </div>
</div>
<?php require_once('includes/foot.php'); ?>
