<nav class="site-navbar">
    <a class="site-brand" href="<?= url('home') ?>">
        <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
        <span class="brand-name"><?= SITE_NAME ?></span>
    </a>

    <div class="site-menu">
        <?php
        if (!LOGGED_IN_USER) {
        ?>
            <a href="<?= url('login') ?>" class="btn btn-ghost">Sign in</a>
            <a href="<?= url('register') ?>" class="btn btn-primary">Create account</a>
        <?php
        } else {
        ?>
            <div class="dropdown">
                <button class="dropdown-toggle menu-item no-arrow-icon" type="button" data-toggle="dropdown" data-display="static">
                    <img src="<?= url('images/users', LOGGED_IN_USER['image']) ?>" alt="user-img" class="user-img">
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-user">
                        <span class="user-name"><?= LOGGED_IN_USER['name'] ?></span>
                        <span class="user-mail"><?= LOGGED_IN_USER['email'] ?></span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?= url('user/dashboard') ?>" class="dropdown-item"><?= svg_icon("th-large") ?> <span class="text">Dashboard</span></a>
                    <?php if (IS_ADMIN) { ?>
                        <a href="<?= url('admin/dashboard') ?>" class="dropdown-item"><?= svg_icon("user-cog") ?> <span class="text">Admin panel</span></a>
                    <?php } ?>
                    <a href="<?= url('logout') ?>" class="dropdown-item is-danger"><?= svg_icon("sign-out-alt") ?> <span class="text">Logout</span></a>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</nav>
