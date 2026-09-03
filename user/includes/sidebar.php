<?php
# Sidebar options for user
define('SIDEBAR_OPTIONS_USER', [
    [
        'title' => "Dashboard",
        'description' => "View all the statistics and reports",
        'icon' => 'th-large',
        'url' => 'dashboard',
    ],
    [
        'title' => "Profile Setting",
        'description' => "Manage your profile settings",
        'icon' => 'user-cog',
        'url' => _DIR_ . 'user/setting',
    ],
]);
?>

<div class="sidebar">

    <a href="dashboard" class="sidebar-brand">
        <span class="brand-mark"><?= strtoupper(substr(SITE_NAME, 0, 1)) ?></span>
        <span class="brand-text">
            <span class="brand-name"><?= SITE_NAME ?></span>
            <span class="brand-role">My account</span>
        </span>
    </a>

    <p class="nav-label">Menu</p>

    <ul class="nav">
        <?php foreach (SIDEBAR_OPTIONS_USER as $option) : ?>
            <li class="nav-item">
                <a href="<?= $option['url'] ?>" class="nav-link">
                    <?= svg_icon($option['icon']) ?>
                    <span class="text"><?= $option['title'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="sidebar-foot">
        <div class="user-info">
            <div class="user-image-container">
                <img src="<?= _DIR_ ?>images/users/<?= LOGGED_IN_USER['image'] ?>" alt="" class="user-img">
            </div>
            <div class="user-meta">
                <p class="user-name"><?= LOGGED_IN_USER['name'] ?></p>
                <p class="user-mail"><?= LOGGED_IN_USER['email'] ?></p>
            </div>
        </div>
        <a href="logout" class="sidebar-logout" title="Log out"><?= svg_icon("sign-out-alt") ?></a>
    </div>

</div>
