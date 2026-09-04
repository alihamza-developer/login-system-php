<?php
require_once('includes/db.php');
$page_name = 'Cron';

require_once "includes/head.php";
require_once _DIR_ . 'cron/includes/Cron.php';
require_once _DIR_ . 'cron/includes/tasks.php';

$tasks = $_cron->all();

# Human readable interval
function every_label($seconds)
{
    if ($seconds % 86400 === 0) return ($seconds / 86400) . ' day(s)';
    if ($seconds % 3600 === 0) return ($seconds / 3600) . ' hour(s)';
    if ($seconds % 60 === 0) return ($seconds / 60) . ' minute(s)';
    return $seconds . ' second(s)';
}
?>
<div class="page-head">
    <h1 class="page-title">Cron</h1>
    <p class="page-sub">Scheduled cleanup tasks. Run by <code>php cron/index.php</code>.</p>
</div>

<div class="setting-stack">

    <div class="card">
        <div class="card-head">
            <div>
                <p class="card-title">Tasks</p>
                <p class="card-note">Each task runs at most once per interval.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="device-list">
                <?php foreach ($tasks as $task) { ?>
                    <div class="device-row">
                        <span class="device-icon"><?= svg_icon('cog') ?></span>
                        <div class="device-info">
                            <p class="device-name">
                                <?= $task['title'] ?>
                                <?php if ($task['running']) { ?>
                                    <span class="pill pill-warn">Running</span>
                                <?php } elseif ($task['last_status'] === 'error') { ?>
                                    <span class="pill pill-danger">Failed</span>
                                <?php } elseif ($task['due']) { ?>
                                    <span class="pill pill-warn">Due</span>
                                <?php } elseif ($task['last_status'] === 'ok') { ?>
                                    <span class="pill pill-ok">Up to date</span>
                                <?php } ?>
                            </p>
                            <p class="device-meta">
                                every <?= every_label($task['every']) ?> &middot;
                                <?php if ($task['runs']) { ?>
                                    last ran <?= date('j M Y, g:i a', strtotime($task['last_run_at'])) ?>
                                    in <?= $task['duration_ms'] ?>ms &middot; <?= $task['last_message'] ?>
                                <?php } else { ?>
                                    never run
                                <?php } ?>
                            </p>
                        </div>
                        <button class="btn btn-ghost btn-sm jx-req-element" data-target="cron"
                            data-submit='{"run_task": 1, "name": "<?= $task['name'] ?>"}'>Run now</button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

</div>
<?php require_once('./includes/foot.php'); ?>
