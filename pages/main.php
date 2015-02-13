<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

// Get recent punches

$recentPunches = $user->logs(10);
$recentHoursWorked = $user->lastPunch(10);

$schedule = $user->getSchedule();
$hoursWorkedToday = $user->hoursWorked();

?>
<div id="punchButton">
	<form role="form" method="post">
		<button type="submit" name="punch" class="btn btn-primary btn-lg extraWide">Punch</button> <button type="submit" name="punchWithNote" class="btn btn-warning btn-lg" rel="tooltip" title="Punch and Add Note" data-placement="right" ><span class="glyphicon glyphicon-pencil"></span></button>
	
	</form>
</div>

<?php if (isset($_SESSION['message_alert'])) { $sessionMessage = new Message(); $sessionMessage->displayMessage(TRUE); } ?>
<?php if (isset($message)) { $message->displayMessage(); } ?>

<hr>

<?php 
if (isset($_SESSION['ask_for_earnings']) && !$user->isWorking()) {
?>
<div class="panel panel-warning">
  <div class="panel-heading">
    <h3 class="panel-title">Add earnings</h3>
  </div>
  <div class="panel-body">
    <p>Would you like to add your earnings to your last punch?</p>
    <form class="form-inline" method="post">
      <div class="form-group">
        <input type="text" class="form-control" id="inputEarnings" name="inputEarnings" >
      </div>

      <button type="submit" class="btn btn-default" name="add_earnings">Add Earnings</button>
    </form>
  </div>
</div>
<?php 
unset($_SESSION['ask_for_earnings']);
}
?>

<p>
<h3>Hello, <?php echo $user->firstName; ?></h3>
<?php

$today = strtolower(date("l"));


if ($schedule[$today] != 0) {
    echo "You are scheduled to work today from ".$schedule[$today]['start']." to ".$schedule[$today]['end'].". ";
} else {
    echo "You are not scheduled to work today. ";
}

if ($hoursWorkedToday != "00:00") {
    echo "You have worked $hoursWorkedToday hours so far.";
}


?>
</p>

<hr>

<div id="history">
<h2>Recent Punches</h2>
<table class="table table-striped table-hover " id="clickable">
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
        
        if ($user->timeFormat() == "12") {
		    $timeFormat = 'M d, Y h:i A';
		} else {
		    $timeFormat = 'M d, Y H:i';
		}

		$t = strtotime($entry['timestamp']);
		$time = date($timeFormat,strtotime($entry['timestamp']));
		
		
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
        echo "<td><a href='/punch/".$entry['id']."'>$time</a></td>";
        echo "<td>".$activity."</td>";
        echo "<td>".$entry['event']."</td>";
        echo "<td>";
        
        if (strlen($entry['note']) > 30) {
            echo "<a href='#' rel='tooltip' title='".toHTML($entry['note'])."' data-placement='right'>".substr(toHTML($entry['note']),0,27)."...</a>";
            //echo substr($entry['note'],0,27)."...";
        } else {
            echo toHTML($entry['note']);
        }
        echo "</td>";
        echo "</tr>";
    }
?>

  </tbody>
</table>

<hr>

<h2>Recent Hours Worked</h2>
<table class="table table-striped table-hover" id="clickable">
  <thead>
    <tr>
      <th>Start Time</th>
      <th>End time</th>
      <th>Hours Worked</th>
      <?php
      if ($user->isFreelance()) {
          echo "<th>Earnings</th>";
      }
      ?>
    </tr>
  </thead>
  <tbody>
<?php 
    foreach ($recentHoursWorked as $entry) {

		//$t = strtotime($entry['timestamp']);
		if ($user->timeFormat() == "12") {
		    $timeFormat = 'M d, Y h:i A';
		} else {
		    $timeFormat = 'M d, Y H:i';
		}
		
		$endTime = date($timeFormat,strtotime($entry['timestamp']));
	    $startTime = date($timeFormat,strtotime($entry['previous_timestamp']));
		
		echo "<tr>";
        echo "<td><a href='/punch/".$entry['id']."'>$startTime</a></td>";
        echo "<td>$endTime</td>";
        echo "<td>".$entry['hours_worked']."</td>";
        if ($user->isFreelance()) {
            if ( $entry['earnings'] != "" ) {
                echo "<td>\$".$entry['earnings']."</td>";
            } else {
                echo "<td>\$0.00</td>";
            }
        }
        echo "</tr>";
    }
?>

  </tbody>
</table>
</div>
<div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Add Note</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <textarea name="note" class="form-control" rows="3" id="textArea" maxlength="128"></textarea>
            <br>
            <font size="2">Maximum characters: 128</font>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" name="addNote" class="btn btn-primary">Add Note To Punch</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php 
    if (isset($_SESSION['requestingNote'])) { 
        unset($_SESSION['requestingNote']);
    ?>
    <script>
        $(window).load(function(){
            $('#noteModal').modal('show');
        });
    </script>
<?php    } ?>