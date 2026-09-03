<?php
require_once('includes/db.php');
$page_name = 'Dashboard';

require_once "includes/head.php";

$week_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

$total_users = $db->count('users', []);
$verified_users = $db->count('users', ['verify_status' => 1]);
$unverified_users = $total_users - $verified_users;
$admin_users = $db->count('users', ['is_admin' => 1]);
$new_users = (int) $db->query("SELECT COUNT(1) c FROM `users` WHERE `date_added` > '$week_ago'", ['select_query' => true])[0]['c'];

$recent = $db->query("SELECT `name`, `email`, `image`, `is_admin`, `verify_status`, `date_added` FROM `users` ORDER BY `id` DESC LIMIT 6", ['select_query' => true]);
?>
<div class="page-head">
    <h1 class="page-title">Admin dashboard</h1>
    <p class="page-sub">Accounts and activity across <?= SITE_NAME ?>.</p>
</div>

<div class="stat-grid">
    <div class="stat-item">
        <p class="stat-label">Total users</p>
        <p class="stat-value"><?= number_format($total_users) ?></p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Verified</p>
        <p class="stat-value"><?= number_format($verified_users) ?></p>
        <p class="stat-note"><?= number_format($unverified_users) ?> still pending</p>
    </div>
    <div class="stat-item">
        <p class="stat-label">New this week</p>
        <p class="stat-value"><?= number_format($new_users) ?></p>
        <p class="stat-note">Signed up in the last 7 days</p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Administrators</p>
        <p class="stat-value"><?= number_format($admin_users) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <p class="card-title">Recent sign-ups</p>
            <p class="card-note">The <?= count($recent) ?> most recent accounts.</p>
        </div>
        <a href="users" class="btn btn-ghost btn-sm">All users</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!count($recent)) { ?>
                    <tr>
                        <td colspan="4" class="table-empty">No accounts yet.</td>
                    </tr>
                <?php } ?>
                <?php foreach ($recent as $user) : ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <img src="<?= url('images/users/' . $user['image']) ?>" alt="" class="user-cell-img">
                                <div>
                                    <p class="user-cell-name"><?= $user['name'] ?: 'Unnamed' ?></p>
                                    <p class="user-cell-mail"><?= $user['email'] ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="pill <?= $user['is_admin'] == 1 ? 'pill-accent' : 'pill-muted' ?>">
                                <?= $user['is_admin'] == 1 ? 'Admin' : 'Member' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['verify_status'] == 1) { ?>
                                <span class="pill pill-ok">Verified</span>
                            <?php } else { ?>
                                <span class="pill pill-warn">Pending</span>
                            <?php } ?>
                        </td>
                        <td class="cell-muted"><?= date('j M Y', strtotime($user['date_added'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once "includes/foot.php"; ?>
