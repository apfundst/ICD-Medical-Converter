<?

include_once('includes/util.php');

if (!$_POST) {
    json_failure("No post was passed");
}

if (!array_key_exists('action', $_POST)) {
    json_failure("No action exists in _POST");
}

include_once('includes/presenter.php');

$action = $_POST['action'];

if (!function_exists($action)) {
    json_failure("The specificed action does not exist");
}

call_user_func($action);

