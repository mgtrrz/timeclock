<?php
include_once('./config.php');
require('./lib/password.php');

function getUserIP() {
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

function communicate($request, $obj = "") {
    
    try {

        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($request == "check_password") {
            if (empty($obj)) {
                // We need $obj passed, so if it's empty, throw an exception
                echo "Object required on function call with specified action";
                exit;
            }
            
            // Grab some basic information
            $data = $db->prepare('SELECT password, staff_id, first_name, last_name FROM users WHERE username=:usr');
            $data->bindParam(':usr', $obj['username']);
            $data->execute();
            $result = $data->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($obj['password'], $result['password'])) {
                // password matches, return the staff ID
                return $result;
            } else {
                return false;
            }
        	
        } else {
            echo "Warning: communicate called without request or object";
        }

        
    } catch(PDOException $db) {
        //echo $errorMsg;
        echo 'ERROR: ' . $db->getMessage();
    }

    $db = null;
}

function timeBetweenDates($startDate, $endDate = "") {
	if ($endDate == "") {
		$endDate = date("Y-m-d H:i");
	} else {
	    $endDate = date('Y-m-d H:i',strtotime($endDate));
	}
	
	$startDate = date('Y-m-d H:i',strtotime($startDate));

	$startedWorking = new DateTime($startDate);
	$currentTime = new DateTime($endDate);
	$interval = $startedWorking->diff($currentTime);
		
	return $interval->format('%H:%I');
}

function timeBetweenDatesWithSeconds($startDate, $endDate = "") {
	if ($endDate == "") {
		$endDate = date("Y-m-d H:i:s");
	}

	$startedWorking = new DateTime($startDate);
	$currentTime = new DateTime($endDate);
	$interval = $startedWorking->diff($currentTime);
		
	return $interval->format('%H:%I:%S');
}

function isValidTime($time) {
    
    // Checks if its numeric (single value) and if its greater than 0 or less than 24.
    if (is_numeric($time) && $time >= 0 && $time <= 24) {
        
        $time = abs(intval($time));
        
        if ($time == 24) {
            $time = "0:00";
        } else {
            $time = $time.":00";
        }
        
        if (strtotime($time)) {
            return $time;
        } else {
            return false;
        }
        
    }
    
    if (preg_match('/^(([0-1]?[0-9]|2[0-3]):[0-5][0-9]|24:00)$/', $time)) {
        if ($time == "24:00") {
            $time = "0:00";
        }
        
        if (strtotime($time)) {
            return $time;
        } else {
            return false;
        }
        
    } else {
        // EVERYTHING ELSE FAILED THIS ISN'T RIGHT!
        return false;
    }
    
}


function session() {
    // initiate/resume our session: Checking for user agent and IP address
    session_start();
    //session_regenerate_id(true);
    
    $_SESSION['timeout'] = time();
    
    if ($_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT'] || $_SESSION['remote_ip'] != $_SERVER['REMOTE_ADDR']) {
        //$ErrorMessage = "User agent or Remote IP discrepency! Forcing close session of user ".$_SESSION['sid'].". Session saved user_agent: ".$_SESSION['user_agent']." -- Reported User Agent: ".$_SERVER['HTTP_USER_AGENT'].". Session saved Remote IP address: ". $_SESSION['remote_ip'] ." -- Reported Remote IP: ".$_SERVER['REMOTE_ADDR'];
        //error_log($ErrorMessage);
        destroy_session();
        // We'll also send a message to display at the top notifying that session information changed.
        header("Location: login.php");
        exit;
    }
    
    
    if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > 172800)) {
        //$ErrorMessage2 = "Session timed out! Forcing close session of user ". $_SESSION['sid']. ". Session saved timeout: ". $_SESSION['timeout'];
        //error_log($ErrorMessage2);
        
        destroy_session();
        // We'll also send a message to display at the top notifying that the session has expired.
        header("Location: login.php");
        exit;
    }
    
    
}

function initiate_session() {
    // Creating a new session - Setting user agent and remote IP variables
    session_start();
    
    /* BUG REPORT #3 TESTING
     * Commenting out to test frequent and premature session expires. 
     */
    //session_regenerate_id(true);
    
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['remote_ip'] = $_SERVER['REMOTE_ADDR'];
    
    $_SESSION['timeout'] = time();
    
    ini_set("session.cookie_lifetime","172800"); // Setting a time out of 48 hours.
    
}

function destroy_session() {
    // Securely destroying our session.
    //remove PHPSESSID from browser
    //if ( isset( $_COOKIE[session_name()] ) )
    //setcookie( session_name(), “”, time()-3600, “/” );
    //clear session from globals
    $_SESSION = array();
    //clear session from disk
    session_destroy();
}
