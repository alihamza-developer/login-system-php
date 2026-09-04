<?php
assets_file([
    'Libraries/font-awesome.min.css',
    'Libraries/bootstrap.min.css',
    'custom.css', # Global File
    'styles.css', # Global Styles (inputs,buttons,etc...)

    # Components
    'components/toaster.css',
    'components/dialog.css',
    'components/checkbox.css',
    'components/loader.css',
    'components/popup-window.css',
    'components/dropdown.css',
], 'css', _DIR_ . "css");
?>
<?php $CSS_FILES_ = isset($CSS_FILES_) ? $CSS_FILES_ : []; ?>