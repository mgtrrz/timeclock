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

function toHTML($string) {
    return htmlentities($string, ENT_QUOTES, "UTF-8");
}

function getPunchDetails($punchID, $userID = "", $variation = "") {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ( $userID == "" && $variation == "" ) {
        $data = $db->prepare('SELECT id, user_id, timestamp, in_out, event, ip_address, note, previous_punch, hours_worked, earnings FROM punches WHERE id=:id');
        $data->bindParam(':id', $punchID);
        $data->execute();
        $result = $data->fetch(PDO::FETCH_ASSOC);
    
    } else {
        
        if ($variation == "next") {
            $data = $db->prepare('SELECT * FROM punches WHERE user_id=:userid AND id > :id ORDER BY id ASC LIMIT 1');
        } elseif ($variation == "previous") {
            $data = $db->prepare('SELECT * FROM punches WHERE user_id=:userid AND id < :id ORDER BY id DESC LIMIT 1');
        }
        
        $data->bindParam(':userid', $userID);
        $data->bindParam(':id', $punchID);
        $data->execute();
        $result = $data->fetch(PDO::FETCH_ASSOC);
        if ( empty($result) ) {
            // if nothing came back, return an empty array
            $result = array();
        }
    }
    

    return $result;
}

function maxPunchID() {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $data = $db->prepare('SELECT MAX(id) as id FROM punches');
    $data->execute();
    $result = $data->fetch(PDO::FETCH_ASSOC);
    
    return $result['id'];
}

function savePunchDetails($punchID, $newChanges) {
    
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = array( 'status' => true );

    if (array_key_exists('note', $newChanges)) {
        $data = $db->prepare('UPDATE punches SET note=:note WHERE id=:id');
        $data->bindParam(':note', $newChanges['note']);
        $data->bindParam(':id', $punchID);
        if ( !$data->execute() ) {
            $result['status'] = false;
            $result['issue'] = 'note';
        }
    } 
    
    if (array_key_exists('earnings', $newChanges)) {
        $data = $db->prepare('UPDATE punches SET earnings=:earnings WHERE id=:id');
        $data->bindParam(':earnings', $newChanges['earnings']);
        $data->bindParam(':id', $punchID);
        if ( !$data->execute() ) {
            $result['status'] = false;
            $result['issue'] = 'note';
        }
    } 
    
    if (array_key_exists('timestamp', $newChanges)) {
        $data = $db->prepare('UPDATE punches SET timestamp=:timestamp WHERE id=:id');
        $data->bindParam(':timestamp', $newChanges['timestamp']);
        $data->bindParam(':id', $punchID);
        if ( !$data->execute() ) {
            $result['status'] = false;
            $result['issue'] = 'note';
        }
        
        $data = $db->prepare('SELECT * FROM punches WHERE id=:id');
        $data->bindParam(':id', $punchID);
        $data->execute();
        $currentPunch = $data->fetch(PDO::FETCH_ASSOC);

        
        if ($currentPunch['in_out'] == 1) {
            // then if the PUNCH OUT that comes after it exists, that needs to be updated as well
            $data = $db->prepare('SELECT * FROM punches WHERE user_id=:userid AND id > :id ORDER BY id ASC LIMIT 1');
            $data->bindParam(':userid', $currentPunch['user_id']);
            $data->bindParam(':id', $punchID);
            $data->execute();
            $nextPunch = $data->fetch(PDO::FETCH_ASSOC);
            
            print_r($nextPunch);
            if ( !empty($nextPunch) ) {
                // Next punch DOES exist! So let's update the previous_punch value on the NEXT timestamp..
                $data = $db->prepare('UPDATE punches SET previous_punch=:timestamp WHERE id=:id');
                $data->bindParam(':timestamp', $newChanges['timestamp']);
                $data->bindParam(':id', $nextPunch['id']);
                $data->execute();
            }
        }
        
        
    }
    
    if ($result['status'] == true) {
        $data = $db->prepare('UPDATE punches SET event="Punch*" WHERE id=:id');
        $data->bindParam(':id', $punchID);
        $data->execute();
    }
    
    return $result;
    
}

function adjustHoursWorked($user) {
    
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = $db->prepare('SELECT * from punches WHERE user_id=:userid AND in_out=0');
    $data->bindParam(':userid', $user);
    $data->execute();
    $result = $data->fetchALL(PDO::FETCH_ASSOC);
    
    foreach ($result as $row) {
        $newHoursWorked = timeBetweenDates($row['previous_punch'], $row['timestamp']);
        
        $data = $db->prepare('UPDATE punches SET hours_worked=:hoursworked WHERE id=:id');
        $data->bindParam(':hoursworked', $newHoursWorked);
        $data->bindParam(':id', $row['id']);
        $data->execute();
    }
}



function validDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
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


function addTimes($times) {
    
    if (is_array($times)) {
        
        $length = sizeof($times);
        
        $totalTime = "00:00";
        
		for($x=0; $x < $length; $x++){

		    $secs = strtotime($times[$x])-strtotime("00:00:00");
            $totalTime = date("H:i",strtotime($totalTime)+$secs);
		    
		}
		
		return $totalTime;
    }
    
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

function sumOfHours($times) {
    
    $hou = 0;
    $min = 0;

    for ($x=0; $x < sizeof($times) ; $x++) {
            $split = explode(":", $times[$x]); 
            $hou += $split[0];
            $min += $split[1];
            //$sec = $split[2];
    }
    $minutes = $min;
    $hours = $minutes/60;
    $minutes = $minutes%60;
    $hours += $hou; 
    return leadingZeros((integer)$hours, 2).":".leadingZeros($minutes, 2);
    
}

function leadingZeros($num,$numDigits) {
   return sprintf("%0".$numDigits."d",$num);
}

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($file, $filename) {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="' . $filename . '";');
    echo $file;
}


//
// Courtesy of: http://www.hashbangcode.com/blog/converting-and-decimal-time-php
//

function time_to_decimal($time) {
    $timeArr = explode(':', $time);
    $decTime = ($timeArr[0]*60) + ($timeArr[1]);
 
    return $decTime/60;
}
/*
function decimal_to_time($decimal) {
    $hours = floor($decimal / 60);
    $minutes = floor($decimal % / 60);
    $seconds = $decimal - (int)$decimal;
    $seconds = round($seconds * 60);
 
    return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
}
*/
//
// End courtesy. Back to my shitty coding.
//

function percentage($part, $whole) {
    return (int)(( $part * 100 ) / $whole);
}

function logToDatabase() {
    
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
