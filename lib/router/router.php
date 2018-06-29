<?php
#
#
# Router
# https://github.com/morswin22/router/
#
# (c) 2018 Patryk Janiak
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#
require_once './lib/Twig/autoloader.php';

Twig_AutoLoader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);
$route_found = false;

session_start();
if (!isset($_SESSION['previous'])) {
    $_SESSION['previous'] = $_SERVER['REQUEST_URI'];;
}
if (!isset($_SESSION['recover'])) {
    $_SESSION['recover'] = $_SERVER['REQUEST_URI'];
}
$previous_page = $_SESSION['previous'];
if ($_SERVER['REQUEST_URI'] != $_SESSION['previous'] && $_SERVER['REQUEST_URI'] != '/favicon.ico') {
    $_SESSION['recover'] = $_SESSION['previous'];
    $_SESSION['previous'] = $_SERVER['REQUEST_URI'];
}
if ($_SERVER['REQUEST_URI'] != $_SESSION['recover'] && $_SERVER['REQUEST_URI'] == $_SESSION['previous']) {
    $previous_page = $_SESSION['recover'];
}

function redirect($path = '/') {
    header('Location: '.$path);
}

function error(int $code, $render = true) {
    http_response_code($code);
    if ($render == true) {
        $p = array('code'=>$code);
        if (is_file(__DIR__.'/../../templates/error.html')) {
            render('error.html', $p);
        } else {
            copy(__DIR__.'/error.html',__DIR__.'/../../templates/error.html');
            render('error.html', $p);
            unlink(__DIR__.'/../../templates/error.html');
        }
    }
    exit();
}

function getRegex($pattern){
    if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern))
        return false; // Invalid pattern

    // Turn "(/)" into "/?"
    $pattern = preg_replace('#\(/\)#', '/?', $pattern);

    // Create capture group for ":parameter"
    $allowedParamChars = '[a-zA-Z0-9\_\-.%()]+';
    $pattern = preg_replace(
        '/:(' . $allowedParamChars . ')/',   # Replace ":parameter"
        '(?<$1>' . $allowedParamChars . ')', # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
        $pattern
    );

    // Create capture group for '{parameter}'
    $pattern = preg_replace(
        '/{('. $allowedParamChars .')}/',    # Replace "{parameter}"
        '(?<$1>' . $allowedParamChars . ')', # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
        $pattern
    );

    // Add start and end matching
    $patternAsRegex = "@^" . $pattern . "$@D";

    return $patternAsRegex;
}

function route($path, $callback) {
    global $_SERVER, $route_found;

    $patternAsRegex = getRegex($path);

    if ($ok = !!$patternAsRegex) {
        // We've got a regex, let's parse a URL
        if ($ok = preg_match($patternAsRegex, $_SERVER['REQUEST_URI'], $matches)) {
            // Get elements with string keys from matches
            $params = array_intersect_key(
                $matches,
                array_flip(array_filter(array_keys($matches), 'is_string'))
            );
        }
    }

    if (!isset($params)) { $params = array(); }

    if ($ok) {
        $route_found = true;
        $callback($params);
        exit();
    }
}

function render($path, $args = array()) {
    global $twig, $previous_page; 
    $args['previous_page'] = $previous_page;
    echo $twig->render($path, $args);
}

register_shutdown_function(function() {
    global $route_found;
    if (!$route_found) {
        error(404);
    }
});

?>