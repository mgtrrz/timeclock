<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();


echo "<h2>Users Currently Working</h2>\n";

if (!empty($usersWorking)) {
    ?>
    <table class="table table-striped table-hover" id="clickable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Clocked In</th>
          <th>Hours Worked</th>
          <th>Punch</th>
        </tr>
      </thead>
      <tbody>
    <?php 

    foreach ($usersWorking as $entry) {
    
		echo "<tr>";

        echo "<td><a href='http://".$_SERVER['SERVER_NAME']."/admin/users/".$entry['staff_id']."' >".$entry['first_name']." ".$entry['last_name']."</a></td>";
        //echo "<td>".$entry['first_name']." ".$entry['last_name']."</td>";
        echo "<td>".$entry['timestamp']."</td>";
        echo "<td>".timeBetweenDatesWithSeconds($entry['timestamp'])."</td>";
        echo '<td><form method="post"><input type="hidden" name="user" value="'.$entry['staff_id'].'"><button type="submit" name="admin_punch" class="btn btn-primary btn-xs">Punch</button></form></td>';
        echo "</tr>";
    }
	echo '</tbody>';
	echo '</table>';

} else {
	echo '<div class="well">';
    echo 'No users currently working';
	echo '</div>';
}

	if (isset($_SESSION['message_alert'])) {
		$msg = new Message();
		$msg->displayMessage(TRUE);
	}
?>
<hr>
<h2>Users Scheduled</h2>
<h3>Today</h3>
<?php

// Obtaining schedules for users
$adminSched = new Admin();
$userSchedules = $adminSched->getUserSchedule();

// Today as a day (sunday, monday, tuesday, etc)
$today = strtolower(date("l"));

// Grabbing tomorrow in the same way
$tdate = new DateTime('tomorrow');
$tomorrow = strtolower($tdate->format('l'));

$workingToday = array();

$count = 0;
foreach ($userSchedules as $user) {
    // converting the json schedule format in the database to an array
    $user['schedule'] = json_decode($userSchedules[$count]['schedule'], true);
    
    // we're then going to check if anyone is working today and add it to an array
    if ($user['schedule'][$today] != 0) {
        $workingToday[] = $user;
    }
    
    $count++;
}
?>

<table class="table table-striped table-hover "  id="clickable">
<thead>
<tr>
  <th>Name</th>
  <th>Starts</th>
  <th>Ends</th>
  <th>Working</th>
  <th>On Shift</th>
  <th>Completed</th>
</tr>
</thead>
<tbody>
      
<?php 

$currentTime = date('Y-m-d H:i:s');

if (!empty($workingToday)) {

    foreach ($workingToday as $user) {
        
        // Grabbing shift times for the user which will display green if they're IN shift and currently working or RED
        // if they are IN shift but clocked out.
        //
        // Some end times may end up into the next day which would
        // cause issues. For this to work properly, we will need to attach dates to start and end times
        // if the end time 'appears' less than the start time.
        //echo '<pre>';
        //print_r($user);
        //echo '</pre>';
        
        $userStartTime = "";
        $userEndTime = "";
        
        
        $userStartTime = date('Y-m-d H:i:s', strtotime("today ".$user['schedule'][$today]['start']));
        
        // if SCHEDULE_END is LESS than SCHEDULE_START (e.g. 5pm shift start and 2am shift end)
        // make SCHEDULE_END tomorrow.
        if (strtotime($user['schedule'][$today]['end']) < strtotime($user['schedule'][$today]['start'])) {
            $userEndTime = date('Y-m-d H:i:s', strtotime("tomorrow ".$user['schedule'][$today]['end']));
        } else {
            $userEndTime = date('Y-m-d H:i:s', strtotime("today ".$user['schedule'][$today]['end']));
        }
        
        // Compensating for break. If the 'break' key exists in the array, subtract it from hours scheduled.
        $hoursScheduled = timeBetweenDates($userStartTime, $userEndTime);
        
        //echo "<br>Hours Scheduled: $hoursScheduled<br>";
        
        if (array_key_exists('break', $user['schedule'][$today])) {
            $hoursScheduled = timeBetweenDates($hoursScheduled, $user['schedule'][$today]['break']);
        }
        
        if ($user['is_working']) {
            $userIsWorking = "Yes";
        } else {
            $userIsWorking = "No";
        }
        
        $tempUser = new User($user['staff_id']);
        $hoursWorkedToday = $tempUser->hoursWorked();
        /*
        echo "Current Time: $currentTime <br>";
        echo "Current Time strtotime:".strtotime($currentTime)."<br>";
        echo "User Shift Start: $userStartTime <br>";
        echo "User Shift Start strtotime: ". strtotime($userStartTime)."<br>";
        echo "User Shift End: $userEndTime <br>";
        echo "User Shift End strtotime: ". strtotime($userEndTime)."<br><br>";
        */
        if (strtotime($currentTime) >= strtotime($userStartTime) && strtotime($currentTime) <= strtotime($userEndTime)) {
            // If the current time is anywhere between SHIFT START and SHIFT END...
    
            if ($userIsWorking == "Yes") {
                echo '<tr class="success">';
            } else {
                
                if ($hoursWorkedToday == "00:00") {
                    echo '<tr class="danger">';
                } else if ($hoursWorkedToday >= $hoursScheduled) {
                    echo '<tr class="success">';
                } else {
                    echo '<tr class="warning">';
                }
    
            }
            $onShift = "Yes";
        } elseif ( strtotime($currentTime) > strtotime($userEndTime) ) {
            // If the current time is passed the user's end shift time
            
            if ($hoursWorkedToday == "00:00") {
                echo '<tr class="danger">';
            } else if ($hoursWorkedToday >= $hoursScheduled) {
                echo '<tr class="success">';
            } else {
                 echo '<tr class="warning">';
            }
                
            $onShift = "No";
        }
    
        
        echo "<td><a href='http://".$_SERVER['SERVER_NAME']."/admin/users/".$user['staff_id']."' >".$user['first_name']." ".$user['last_name']."</a></td>";
        echo "<td>".$user['schedule'][$today]['start']."</td>";
        echo "<td>".$user['schedule'][$today]['end']."</td>";
        echo "<td>$userIsWorking</td>";
        echo "<td>$onShift</td>";
        echo "<td>$hoursWorkedToday/$hoursScheduled</td>";
        echo "</tr>";
        
        //unset($userDB);
    }
    echo '</tbody>';
    echo '</table>';
    
} else {
    echo '<div class="well">';
    echo 'No users scheduled for today.';
	echo '</div>';
}
	
$workingTomorrow = array();

$count = 0;
foreach ($userSchedules as $user) {
    $user['schedule'] = json_decode($userSchedules[$count]['schedule'], true);
    //echo '<pre>';
    //print_r($user);
    //echo '</pre>';
    
    // we're then going to check if anyone is working tomorrow and add it to an array
    if ($user['schedule'][$tomorrow] != 0) {
        $workingTomorrow[] = $user;
    }
    
    $count++;
}

echo "<h3>Tomorrow</h3>";

if (!empty($workingTomorrow)) {
    ?>
    

    <table class="table table-striped table-hover" id="clickable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Starts</th>
          <th>Ends</th>
        </tr>
      </thead>
      <tbody>
    
    <?php
    foreach ($workingTomorrow as $user) {
    	    echo "<tr>";
    
            echo "<td><a href='http://".$_SERVER['SERVER_NAME']."/admin/users/".$user['staff_id']."' >".$user['first_name']." ".$user['last_name']."</a></td>";
            echo "<td>".$user['schedule'][$tomorrow]['start']."</td>";
            echo "<td>".$user['schedule'][$tomorrow]['end']."</td>";
            echo "</tr>";
            
            //unset($userDB);
        }
    	echo '</tbody>';
    	echo '</table>';
    
} else {
    echo '<div class="well">';
    echo 'No users scheduled for tomorrow.';
	echo '</div>';
}	
    ?>
    
    <hr>
    
    <h3>Recent Punches</h3>
    
    <table class="table table-striped table-hover " id="clickable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Time Stamp</th>
          <th>Activity</th>
          <th>IP Address</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
    <?php 
    
    $userPunches = $admin->getUserPunches();
    
        foreach ($userPunches as $entry) {
            
            $tempUser = new User($entry['user_id']);
    
    		$t = strtotime($entry['timestamp']);
    		$time = date('M d, Y H:i',strtotime($entry['timestamp']));
    		
    		
    		if ($entry['in_out'] == 1) {
    			$activity = "In";
    		} else {
    			$activity = "Out";
    		}
        
    		echo "<tr>";
    		echo "<td><a href='/punch/".$entry['id']."'>".$tempUser->getUserRealName(TRUE)."</a></td>";
            echo "<td>$time</td>";
            echo "<td>$activity</td>";
            echo "<td>".$entry['ip_address']."</td>";
            echo "<td>";
            if (strlen($entry['note']) > 25) {
                //echo substr($entry['note'],0,22)."...";
                echo "<a href='#' rel='tooltip' title='".$entry['note']."' data-placement='right'>".substr($entry['note'],0,22)."...</a>";
            } else {
                echo $entry['note'];
            }
            echo "</td>";
            echo "</tr>";
        }
    
    echo "  </tbody>";
    echo "</table>";

