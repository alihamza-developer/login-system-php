<?php
require_once('includes/db.php');
$page_name = 'Settings';

require_once "includes/head.php";
?>
<div class="page-head">
    <h1 class="page-title">Settings</h1>
    <p class="page-sub">Outgoing mail configuration.</p>
</div>

<div class="setting-stack">

    <form action="settings" method="POST" class="ajax_form">

        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-title">Mail</p>
                    <p class="card-note">Outgoing SMTP credentials.</p>
                </div>
            </div>

            <div class="card-body">
                <div class="field-row">
                    <div class="form-group">
                        <label class="field-label" for="smtp_host">SMTP host</label>
                        <input type="text" id="smtp_host" class="form-control" name="smtp_host" value="<?= SMTP_HOST ?>">
                    </div>
                    <div class="form-group">
                        <label class="field-label" for="smtp_port">Port</label>
                        <input type="text" id="smtp_port" class="form-control" name="smtp_port" value="<?= SMTP_PORT ?>">
                    </div>
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label class="field-label" for="smtp_user">Username</label>
                        <input type="text" id="smtp_user" class="form-control" name="smtp_user" value="<?= SMTP_USER ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="field-label" for="smtp_secure">Encryption</label>
                        <select id="smtp_secure" class="form-control" name="smtp_secure">
                            <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'] as $key => $label) { ?>
                                <option value="<?= $key ?>" <?= SMTP_SECURE === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group form-group-half">
                    <label class="field-label" for="smtp_pass">Password</label>
                    <input type="password" id="smtp_pass" class="form-control" name="smtp_pass" value="<?= htmlspecialchars(SMTP_PASS, ENT_QUOTES) ?>" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="save_settings" value="<?= bc_code(); ?>">
            <button class="btn btn-primary" type="submit"><?= svg_icon('save') ?> Save settings</button>
        </div>
    </form>

</div>
<?php require_once('./includes/foot.php'); ?>