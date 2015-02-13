<?php

if (!defined('TIMECLOCK')) die();
if ($user->isAdmin()) {
    include_once('./includes/admin.class.php');
    $admin = new Admin();
    $allUsers = $admin->getAllUsers();
}

// Get the info from the database when start Timestamp and end Timestamp are set
if ( (isset($_GET['startTimestamp']) && isset($_GET['endTimestamp'])) or $_GET['payperiod'] != "" ) {
    
    $startTimeStamp = strip_tags(trim($_GET['startTimestamp']));
    $endTimeStamp = strip_tags(trim($_GET['endTimestamp']));
    
    if ($startTimeStamp == "" || $endTimeStamp == "") {
        
        $payperiod = explode('-',trim($_GET['payperiod'])); 
        
        $startTimeStamp = date('Y-m-d 0:00:00', strtotime($payperiod[0]));
        $endTimeStamp = date('Y-m-d 23:59:59', strtotime($payperiod[1]));
    }
    
    // If "user" is set, then get info for that specific user.
    // The admin can only get this info, so we're also checking if the current user is an admin.
    if (isset($_GET['user']) && $user->isAdmin()) { $dataUser = strip_tags(trim($_GET['user'])); } else { $dataUser = $_SESSION['sid']; }
    
    //$results = true;
    
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        if ($dataUser == "all") {
            $data = $db->prepare('SELECT id, user_id User, previous_punch Start, timestamp End, hours_worked Hours, earnings from punches WHERE hours_worked IS NOT NULL AND timestamp > :startTime AND timestamp < :endTime');

        } else {
            $data = $db->prepare('SELECT id, user_id User, previous_punch Start, timestamp End, hours_worked Hours, earnings from punches WHERE user_id=:userid AND hours_worked IS NOT NULL AND timestamp > :startTime AND timestamp < :endTime');
            $data->bindParam(':userid', $dataUser);
        }

        $data->bindParam(':startTime', $startTimeStamp);
        $data->bindParam(':endTime', $endTimeStamp);
        $data->execute();
        $result = $data->fetchAll(PDO::FETCH_ASSOC);
        
        if ( isset($_POST['export'])) {
            if ($dataUser == "all") {
                $data = $db->prepare('SELECT id, user_id User, previous_punch Start, timestamp End, hours_worked Hours, earnings Earnings from punches WHERE hours_worked IS NOT NULL AND timestamp > :startTime AND timestamp < :endTime');
    
            } else {
                $data = $db->prepare('SELECT previous_punch Start, timestamp End, hours_worked Hours, earnings Earnings from punches WHERE user_id=:userid AND hours_worked IS NOT NULL AND timestamp > :startTime AND timestamp < :endTime');
                $data->bindParam(':userid', $dataUser);
            }
            
            $data->bindParam(':startTime', $startTimeStamp);
            $data->bindParam(':endTime', $endTimeStamp);
            $data->execute();
            $toCSV = $data->fetchAll(PDO::FETCH_ASSOC);
        }
    
        
    } catch(PDOException $db) {
        echo 'ERROR: ' . $db->getMessage();
    }
 
    if ( isset($_POST['export'])) {
        $startCSV = date("Y-m-d",strtotime($startTimeStamp));
        $endCSV = date("Y-m-d",strtotime($endTimeStamp));
        download_send_headers(array2csv($toCSV), 'timeclock_stats_'.$startCSV.'_'.$endCSV.'.csv');
        exit;
    }
}

// Getting pay periods since user's created dates
$usersStartDate = $user->createdDate();
while(new DateTime($usersStartDate) < new DateTime() ) {
    
    $start1 = date('F 1, Y', strtotime($usersStartDate));
    $end1 = date('F 15, Y', strtotime($start1));
    $start2 = date('F 16, Y' , strtotime($start1));
    $end2 = date('F t, Y', strtotime($start1));
    $payperiods[] = "$start1 - $end1";
    if (new DateTime($start2) < new DateTime()) {
        $payperiods[] = "$start2 - $end2";
    }
    $usersStartDate = date("Y-m-d", strtotime("+1 month", strtotime($start1)));
    
    
}

$payperiods = array_reverse($payperiods);
?>


<h2>Statistics</h2>

<form class="form-horizontal" method="get" action="/statistics/data">
  <fieldset>
    <div class="form-group">
      <label for="inputEmail" class="col-lg-2 control-label">Start Date</label>
      <div class="col-lg-10">
        <input type="text" id="datetimepickerStart" name="startTimestamp" class="form-control" value="<?php if ($_POST['payperiod'] != "") echo $startTimeStamp;?>">
      </div>
    </div>
    <div class="form-group">
      <label for="inputPassword" class="col-lg-2 control-label">End Date</label>
      <div class="col-lg-10">
        <input type="text" id="datetimepickerEnd" name="endTimestamp" class="form-control" value="<?php if ($_POST['payperiod'] != "") echo $endTimeStamp; ?>">
      </div>
    </div>
    <?php if ($user->isAdmin()) { ?>
    <div class="form-group">
    <label for="select" class="col-lg-2 control-label">User</label>
      <div class="col-lg-10">
        <select class="form-control" id="select" name="user">
          <option value="all" >All Users</option>
          <?php
          
          foreach ($allUsers as $oneUser) {
              if ($oneUser['staff_id'] == $_GET['user']) {
                  $selected = 'selected';
              } else {
                  $selected = '';
              }
              echo '<option value="'.$oneUser['staff_id'].'" '.$selected.' >'.$oneUser['first_name'].' '.$oneUser['last_name'].'</option>';
          }
          
          ?>
        </select>
      </div>
    </div>
    <?php } ?>
    <div class="form-group">
    <label for="select" class="col-lg-2 control-label">Payperiod</label>
      <div class="col-lg-10">
        <select class="form-control" id="select" name="payperiod">
          <option value="">Select Payperiod</option>
          <?php
          
          foreach ($payperiods as $payperiod) {
              if ($_GET['payperiod'] == $payperiod) {
                  $selected = "selected";
              } else {
                  $selected = "";
              }
              echo "<option $selected>$payperiod</option>";
          }
          
          ?>
        </select>
      </div>
    </div>
    
    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </fieldset>
</form>
<?php

if ($result) {
?>

<hr>

<div id='stats_calendar'></div>

<hr>

<h3>Results</h3>

<table class="table table-striped table-hover" id="clickable">
  <thead>
    <tr>
    <?php 
    if ($admin && $dataUser == "all") {
        echo "<th>Name</th>";
    }
    ?>
      <th>Start Time</th>
      <th>End time</th>
      <th>Hours Worked</th>
      <th>Earnings</th>
    </tr>
  </thead>
  <tbody>
<?php 
    $daysWorked = 0;
    foreach ($result as $entry) {
        
        $tempUser = new User($entry['User']);
        
        if ($user->timeFormat() == "12") {
		    $timeFormat = 'M d, Y h:i A';
		} else {
		    $timeFormat = 'M d, Y H:i';
		}
        
		//$t = strtotime($entry['timestamp']);
		$startTime = date($timeFormat,strtotime($entry['Start']));
		$endTime = date($timeFormat,strtotime($entry['End']));
    
		echo "<tr>";
		if ($admin && $dataUser == "all") {
		    echo "<td>". $tempUser->getUserRealName(TRUE) . "</td>";
		}
        echo "<td><a href='/punch/" . $entry['id'] . "'>$startTime</a></td>";
        echo "<td>$endTime</td>";
        echo "<td>".$entry['Hours']."</td>";
        if ( $entry['earnings'] != "" ) {
            echo "<td>$".$entry['earnings']."</td>";
        } else {
            echo "<td>\$0.00</td>";
        }
        echo "</tr>";
        
        $totalHours[] = $entry['Hours'];
        $sumEarnings += $entry['earnings'];
        
        
        // Creating the array/json data for the calendar
        $dayStat = array(
                        "title" => date('- g:ia',strtotime($entry['End'])),
                        "start" => date('Y-m-d H:i:s',strtotime($entry['Start'])),
                        "end"   => date('Y-m-d H:i:s',strtotime($entry['End'])),
                        "url"   => "/punch/".$entry['id'],
                    );
        $statsCalendar[] = $dayStat;
        
        if (!$defaultDate) {
            $defaultDate =  date('Y-m-d',strtotime($entry['Start']));
        }
    
        // end array/json data for the calendar.
        
        
        // if the previousDate DOES NOT EQUAL the current date for this entry, add days worked.
        // if it does, then this is another shift on the same day, so no need to count it.
        if ($prevDate != date('M d, Y',strtotime($entry['Start'])) || $daysWorked == 0 ) {
            $daysWorked++;
        } 
        
        // Going to set prevDate to the current date of this entry.
        $prevDate = date('M d, Y',strtotime($entry['Start']));
        
        // it makes sense when you think about it but not when I write it like that ^
    }
    
    $sum = sumOfHours($totalHours);
    
    
    $decimalTime = time_to_decimal($sum);

    $perHour =  number_format(( $sumEarnings / $decimalTime ), 2, '.', ',');
    
    $date1 = new DateTime($startTimeStamp);
    $date2 = new DateTime($endTimeStamp);

    $totalDays = $date2->diff($date1)->format("%a");
    
    
    //  This is eh... Normally, "Chosen Day, Year 23:59:59" does not count as a day so we get around that by adding 1 to totalDays.
    //  This really isn't preferred because it'll count the day chosen even if 0:00 is used for the hour.
    //  Then it's really not a day, right? 
    $totalDays++;
    
    $hoursPerDay = number_format(( $decimalTime / $daysWorked ), 2, '.', ',');
    $hoursPerSession = $decimalTime / $totalDays;
    
    $tph = round( ( $sumEarnings * 20 ) / $decimalTime );
?>

  </tbody>
</table>

<strong>Total hours worked: </strong><?php echo $sum; ?><br /><br />
<!--<strong>Hours in Decimal format (DEBUG): </strong><?php // echo $decimalTime; ?><br /><br />-->
<strong>Total days: </strong><?php echo $totalDays ?><br />
<strong>Days worked: </strong><?php echo $daysWorked ?><br />
<strong>Percentage of days worked in period: </strong><?php echo percentage($daysWorked, $totalDays) ?>%<br /><br />

<strong>Average hours per session: </strong><?php echo $hoursPerDay ?><br /><br />
<strong>Total earnings: </strong>$<?php echo number_format($sumEarnings, 2, '.', ',') ?><br />
<strong>Per Hour: </strong>$<?php echo $perHour ?><br />
<strong>TPH: </strong><?php echo $tph ?><br />
<br />
<form method="post" >
<button type="submit" name="export" class="btn btn-primary">Export to CSV</button>
</form>
<hr>

<script>

$(document).ready(function() {

	$('#stats_calendar').fullCalendar({
	    header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek'
			},
		eventLimit: true,
		editable: false,
		defaultDate: '<?php echo $defaultDate; ?>',
		events: <?php echo json_encode($statsCalendar); ?>
	});
		
});
</script>

<?php
} else { // if ($result)
    if ( isset($_GET['startTimestamp']) && isset($_GET['endTimestamp']) ) {
        echo '<hr>';
        echo '<div class="panel panel-warning">';
        echo '  <div class="panel-heading">';
        echo '    <h3 class="panel-title">Statistics</h3>';
        echo '  </div>';
        echo '  <div class="panel-body">';
        echo '  There are no punches for the chosen time period.';
        echo '  </div>';
        echo '</div>';
    }
} 
?>

<link rel="stylesheet" type="text/css" href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/lib/datetimepicker/jquery.datetimepicker.css"/ >
<script src="http://<?php echo $_SERVER['SERVER_NAME']; ?>/lib/datetimepicker/jquery.datetimepicker.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    
    $('#datetimepickerStart').datetimepicker({
        format:'Y-m-d H:i:s',
        closeOnDateSelect:true,
        defaultTime:'00:00'
    });
    
    $('#datetimepickerEnd').datetimepicker({
        format:'Y-m-d H:i:s',
        closeOnDateSelect:true,
        defaultTime:'23:59'
    });
    
});  
</script>