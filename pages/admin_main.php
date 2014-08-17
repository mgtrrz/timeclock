<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();


echo "<h2>Users Currently Working</h2>\n";

if (!empty($usersWorking)) {
    ?>
    <table class="table table-striped table-hover ">
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

        echo "<td>".$entry['first_name']." ".$entry['last_name']."</td>";
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
        if ($user['schedule'][$today]['end'] < $user['schedule'][$today]['start']) {
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
        
        //echo $user['first_name'].' start time: '.$userStartTime.'<br>End Time: '.$userEndTime.'<br> Hours Scheduled: '.$hoursScheduled.'<br>';
        
        if ($currentTime >= $userStartTime && $currentTime <= $userEndTime) {
            
            if ($userIsWorking == "Yes") {
                echo '<tr class="success">';
            } else {
                echo '<tr class="danger">';
            }
            $onShift = "Yes";
        } else {
            /*
            if ($userIsWorking != "Yes" && $currentTime > $userEndtime) {
                echo '<tr class="completed">';
            } else {
                echo '<tr>';
            }
            */
            echo '<tr>';
            $onShift = "No";
        }
        
        $hoursWorkedToday;
        
        echo '</pre>';
        
        echo "<td><a href='http://".$_SERVER['SERVER_NAME']."/admin/users/".$user['staff_id']."' >".$user['first_name']." ".$user['last_name']."</a></td>";
        echo "<td>".$user['schedule'][$today]['start']."</td>";
        echo "<td>".$user['schedule'][$today]['end']."</td>";
        echo "<td>$userIsWorking</td>";
        echo "<td>$onShift</td>";
        echo "<td>0:00/$hoursScheduled</td>";
        echo "</tr>";
        
        //unset($userDB);
    }
	echo '</tbody>';
	echo '</table>';
	
?>

<h3>Tomorrow</h3>

    <table class="table table-striped table-hover ">
      <thead>
        <tr>
          <th>Name</th>
          <th>Starts</th>
          <th>Ends</th>
        </tr>
      </thead>
      <tbody>
      
<?php 
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

foreach ($workingTomorrow as $user) {
	    echo "<tr>";

        echo "<td>".$user['first_name']." ".$user['last_name']."</td>";
        echo "<td>".$user['schedule'][$tomorrow]['start']."</td>";
        echo "<td>".$user['schedule'][$tomorrow]['end']."</td>";
        echo "</tr>";
        
        //unset($userDB);
    }
	echo '</tbody>';
	echo '</table>';
	
?>

<hr>

<h3>Recent Punches</h3>

<table class="table table-striped table-hover ">
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
		echo "<td>".$tempUser->getUserRealName(TRUE)."</td>";
        echo "<td>$time</td>";
        echo "<td>$activity</td>";
        echo "<td>".$entry['ip_address']."</td>";
        echo "<td>".$entry['note']."</td>";
        echo "</tr>";
    }
?>

  </tbody>
</table>
