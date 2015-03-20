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

if (isset($_POST['confirmDeletion'])) {
    $user->setSchedule(false);
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
            $result = $user->setSchedule(json_encode($newSchedule));
            
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

$userSchedule = $user->getSchedule();

echo '<hr>';
if ($editMode) {
    echo '<h2>Edit Schedule</h2>';
} else {
    echo '<h2>Schedule</h2>';
}


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
        
        // If the end time is before the start time, we're assuming this shift goes into the next day
        // assuming is a dangerous word
        $dtStart = new DateTime($userSchedule[strtolower($day)]['start']);
        $dtEnd = new DateTime($userSchedule[strtolower($day)]['end']);
        
        if ( $dtStart < $dtEnd ) {
            $userEndTime = date('Y-m-d H:i:s', strtotime("tomorrow ".$userSchedule[strtolower($day)]['end']));
        } else {
            $userEndTime = date('Y-m-d H:i:s', strtotime("today ".$userSchedule[strtolower($day)]['end']));
        }
        
        $hoursScheduled = timeBetweenDates($userStartTime, $userEndTime);
        
        // Compensating for break. If the 'break' key exists in the array, subtract it from hours scheduled.
        if ( $userSchedule[strtolower($day)] != 0  &&  array_key_exists('break', $userSchedule[strtolower($day)]) ) {
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

if ($user->isFreelance() || $user->isAdmin()) {
    
    if (!$editMode) 
        echo "<form method='post' role='form'>\n";
    
    if ($userSchedule != false || $editMode)
        if ($editMode) {
            echo '<button type="submit" name="submit" class="btn btn-success">Submit Changes</button> <button type="submit" class="btn btn-danger" name="confirmDeletion" onclick="return confirm(\'Are you sure you want to remove your schedule?\')">Remove Schedule</button> <a href="schedule" class="btn btn-default">Cancel</a>';
        } else {
            echo '<button type="submit" name="edit" class="btn btn-primary">Edit Schedule</button>';
        }
        
    else
        echo '<button type="submit" name="edit" class="btn btn-primary">Create Schedule</button>';
    
    echo "</form>\n";
    
} else {
    if ($userSchedule == false) 
        echo '<div class="panel panel-warning">';
        echo '  <div class="panel-heading">';
        echo '    <h3 class="panel-title">No Schedule</h3>';
        echo '  </div>';
        echo '  <div class="panel-body">';
        echo '  You are not currently on a schedule. Please ask your administrator to have a schedule set up for you.';
        echo '  </div>';
        echo '</div>';
}

?>