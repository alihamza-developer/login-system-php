<?php
require_once "./includes/db.php";

$current_url = get_current_url();
// Remove www and redirect
if (strpos($current_url, "www.") !== false) {
    $current_url = str_replace("www.", "", $current_url);
    header("Location: $current_url");
    exit;
}
$current_url = str_replace("www.", "", $current_url);
$path = str_replace(SITE_URL, "", $current_url);
$path = explode('?', $path)[0];
$path = trim($path, "/");
$path_arr = explode("/", $path);


require_once "includes/routes.php";

$filename = null;
$path = $path == "" ? "/" : $path;
// is contain routes variables
function is_contain_routes_vars($url)
{
    return preg_match('/(\{\w*\})/m', $url);
}

// Pages
foreach ($routes as $url => $file) {
    $url = trim($url, "/");
    if (empty($url)) $url = "/";
    if ($url == $path) {
        $filename = $file;
        break;
    }
    // Check if url contains parameters (e.g. /user/{user_id})
    else if (preg_match('/(\{\w*\})/m', $url, $matches)) {
        $url_arr = explode("/", $url);
        if (count($url_arr) == count($path_arr)) {
            // Start matching
            $is_valid = true;
            foreach ($url_arr as $i => $routes_path) {
                $url_path = $path_arr[$i];
                // Check if is variable
                if (is_contain_routes_vars($routes_path)) {
                    $routes_path = ltrim($routes_path, '{');
                    $routes_path = rtrim($routes_path, '}');
                    $_GET[$routes_path] = $url_path;
                } else {
                    if ($url_path != $routes_path) {
                        $is_valid = false;
                        break;
                    }
                }
            }
            if ($is_valid) {
                $filename = $file;
                break;
            }
        }
    }
}

// Require file
if (is_null($filename)) {
    _404();
} else {
    $GLOBALS['FILE_NAME'] = $filename;
}
$filename = "views/" . $filename;

$filename .= ".php";
$GLOBALS['SCRIPT_FILENAME'] = $filename;
$GLOBALS['FILE_PATH'] = $path;
require_once $filename;
