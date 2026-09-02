<?php
// SVG Icons 
$svg_names = [
    'times',
    "pdf",
    "file-alt",
    "file-archive",
    "file-video",
    "file",
    "check",
    "folder",
];
$svg_icons = [];
foreach ($svg_names as  $svg_name) {
    $svg_icons[$svg_name] = svg_icon($svg_name);
}
?>

<script>
    const SVG_ICONS = <?= json_encode($svg_icons) ?>;
</script>

<?php

assets_file([
    "Libraries/jquery.min.js",
    "Libraries/popper.min.js",
    "Libraries/bootstrap.min.js",
    "Functions/index.js",
    "components/Toaster.js",
    "components/Dialog.js",
    "Functions/functions.js",
    "Functions/prototype.fn.js",
    "Functions/jquery.fns.js",
    "Functions/forms.js",
], 'js', _DIR_ . "js");

assets_file($JS_FILES_, 'js', "js");
