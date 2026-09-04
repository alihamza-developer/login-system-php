<nav class="navbar">
    <a class="logo page-name" href="dashboard">
        Admin Dashboard
    </a>
    <div class="menu">
        <div class="dropdown">
            <button class="dropdown-toggle menu-item no-arrow-icon" type="button" data-toggle="dropdown" data-display="static">
                <img src="../images/users/<?= LOGGED_IN_USER['image']; ?>" alt="user-img" class="user-img">
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-user">
                    <span class="user-name"><?= LOGGED_IN_USER['name'] ?></span>
                    <span class="user-mail"><?= LOGGED_IN_USER['email'] ?></span>
                </div>
                <div class="dropdown-divider"></div>
                <a href="<?= _DIR_ ?>user/dashboard" class="dropdown-item"><?= svg_icon("th-large") ?> <span class="text">User dashboard</span></a>
                <a href="<?= _DIR_ ?>user/setting" class="dropdown-item"><?= svg_icon("user-cog") ?> <span class="text">Profile setting</span></a>
                <a href="<?= _DIR_ ?>user/logout" class="dropdown-item is-danger"><?= svg_icon("sign-out-alt") ?> <span class="text">Logout</span></a>
            </div>
        </div>
    </div>
</nav>