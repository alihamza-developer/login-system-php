<?php
assets_file([
    'Libraries/font-awesome.min.css',
    'Libraries/bootstrap.min.css',
    'custom.css',
    'styles.css',
    'components/Toaster.css',
    'components/Dialog.css',
    'components/checkbox.css',
    'components/loader.css',
    'components/popup-window.css',
], 'css', _DIR_ . "css");
?>
<?php $CSS_FILES_ = isset($CSS_FILES_) ? $CSS_FILES_ : []; ?>