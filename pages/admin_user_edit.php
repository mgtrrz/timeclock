<?php

if (!defined('TIMECLOCK')) die();

// We need to check if this user exists or if we need to return a 404.
$userID = $requestURI[2];

$editUser = new User($userID);

$recentPunches = $editUser->logs(10);
$recentHoursWorked = $editUser->lastPunch(10);

echo "<h2>".$editUser->getUserRealName(true)."</h2>";

if ($editUser->isWorking()) {
    //echo '<div class="alert alert-warning" role="alert">Clocked in and working. Completed <span id="spawn_realtime">'.timeBetweenDatesWithSeconds($editUser->lastPunch()).'</span> hours.</div>';
    ?>
    
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $editUser->firstName ?> is clocked in and working. Completed <?php echo timeBetweenDatesWithSeconds($editUser->lastPunch()) ?> hours.</h3>
        </div>
    </div>
    
    <?php
    
} 

echo "<form method='post'><input type='hidden' name='user' value='".$userID."'><button type='submit' class='btn btn-primary btn-lg' name='user_edit_punch'>Punch ". $editUser->firstName ."</button></form>";

if (isset($_SESSION['message_alert'])) {
	$msg = new Message();
	$msg->displayMessage(TRUE);
}

// ######################################################################################################
// ############################################ SCHEDULE ################################################
// ######################################################################################################

if (isset($_POST['edit'])) {
    $editMode = true;
} else {
    $editMode = false;
}

if ($editMode) {
    echo '<h3>Editing Schedule</h3>';
} else {
    echo '<h3>Schedule</h3>';
}

if (isset($_POST['confirmDeletion'])) {
    $editUser->setSchedule(false);
    $alert = new Message("Schedule successfully removed!", "alert-success");
}

$daysOfWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

$error = array();

if (isset($_POST['submit'])) {
    // We're creating an array of our new schedule here.
    $newSchedule = array();
    
    foreach ($daysOfWeek as $day) {
        
        $postDayStart = $_POST[strtolower($day)."_start"];
        $postDayEnd = $_POST[strtolower($day)."_end"];
        $postDayBreak = $_POST[strtolower($day)."_break"];
        
        // Sanitation checks!
        if ($postDayStart != "") {
            
            $checkedDayStart = isValidTime($postDayStart);
            
            // if isValidTime returned false
            if (!$checkedDayStart) {
                $malformed = true;
                $error[] = strtolower($day)."_start";
            } else {
                // looks okay.. let's add it to the new Schedule array
                $newSchedule[strtolower($day)]['start'] = $checkedDayStart;
            }
            
        } else {
            $newSchedule[strtolower($day)] = 0;
        }
        
        // DAY END
        if ($postDayEnd != "") {
            
            $checkedDayEnd = isValidTime($postDayEnd);
            
            if ($checkedDayEnd == $checkedDayStart) {
                $malformed = true;
                $error[] = strtolower($day)."_start";
                $error[] = strtolower($day)."_end";
            }
            
            // if isValidTime returned false
            if (!$checkedDayEnd) {
                $malformed = true;
                $error[] = strtolower($day)."_end";
            } else {
                // looks okay.. let's add it to the new Schedule array
                $newSchedule[strtolower($day)]['end'] = $checkedDayEnd;
            }
            
        } else {
            $newSchedule[strtolower($day)] = 0;
        }
        
        // DAY BREAK
        if ($postDayBreak != "") {
            
            $checkedDayBreak = isValidTime($postDayBreak);
            
            if (!$checkedDayBreak) {
                $malformed = true;
                $error[] = strtolower($day)."_break";
            } else {
                // looks okay.. let's add it to the new Schedule array
                if ($newSchedule[strtolower($day)] != 0 )
                    $newSchedule[strtolower($day)]['break'] = $checkedDayBreak;
            }
        }
        
        
        
    }
    
    
    // Attempting to push to the database..
    if (!$malformed) {
        // Nothing above failed.
        $jsonEncodedSchedule = json_encode($newSchedule);
        
        if (json_encode($newSchedule)) {
            $result = $editUser->setSchedule(json_encode($newSchedule));
            
            if ($result) {
                $alert = new Message("Schedule successfully updated!", "alert-success");
            }
        } else {
            $alert = new Message("There was an issue with encoding the data.", "alert-danger");
            $editMode = 1;
        }
        
    } else {
        // A sanitary check above failed and this issue will need to be presented to the user.
        // We will later add a red box around the bad input boxes to show the user what needs correcting.
        //echo "<pre>";
        //print_r($error);
        //echo "</pre>";
        $alert = new Message("Incorrectly formatted time. Please correct the errors above.", "alert-danger");
        $editMode = 1;
    }
    
}

$userSchedule = $editUser->getSchedule();


if ($userSchedule != false ||$editMode) {
    
    if ($editMode) {
        echo "<div class='well well-sm'><p><strong>Instructions:</strong> Enter hours in 24 hour format. Single numbers (e.g. 8, 14, 22) 
              can also be entered and will automatically be converted to a time format. 
              To schedule a day off, simply leave start and end times for that day blank. For midnight, enter either 24:00 or 0:00. 
              If 24 is entered, it will automatically be changed to 0:00.</p></div>";
    }
    
    ?>
    <table class="table table-striped table-hover ">
      <thead>
        <tr>
          <th>Day</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Break</th>
          <?php if (!$editMode) { ?><th>Total Hours</th><?php } ?>
        </tr>
      </thead>
      <tbody>
    <?php
    
    $totalHoursScheduled = 0;
    
    foreach ($daysOfWeek as $day) {
        
        
        $userStartTime = date('Y-m-d H:i:s', strtotime("today ".$userSchedule[strtolower($day)]['start']));
        
        if ($userSchedule[strtolower($day)]['end'] < $userSchedule[strtolower($day)]['start']) {
            $userEndTime = date('Y-m-d H:i:s', strtotime("tomorrow ".$userSchedule[strtolower($day)]['end']));
        } else {
            $userEndTime = date('Y-m-d H:i:s', strtotime("today ".$userSchedule[strtolower($day)]['end']));
        }
        
        $hoursScheduled = timeBetweenDates($userStartTime, $userEndTime);
        
        // Compensating for break. If the 'break' key exists in the array, subtract it from hours scheduled.
        if ($userSchedule[strtolower($day)] != 0 && array_key_exists('break', $userSchedule[strtolower($day)])) {
            $hoursScheduled = timeBetweenDates($hoursScheduled, $userSchedule[strtolower($day)]['break']);
        }
        
        if ($editMode) {
            
            if ($editMode) echo "<form method='post' role='form'>\n";
            
            // EDIT MODE 
            echo "<tr>\n";
        	echo "<td>".$day."</td>\n";
        	if ($userSchedule[strtolower($day)] == 0) {
        	    
        	    // For any entries in the error array, we'll be displaying a red line around the input box and add the POST data back in.
        	    // WE SHOULD PROBABLY FORMAT THIS!
        	    if (in_array(strtolower($day)."_start", $error)) {
        	        echo "<td><div class='form-group has-error'><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' value=".$_POST[strtolower($day)."_start"]."></input></div></td>\n";
        	    } else {
        	        echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' ></input></td>\n";
        	    }
        	    
        	    if (in_array(strtolower($day)."_end", $error)) {
        	        echo "<td><div class='form-group has-error'><input id='inputError' class='form-control input-sm' type='text' name='". strtolower($day) ."_end' value=".$_POST[strtolower($day)."_end"]."></input></div></td>\n";
        	    } else {
        	        echo "<td><input id='inputError' class='form-control input-sm' type='text' name='". strtolower($day) ."_end' ></input></td>\n";
        	    }
        	    
        	    if (in_array(strtolower($day)."_break", $error)) {
        	        echo "<td><div class='form-group has-error'><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' value=".$_POST[strtolower($day)."_break"]."></input></div></td>\n";
        	    } else {
        	        echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' ></input></td>\n";
        	    }
        	   
        	} else {
        	    
        	    if (in_array(strtolower($day)."_start", $error)) {
        	        echo "<td><div class='form-group has-error'><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' value='".$_POST[strtolower($day)."_start"]."'></input></div></td>\n";
        	    } else {
        	        echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' value='".$userSchedule[strtolower($day)]['start']."'></input></td>\n";
        	    }
        	    
        	    if (in_array(strtolower($day)."_end", $error)) {
                    echo "<td><div class='form-group has-error'><input class='form-control input-sm' type='text' name='". strtolower($day) ."_end' value='".$_POST[strtolower($day)."_end"]."'></input></div></td>\n";
                } else {
                    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_end' value='".$userSchedule[strtolower($day)]['end']."'></input></td>\n";
                }
                
                if (in_array(strtolower($day)."_break", $error)) {
                    echo "<td><div class='form-group has-error'><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' value='".$_POST[strtolower($day)."_break"]."'></input></div></td>\n";
                } else {
                    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' value='".$userSchedule[strtolower($day)]['break']."'></input></td>\n";
                }
                
        	}
            echo "</tr>\n";
            
        } else {
            
            // NOT EDITING MODE!
            echo "<tr>\n";
        	echo "<td>".$day."</td>\n";
        	if ($userSchedule[strtolower($day)] == 0) {
        	    echo "<td>Off</td>\n";
        	    echo "<td>Off</td>\n";
        	    echo "<td></td>\n";
        	    echo "<td></td>\n";
        	} else {
        	    echo "<td>".$userSchedule[strtolower($day)]['start']."</td>\n";
                echo "<td>".$userSchedule[strtolower($day)]['end']."</td>\n";
                echo "<td>".$userSchedule[strtolower($day)]['break']."</td>\n";
                echo "<td>$hoursScheduled</td>";
        	}
            echo "</tr>\n";
        }
        
        $totalHoursScheduled += $hoursScheduled;
        
    }
    
    echo "</tbody>\n";
    echo "</table>\n";
    
    echo "<strong>Total Hours:</strong> $totalHoursScheduled\n<br><br>";
    
    
} // END if ($userSchedule != false)

if ($alert) { $alert->displayMessage(); }

if (!$editMode) echo "<form method='post' role='form'>\n";
    
if ($userSchedule != false || $editMode) {

    if ($editMode) {
        echo '<button type="submit" name="submit" class="btn btn-success">Submit Changes</button> <a href="schedule" class="btn btn-danger">Cancel</a>';
    } else {
        echo '<button type="submit" name="edit" class="btn btn-primary">Edit Schedule</button> <button type="submit" class="btn btn-danger" name="confirmDeletion" onclick="return confirm(\'Are you sure you want to remove your schedule?\')">Remove Schedule</button>';
    }
    
} else {
    echo '<button type="submit" name="edit" class="btn btn-primary">Create Schedule</button>';
}

echo "</form>\n";

// ###################################################################################################### 
// ########################################### /SCHEDULE ################################################ 
// ######################################################################################################

?>
<hr>

<h3>Recent Hours Worked</h3>

<table class="table table-striped table-hover ">
  <thead>
    <tr>
      <th>Start Time</th>
      <th>End time</th>
      <th>Hours Worked</th>
    </tr>
  </thead>
  <tbody>
<?php 
    foreach ($recentHoursWorked as $entry) {

		//$t = strtotime($entry['timestamp']);
		$endTime = date('M d, Y H:i',strtotime($entry['timestamp']));
		$startTime = date('M d, Y H:i',strtotime($entry['previous_timestamp']));
    
		echo "<tr>";
        echo "<td>$startTime</td>";
        echo "<td>$endTime</td>";
        echo "<td>".$entry['hours_worked']."</td>";
        echo "</tr>";
    }
?>

  </tbody>
</table>

<hr>

<h3>Recent Punches</h3>

<table class="table table-striped table-hover" id="clickable">
  <thead>
    <tr>
      <th>Time Stamp</th>
      <th>Activity</th>
      <th>Event</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
<?php 
    foreach ($recentPunches as $entry) {

		$t = strtotime($entry['timestamp']);
		$time = date('M d, Y H:i',strtotime($entry['timestamp']));
		
		
		if ($entry['in_out'] == 1) {
			$activity = "Clocked in";
		} else {
			$activity = "Clocked out";
		}
    
		echo "<tr>";
        echo "<td><a href='/punch/".$entry['id']."'>$time</a></td>";
        echo "<td>".$activity."</td>";
        echo "<td>".$entry['event']."</td>";
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
?>

  </tbody>
</table>

<hr>

<h3>Modify User Details</h3>