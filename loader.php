<?php

// This can be found at the top of all pages. Basically prevents loading the file directly and running any code and exposing errors.
if (!defined('TIMECLOCK')) define('TIMECLOCK', dirname(__FILE__).'/');


include_once('./includes/functions.php'); 
include_once('./includes/user.class.php');
include_once('./includes/message.class.php');
include_once('./includes/version.php');

session();

$user = new User($_SESSION['sid']);

if (isset($_POST['addNote'])) {
    
    if (strlen($_POST['note']) <= 128) {
    
        if ($user->addNote($_POST['note'])) {
            $message = new Message("Successfully added note!", "alert-success");
        } else {
            $message = new Message("Error! Could not add note.", "alert-danger");
        }
    } else {
        $message = new Message("Error! Note has exceeded 128 characters.", "alert-danger");
    }
}

if (isset($_POST['add_earnings'])) {
    
    if ($_POST['inputEarnings'] != "") {
    
        if (is_numeric($_POST['inputEarnings'])) {
            if ($user->addEarnings($_POST['inputEarnings'])) {
                $message = new Message("Successfully added earnings!", "alert-success");
            } else {
                $message = new Message("Error! Could not add earnings.", "alert-danger");
            }
        } else {
            $message = new Message("Error! Earnings not numeric", "alert-danger");
        }
        
    } else {
        // if earnings was empty, it's cool, just go on
        header("Location: /");
    }
}

if (isset($_POST['punch']) || isset($_POST['punchWithNote'])) {
    
    $punchSettings = array(
        "event" => "Punch",
    );
	
	$result = $user->punch($punchSettings);

	if ($result['success'] == 1) {
		$punchMessage = "Punch accepted for ".date('H:i M d, Y',strtotime($result['timestamp']));
		$punchStyle = "alert-success";
		$_SESSION['newEntry'] = 1;
		
		if (isset($_POST['punchWithNote'])) {
            $_SESSION['requestingNote'] = true;
        } 
	} else {
		$punchMessage = $result['return'];
		$punchStyle = "alert-danger";
	}
	
	$message = new Message($punchMessage, $punchStyle, TRUE);
	
	if ($user->isFreelance() && $user->askForEarnings()) {
	    $_SESSION['ask_for_earnings'] = true;
	}
	
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
    if (!isset($_POST['export'])) {
        include('./template/default/header.php');
    }
    include('./pages/statistics.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "edit") {
    include('./template/default/header.php');
    include('./pages/edit.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "punch") {
    include('./template/default/header.php');
    include('./pages/punch.php');
    include('./template/default/footer.php');
} elseif ($requestURI[0] == "admin") {
    include('./pages/admin.php');
    include('./template/default/footer.php');
} elseif ($reqyestURI[0] == "export") {
    include('./pages/export.php');
} else {
	http_response_code(404);
	include('./template/default/header.php');
	include('./pages/notfound.php');
	include('./template/default/footer.php');
}