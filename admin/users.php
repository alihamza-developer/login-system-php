<?php
require_once('includes/db.php');
$page_name = 'Users';

add_assets_template('dataTable');
require_once "includes/head.php";

$users = $db->select('users', '*', [
    'id' => [
        'operator' => '!=',
        'value' => LOGGED_IN_USER_ID
    ]
], [
    'order_by' => 'id DESC',
]);

$total = count($users);
$verified = 0;
foreach ($users as $user) {
    if ($user['verify_status'] == 1) $verified++;
}
?>
<div class="page-head">
    <h1 class="page-title">Users</h1>
    <p class="page-sub">Everyone with an account, except you.</p>
</div>

<div class="stat-grid">
    <div class="stat-item">
        <p class="stat-label">Total</p>
        <p class="stat-value"><?= number_format($total) ?></p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Verified</p>
        <p class="stat-value"><?= number_format($verified) ?></p>
    </div>
    <div class="stat-item">
        <p class="stat-label">Pending</p>
        <p class="stat-value"><?= number_format($total - $verified) ?></p>
        <p class="stat-note">Haven't confirmed their email</p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <p class="card-title">All users</p>
            <p class="card-note">Toggle admin access or remove an account.</p>
        </div>
    </div>

    <div class="table-wrap">
        <?php if ($total) { ?>
            <table class="table dataTable users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Admin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
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
                            <td class="cell-muted"><?= date('j M Y', strtotime($user['date_added'])) ?></td>
                            <td>
                                <?php if ($user['verify_status'] == 1) { ?>
                                    <span class="pill pill-ok">Verified</span>
                                <?php } else { ?>
                                    <span class="pill pill-warn">Pending</span>
                                <?php } ?>
                            </td>
                            <td>
                                <input type="checkbox" class="fancy-checkbox jx-req-element" data-submit='{"user_id": "<?= $user['id'] ?>"}' data-target="users" name="modifyUserIsAdmin" <?= $user['is_admin'] == "1" ? "checked" : "" ?>>
                            </td>
                            <td class="row-action">
                                <button class="icon-btn delete-btn" title="Delete user" data-target="<?= $user['id'] ?>" data-action="user">
                                    <?= svg_icon("trash-alt") ?>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="table-empty">No other users yet.</p>
        <?php } ?>
    </div>
</div>
<?php require_once('./includes/foot.php'); ?>
