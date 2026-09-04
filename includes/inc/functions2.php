<?php
require_once _DIR_ . "includes/svg-icons.php";
// Get svg icon
function svg_icon($name, $size = "17", $color = "#000", $height = false)
{
    global $SITE_ICONS;

    $height_ = $size;
    if ($height) $height_ = "$height";

    $svg = isset($SITE_ICONS[$name]) ? $SITE_ICONS[$name] : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"/></svg>';

    $svg = str_replace("<svg ", "<svg width='{$size}' height='{$height_}' fill='{$color}' ", $svg);
    return $svg;
}

// Readable device name
function browser_label($user_agent)
{
    if (!$user_agent) return 'Unknown device';

    $browsers = ['Edg' => 'Edge', 'OPR' => 'Opera', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'];
    $systems = ['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad', 'Mac OS' => 'macOS', 'Linux' => 'Linux'];

    $browser = 'Unknown browser';
    foreach ($browsers as $needle => $label) {
        if (strpos($user_agent, $needle) !== false) {
            $browser = $label;
            break;
        }
    }
    $system = '';
    foreach ($systems as $needle => $label) {
        if (strpos($user_agent, $needle) !== false) {
            $system = $label;
            break;
        }
    }
    return $system ? "$browser on $system" : $browser;
}
// Global CSS Files
function global_file($filename)
{
    $filename = rtrim($filename, '.php') . ".php";
    return _DIR_ . "components/global/" . $filename;
}
// CSS & JS file
function assets_file($file, $type, $attach_path = null)
{
    if (is_array($file)) {
        // Multiple files
        foreach ($file as $single_file) {
            assets_file($single_file, $type, $attach_path);
        }
        return true;
    }
    // Single file
    if (
        !strstr($file, 'http') &&
        !strstr($file, '//') &&
        !strstr($file, './')
    ) {
        $file = _rtrim($file, ".$type") . ".$type";
        $file .= ASSETS_V;
        $attach_path = is_null($attach_path) ? '' : $attach_path;
        $file = merge_path($attach_path, $file);
    }
    # Our own full urls still need the cache buster
    elseif (strpos($file, SITE_URL) === 0 && strpos($file, '?') === false) {
        $file .= ASSETS_V;
    }
    if ($type === 'css') {
        echo "
        <link rel='stylesheet' href='$file'>";
    } elseif ($type === 'js') {
        echo "
            <script src='$file'></script>";
    }
}

// Load Assets Template Fn
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

// show message page
function showMsgPage($options)
{
    extract($options);
    $returnData = arr_val($options, 'return');
    if ($returnData) {
        ob_start();
        include _DIR_ . "components/msg.php";
        $contents = ob_get_contents();
        ob_get_clean();
        return $contents;
    }
    require(_DIR_ . "components/msg.php");

    $exit = arr_val($options, 'exit', true);
    if ($exit)
        die();
}
// Error Msg Page
function errorMsgPage($msg = "Error Please Try Again!", $options = [])
{
    $options['exit'] = true;
    $options['msg'] = $msg;
    $options['type'] = 'error';
    showMsgPage($options);
}
// success Msg Page
function successMsgPage($msg, $options = [])
{
    $options['exit'] = true;
    $options['msg'] = $msg;
    $options['type'] = 'success';
    showMsgPage($options);
}
// JS Message
function js_msg($type, $msg, $heading = null)
{
    if (is_null($heading)) $heading = $type;
    $options = [
        'type' => $type
    ];
    return "sAlert('$msg', '$heading', " . json_encode($options) . ")";
}
// Is Image File
function is_image_file($file_name)
{
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'jfif');
    $getExt = explode('.', $file_name);
    $ext = strtolower(end($getExt));
    if (in_array($ext, $allowed_ext)) {
        return $ext;
    } else {
        return false;
    }
}
// Get current url
function get_current_url()
{
    $url = "http";
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $url .= "s";
    }
    $url .= "://";
    $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    return $url;
}

// check if parameter in request
function is_request_param($type, $param_name, $user_auth = false)
{
    $value = false;
    if ($type === "POST") $value = isset($_POST[$param_name]);
    else if ($type === "GET") $value = isset($_GET[$param_name]);
    else if ($type === "FILES") $value = isset($_FILES[$param_name]);
    if (!$value) return false;
    if ($user_auth) {
        if (!LOGGED_IN_USER) return false;
    }
    return true;
}
// Check if parameter in post request
function is_post($param_name, $user_auth = false)
{
    return is_request_param("POST", $param_name, $user_auth);
}

// convert https url to www
function get_www_url($url)
{
    // Remove the 'https://' part if it exists
    $url = preg_replace('#^https?://#', '', $url);

    // Add 'www.' if it doesn't already exist
    if (strpos($url, 'www.') !== 0) {
        $url = 'www.' . $url;
    }

    // Remove trailing slashes
    $url = rtrim($url, '/');

    return $url;
}

// Create Slug
function to_slug($title)
{
    $title = to_title_case($title); // Convert To Title Case
    $slug = strtolower($title); // To Lowercase
    $slug = preg_replace('/[\s_]/', '-', $slug); // Replace spaces,underscores,hyphens
    $slug = preg_replace('/[^\w\-]/', '', $slug); // Remove special characters
    $slug = preg_replace('/\-\-+/', '-', $slug); // Remove consecutive hyphens
    $slug = trim($slug, '-'); // Trim leading and trailing hyphens
    return $slug;
}
