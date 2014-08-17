<?php

if (!defined('TIMECLOCK')) define('TIMECLOCK', dirname(__FILE__).'/');

include_once('./includes/functions.php'); 
include_once('./includes/user.class.php');
include_once('./includes/message.class.php');
session();

$user = new User($_SESSION['sid']);

if (isset($_POST['punch'])) {
    
    $punchSettings = array(
        "event" => "Punch",
    );
	
	$result = $user->punch($punchSettings);

	if ($result['success'] == 1) {
		$punchMessage = "Punch accepted for ".date('H:i M d, Y',strtotime($result['timestamp']));
		$punchStyle = "alert-success";
		$_SESSION['newEntry'] = 1;
	} else {
		$punchMessage = $result['return'];
		$punchStyle = "alert-danger";
	}
	
	$message = new Message($punchMessage, $punchStyle, TRUE);
	
	unset($_POST['punch']);
    header("Location: /");
    exit();
}

// Grabbing the variables after the domain and determining the pages to load.
$pageName = ereg_replace("[^A-Za-z0-9]", "", trim($_SERVER['REQUEST_URI']) );
$requestURI = explode('/',trim($_SERVER['REQUEST_URI'],'/'));

if ($requestURI[0] == "" || $requestURI[0] == "index.php") {
    $main = true;
	include('./template/default/header.php');
    include('./pages/main.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "schedule") {
    include('./template/default/header.php');
    include('./pages/schedule.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "statistics") {
    include('./template/default/header.php');
    echo "statistics";
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "edit") {
    include('./template/default/header.php');
    include('./pages/edit.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "admin") {
    include('./pages/admin.php');
    include('./template/default/footer.php');
} else {
	http_response_code(404);
	include('./template/default/header.php');
	include('./pages/notfound.php');
	include('./template/default/footer.php');
}