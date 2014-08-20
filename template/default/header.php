<?php
$userIsWorking = $user->isWorking();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    

    <title>TimeClock</title>

    <!-- Bootstrap core CSS -->
    <link href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/template/default/css/lumen_bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/template/default/css/timeclock.css" rel="stylesheet">
    <? if ($main) { ?>
    <link href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/template/default/css/clock-main.css" rel="stylesheet">
    <? } else {
        if ($userIsWorking) { ?>
    <link href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/template/default/css/clock-min-right.css" rel="stylesheet">
    <? } else { ?>
    <link href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/template/default/css/clock-min.css" rel="stylesheet">
    <? }
    }?>
    <!-- Jquery! -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

    <!-- Bootstrap minified JS -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

  </head>

  <body onload="updateClock(); setInterval('updateClock()', 1000 ) ">

  <div id="container">
  
  <div class="navbar navbar-default">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="/">TimeClock</a>
  </div>
  <div class="navbar-collapse collapse navbar-responsive-collapse">
    <ul class="nav navbar-nav">
      <li <?php if ($requestURI[0] == "") { echo 'class="active"'; } ?>><a href="/">Punches</a></li>
      <li <?php if ($requestURI[0] == "schedule") { echo 'class="active"'; } ?>><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/schedule">Schedule</a></li>
      <li <?php if ($requestURI[0] == "statistics") { echo 'class="active"'; } ?>><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/statistics">Statistics</a></li>
      <li <?php if ($requestURI[0] == "earnings") { echo 'class="active"'; } ?>><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/earnings">Earnings</a></li>
    </ul>
    
    <ul class="nav navbar-nav navbar-right">
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['fn'] ." ". $_SESSION['ln']; ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <?php if($user->isAdmin()) { echo '<li><a href="http://'.$_SERVER['SERVER_NAME'].'/admin">Admin</a></li>' ; } ?>
          <li><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/edit">Edit Profile</a></li>
          <li class="divider"></li>
          <li><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/logout.php">Log out</a></li>
        </ul>
      </li>
    </ul>
  </div>
</div>
<div id="workingBox">
<?php if ($userIsWorking) {
    echo '<div class="alert alert-warning" role="alert">Clocked in and working. Completed <span id="realtime">'.timeBetweenDatesWithSeconds($user->lastPunch()).'</span> hours.</div>';
} ?>
</div>
<div id="clockDisplay">
	<span id="clock">&nbsp;</span>
</div>