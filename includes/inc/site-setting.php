<?php

$ASSETS_TEPLATES_ = [
    'dataTable' => [
        'css' => [
            url('css/jquery.dataTables.min.css'),
            url('css/components/datatable.css'),
        ],
        'js' => [
            url('js/Libraries/jquery.dataTables.min.js'),
            url('js/components/datatable.js'),
        ]
    ],
    "sortable" => [
        "css" => [
            url("css/jquery-ui.min.css")
        ],
        "js" => [
            url("js/jquery-ui.min.js"),
            url("js/jquery.ui.touch-punch.min.js"),
        ]
    ],
    "lightBox" => [
        "css" => [
            url("css/lightbox.min.css"),
        ],
        "js" => [
            url("js/lightbox.js"),
        ]
    ],
    'html2canvas' => [
        'js' => [
            url('js/html2canvas.min.js')
        ]
    ],
    'owlCarousel' => [
        'css' => [
            url('css/owl.carousel.min.css'),
            url('css/owl-carousel.css')
        ],
        'js' => [
            url('js/owl.carousel.min.js'),
            url('js/owl-carousel.js')
        ]
    ],
    'tinyMCE' => [
        'js' => [
            url('js/tinymce/tinymce.min.js'),
        ]
    ],
    'dashboard' => [
        'css' => [
            url('css/dashboard/index.css'),
            url('css/dashboard/sidebar.css'),
            url('css/dashboard/navbar.css'),
            url('css/components/avatar-upload.css'),
        ],
        'js' => [
            url('js/dashboard/sidebar.js'),
        ]
    ]
];


// Assets template load function that will add css and js files to the variables $CSS_FILES_ and $JS_FILES_
function add_assets_template($template_names, $position = 'first')
{
    global $ASSETS_TEPLATES_, $CSS_FILES_, $JS_FILES_;

    $template_names = explode(',', $template_names);

    foreach ($template_names as $template_name) {

        $template = arr_val($ASSETS_TEPLATES_, $template_name);

        if (!$template) return false;
        $css = arr_val($template, 'css', []);
        $js = arr_val($template, 'js', []);
        foreach ($css as $file) {
            if ($position == 'first') array_unshift($CSS_FILES_, $file);
            else $CSS_FILES_[] = $file;
        }
        foreach ($js as $file) {
            if ($position == 'first') array_unshift($JS_FILES_, $file);
            else $JS_FILES_[] = $file;
        }
    }

    return true;
}
