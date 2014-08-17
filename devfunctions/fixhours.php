<?php
include('./config.php');
include('./functions.php');
include('./user.class.php');

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    

    <title>TimeClock</title>

    <!-- Bootstrap core CSS -->
    <link href="css/lumen_bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/timeclock.css" rel="stylesheet">
    
    <!-- Jquery! -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

    <!-- Bootstrap minified JS -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

  </head>

  <body onload="updateClock(); setInterval('updateClock()', 1000 ) ">
  <div id="container">
<?php
if (isset($_GET['user'])) {
    
    if ($_GET['confirm']) {
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $data = $db->prepare('SELECT * from punches WHERE user_id=:userid AND in_out=0 AND hours_worked IS NULL;');
        $data->bindParam(':userid', $_GET['user']);
        $data->execute();
        $result = $data->fetchALL(PDO::FETCH_ASSOC);
        
        foreach ($result as $row) {
            $newHoursWorked = timeBetweenDates($row['previous_punch'], $row['timestamp']);
            
            $data = $db->prepare('UPDATE punches SET hours_worked=:hoursworked WHERE id=:id');
            $data->bindParam(':hoursworked', $newHoursWorked);
            $data->bindParam(':id', $row['id']);
            $data->execute();
        }
        
    }
    
    
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = $db->prepare('SELECT * from punches WHERE user_id=:userid AND in_out=0 AND hours_worked IS NULL;');
    $data->bindParam(':userid', $_GET['user']);
    $data->execute();
    $result = $data->fetchALL(PDO::FETCH_ASSOC);
    
    echo '<h2>BEFORE: </h2>';
    echo '<table class="table table-striped table-hover ">';
    echo '  <thead>';
    echo '<tr>';
    echo '  <th>id</th>';
    echo '  <th>timestamp</th>';
    echo '  <th>previous_punch</th>';
    echo '  <th>hours_worked</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($result as $row) {
		echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['timestamp']."</td>";
        echo "<td>".$row['previous_punch']."</td>";
        echo "<td>".$row['hours_worked']."</td>";
        echo "</tr>";
    }

    echo '</tbody>';
    echo '</table>';
    
    /* ---------------------------------------------------- */
    echo '<h2>AFTER: </h2>';
    echo '<table class="table table-striped table-hover ">';
    echo '  <thead>';
    echo '<tr>';
    echo '  <th>id</th>';
    echo '  <th>timestamp</th>';
    echo '  <th>previous_punch</th>';
    echo '  <th>hours_worked</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($result as $row) {
        
        $newHoursWorked = timeBetweenDates($row['previous_punch'], $row['timestamp']);
        
		echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['timestamp']."</td>";
        echo "<td>".$row['previous_punch']."</td>";
        echo "<td>".$newHoursWorked."</td>";
        echo "</tr>";
    }

    echo '</tbody>';
    echo '</table>';
    echo '<div id="punchButton">';
	echo '<form role="form" method="get">';
	echo '<input type="hidden" name="user" value="'.$_GET['user'].'">';
	echo '	<button type="submit" name="confirm" class="btn btn-primary btn-lg extraWide" value="1">CONFIRM</button>';
	echo '</form>';
    echo '</div>';
    
} else {
    echo "Begin fixing hours worked by appending: ?user=userid";
}
include('./footer.php');