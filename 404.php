<?php
http_response_code(404);
$page_name = "Page not found";

$CSS_FILES_[] = '404.css';
include_once "includes/head.php";
?>
<div class="error-page">
    <div class="error-card">
        <span class="error-code">404</span>
        <h1 class="error-heading">Page not found</h1>
        <p class="error-text">
            The page you're looking for may have been moved, renamed, or never existed.
        </p>
        <a href="<?= SITE_URL ?>" class="btn btn-primary">Go to home</a>
    </div>
</div>
<?php require_once("includes/foot.php") ?>
