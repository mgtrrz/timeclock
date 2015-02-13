<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

$punchResults = getPunchDetails($requestURI[1]);

if (isset($_POST['edit'])) {
    $editMode = true;
} else {
    $editMode = false;
}

if ($user->isAdmin()) {
    $isAdmin = true;
} else {
    $isAdmin = false;
}

if ($user->isFreelance()) {
    $isFreelance = true;
} else {
    $isFreelance = false;
}

$pPunch = getPunchDetails( $requestURI[1], $punchResults['user_id'], "previous" );
$nPunch = getPunchDetails( $requestURI[1], $punchResults['user_id'], "next" );

if ( isset($_POST['save']) && !empty($punchResults) ) {
    if ( $isAdmin || $isFreelance ) {

        $newChanges = array();
        
        if ( $_POST['note'] != $punchResults['note'] ) {
            $newChanges['note'] = $_POST['note'];
        }
        
        $error = false;
        
        if ( $_POST['timestamp'] != $punchResults['timestamp'] ) {
            
            $ts = $_POST['timestamp'];
            
            if ( empty($nPunch) && strtotime($ts) > strtotime("now") ) {
                $error = "New timestamp cannot be later than the current time!";
            }
            
            if ( !empty($pPunch) && strtotime($ts) <= strtotime($pPunch['timestamp']) ) {
                $error = "New timestamp cannot be earlier than last timestamp!";
            }
            
            if ( !empty($nPunch) && strtotime($ts) > strtotime($nPunch['timestamp']) ) {
                $error = "New timestamp cannot be later than next timestamp!";
            }
            
            if (!$error) {
                $newChanges['timestamp'] = $_POST['timestamp'];
                
            } 
    
        }
        
        if ( $_POST['earnings'] != $punchResults['earnings'] && is_numeric($_POST['earnings'])) {
            $newChanges['earnings'] = abs($_POST['earnings']);
        }
        
            
        if (!$error || !empty($newChanges) ) {
            // No errors and "$newChanges" isn't empty!
            // Going to save this to the row in the database:
            $result = savePunchDetails($requestURI[1], $newChanges);
            
            // Adjusting Hours Worked!
            if ( $punchResults['in_out'] == 0  ||  $punchResults['in_out'] == 1 && !empty($nPunch) ) {
                adjustHoursWorked( $punchResults['user_id'] );
            }
            
            if ( $result['status'] != false ) {
                $alert = new Message("Punch changes were successful!", "alert-success");
            } else {
                $alert = new Message("There was an error submitting your punch.", "alert-danger");
            }
        
        } else {
            
            $alert = new Message($error, "alert-danger");
        
        }
        
        $punchResults = getPunchDetails($requestURI[1]);
    }
}

if ($isAdmin || $isFreelance) {
    $canEdit = true;
} else {
    $canEdit = false;
}


if (!empty($punchResults)) {
    
    if (!$isAdmin && $punchResults['user_id'] != $user->getUserID()) {
        echo '<br /><br /><br /><br /><br /><br />';
        echo '<div class="panel panel-danger">';
        echo '  <div class="panel-heading">';
        echo '    <h3 class="panel-title"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> Forbidden</h3>';
        echo '  </div>';
        echo '  <div class="panel-body">';
        echo '  You are not authorized to view this page.';
        echo '  </div>';
        echo '</div>';

    } else {
        
        $tempUser = new User($punchResults['user_id']);
        
        ?>
        
        <h2><?php if ($editMode) echo "Edit "; ?>Punch Details</h2>
        <form class="form-horizontal" method="post">
        <div class="form-group">
          <label class="col-lg-3 control-label">User</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php if ($isAdmin) { echo "<a href=\"/admin/users/" . $punchResults['user_id'] . "\">" . $tempUser->getUserRealName(true) . "</a>"; } else { echo $tempUser->getUserRealName(true); } ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Timestamp</label>
          <div class="col-lg-9">
            <?php if ( $canEdit && $editMode ) { ?>
                <input type="text" id="datetimepicker" name="timestamp" class="form-control" value="<?php echo $punchResults['timestamp']; ?>">
            <?php } else { ?>
                <p class="form-control-static"><?php echo $punchResults['timestamp']; ?></p>
            <?php } ?>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Activity</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php if( $punchResults['in_out'] == 1 ) { echo "<span class=\"glyphicon glyphicon-log-in\"></span> Clocked In"; } else { echo "<span class=\"glyphicon glyphicon-log-out\"></span> Clocked Out"; } ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Event</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $punchResults['event']; ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">IP Address</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $punchResults['ip_address']; ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Note</label>
          <div class="col-lg-9">
            <?php if ( $canEdit && $editMode ) { ?>
                <textarea class="form-control" name="note" rows="3" id="textArea" maxlength="128"><?php echo $punchResults['note']; ?></textarea>
            <?php } else { ?>
                <p class="form-control-static"><?php echo $punchResults['note']; ?></p>
            <?php } ?>
          </div>
        </div>
        <?php if( $punchResults['in_out'] == 1 ) { ?>
        <div class="form-group">
          <label class="col-lg-3 control-label">Worked Until</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $nPunch['timestamp']; ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Hours Worked</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $nPunch['hours_worked']; ?></p>
          </div>
        </div>
        <?php } else { ?>
        <div class="form-group">
          <label class="col-lg-3 control-label">Started at</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $punchResults['previous_punch']; ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Hours Worked</label>
          <div class="col-lg-9">
            <p class="form-control-static"><?php echo $punchResults['hours_worked']; ?></p>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">Earnings</label>
          <div class="col-lg-9">
          
            <?php if ( $canEdit && $editMode ) { ?>
                <input type="text" class="form-control" name="earnings" value="<?php echo $punchResults['earnings']; ?>">
            <?php } else { 
                    if ($punchResults['earnings'] != "") { ?>
                        <p class="form-control-static">$<?php echo $punchResults['earnings']; ?></p>
                <?php } else { ?>
                        <p class="form-control-static">$0.00</p>
            <?php   }
                }
            ?>

          </div>
        </div>
        <?php } ?>

        <?php
        if ( $isAdmin || $isFreelance ) {
            if ($editMode) {
        ?>
        
        <div class="form-group">
          <div class="col-lg-9 col-lg-offset-3">
            <button type="submit" name="save" class="btn btn-success">Save Changes</button>
            <button class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this punch?')" >Delete</button>
            <a href="/punch/<?php echo $requestURI[1] ?>" class="btn btn-default">Cancel</a>
          </div>
        </div>
        
        <?php
            } else {
        ?>
        
        <div class="form-group">
          <div class="col-lg-9 col-lg-offset-3">
            <button type="submit" name="edit" class="btn btn-primary">Edit Punch</button>
          </div>
        </div>
        
        
        <?php
            }
        }
        ?>
        
        </form>
        
        <?php if ($alert) { $alert->displayMessage(); } ?>
        
        <ul class="pager">
        <?php
        
        if ($isAdmin) {
            $prevPunch = $requestURI[1] - 1;
            if ($prevPunch == 0) {
                $prevPunch = 1;
                $disabled = true;
            }
            
            $nextPunch = $requestURI[1] + 1;
            
            if ($nextPunch > maxPunchID()) {
                $nextPunch = $requestURI[1];
                $nDisabled = true;
            }
        } else {
            
            
            if ( !empty( $pPunch['id'] ) ) {
                $prevPunch = $pPunch['id'];
            } else {
                $prevPunch = $requestURI[1];
                $disabled = true;
            }
            
            if ( !empty( $nPunch['id'] ) ) {
                $nextPunch = $nPunch['id'];
            } else {
                $nextPunch = $requestURI[1];
                $nDisabled = true;
            }
            
            
        }
        
        ?>
          <li class="previous <?php if ($disabled) { echo "disabled"; } ?>"><a href="/punch/<?php echo $prevPunch ?>">Previous</a></li>
          <li class="next <?php if ($nDisabled) { echo "disabled"; } ?>"><a href="/punch/<?php echo $nextPunch ?>">Next</a></li>
        </ul>


        <?php

    }

} else {
    echo "<pre>";
    echo "No results for ".$requestURI[1];
    echo "</pre>";
}

?>
<link rel="stylesheet" type="text/css" href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/lib/datetimepicker/jquery.datetimepicker.css"/ >
<script src="http://<?php echo $_SERVER['SERVER_NAME']; ?>/lib/datetimepicker/jquery.datetimepicker.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    
    $('#datetimepicker').datetimepicker({
        format:'Y-m-d H:i:s',
        maxDate:0
    });
    
});  
</script>