<?php
//echo '<pre>';
//print_r($userSchedule);
//echo '</pre>';

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

if (isset($_POST['edit'])) {
    $editMode = true;
} else {
    $editMode = false;
}

$daysOfWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

if (isset($_POST['submit'])) {
    // We're creating an array of our new schedule here.
    $newSchedule = array();
    
    foreach ($daysOfWeek as $day) {
        
        // Sanitation checks must be performed here!
        $postDayStart = strtolower($day)."_start";
        $postDayEnd = strtolower($day)."_end";
        $postDayBreak = strtolower($day)."_break";
        
        if ($_POST[$postDayStart] != "") {
            
            $newSchedule[strtolower($day)] = array(
                    'start' => $_POST[$postDayStart],
                    'end' => $_POST[$postDayEnd]
            );
            
            if ($_POST[$postDayBreak] != "") {
                $newSchedule[strtolower($day)]['break'] = $_POST[$postDayBreak];
            } 
            
            
        } else {
            $newSchedule[strtolower($day)] = 0;
        }

    }
    
    if (!$malformed) {
        
        $jsonEncodedSchedule = json_encode($newSchedule);
        
        if (json_encode($newSchedule)) {
            $result = $user->setSchedule(json_encode($newSchedule));
            
            if ($result) {
                $alert = new Message("Schedule successfully updated!", "alert-success");
            }
        } else {
            $alert = new Message("There was an issue with encoding the data.", "alert-danger");
            $editMode = 1;
        }
        
        
    }
    
}

$userSchedule = $user->getSchedule();

echo '<hr>';
if ($editMode) {
    echo '<h2>Edit Schedule</h2>';
} else {
    echo '<h2>Schedule</h2>';
}


if ($userSchedule != false ||$editMode) {
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

    $daysOfWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
    
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
            
            if ($editMode) 
                echo "<form method='post' role='form'>\n";
            
            // EDIT MODE 
            echo "<tr>\n";
        	echo "<td>".$day."</td>\n";
        	if ($userSchedule[strtolower($day)] == 0) {
        	    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' ></input></td>\n";
        	    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_end' ></input></td>\n";
        	    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' ></input></td>\n";
        	} else {
        	    echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_start' value='".$userSchedule[strtolower($day)]['start']."'></input></td>\n";
                echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_end' value='".$userSchedule[strtolower($day)]['end']."'></input></td>\n";
                echo "<td><input class='form-control input-sm' type='text' name='". strtolower($day) ."_break' value='".$userSchedule[strtolower($day)]['break']."'></input></td>\n";
        	}
            echo "</tr>\n";
            
        } else {
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
        
    }
    
    echo "</tbody>\n";
    echo "</table>\n";
    
    if ($alert) { $alert->displayMessage(); }
} // END if ($userSchedule != false)

if ($user->isFreelance() || $user->isAdmin()) {
    
    if (!$editMode) 
        echo "<form method='post' role='form'>\n";
    
    if ($userSchedule != false)
        if ($editMode) {
            echo '<button type="submit" name="submit" class="btn btn-success">Submit Changes</button> <a href="schedule" class="btn btn-danger">Cancel</a>';
        } else {
            echo '<button type="submit" name="edit" class="btn btn-primary">Edit Schedule</button> <button type="submit" name="remove" class="btn btn-danger">Remove Schedule</button>';
        }
        
    else
        echo '<button type="submit" name="edit" class="btn btn-primary">Create Schedule</a>';
    
    echo "</form>\n";
    
} else {
    if ($userSchedule == false) 
        echo '<p>You are not currently on a schedule. Please ask an administrator for adjustments to your schedule.</p>';
}

?>
