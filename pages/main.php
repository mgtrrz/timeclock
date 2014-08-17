<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

// Get recent punches

$recentPunches = $user->logs(10);
$recentHoursWorked = $user->lastPunch(10);

?>
<div id="punchButton">
	<form role="form" method="post">
		<button type="submit" name="punch" class="btn btn-primary btn-lg extraWide">Punch</button> <button type="submit" name="punchWithNote" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#punchWithNote"><span class="glyphicon glyphicon-pencil"></span></button>
	
	</form>
</div>

<?php if (isset($_SESSION['message_alert'])) { $sessionMessage = new Message(); $sessionMessage->displayMessage(TRUE); } ?>

<hr>
<div id="history">
<h2>Recent Punches</h2>
<table class="table table-striped table-hover ">
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
    
        if (isset($_SESSION['newEntry']) && $_SESSION['newEntry'] == 1) {
			echo "<tr class='success'>";
			unset($_SESSION['newEntry']);
		} else {
			echo "<tr>";
		}
        echo "<td>$time</td>";
        echo "<td>".$activity."</td>";
        echo "<td>".$entry['event']."</td>";
        echo "<td>".$entry['note']."</td>";
        echo "</tr>";
    }
?>

  </tbody>
</table>

<h2>Recent Hours Worked</h2>
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
</div>