<?php
/*
echo "<form method='post'><input type='text' name='number'><button type='submit' value='submit'>submit</button></form>\n";

if (isset($_POST['number'])) {
    $time = $_POST['number'];
    
    if (preg_match('/^(([0-1]?[0-9]|2[0-3]):[0-5][0-9]|24:00)$/', $time , $matches))
        echo "$time is very good!";
    else
        echo "$time is very bad!!! NO";
}
*/
echo "<pre>";

//echo date("H:i", strtotime('+3:05', strtotime('4:52')));
/*
$totalTime = "00:00";

$time = "03:58";
$time2 = "02:10";
$time3 = "01:06";

$secs = strtotime($time)-strtotime("00:00:00");
$totalTime = date("H:i",strtotime($totalTime)+$secs);

echo $totalTime. "\n";

$secs = strtotime($time2)-strtotime("00:00:00");
$totalTime = date("H:i",strtotime($totalTime)+$secs);

echo $totalTime. "\n";

$secs = strtotime($time3)-strtotime("00:00:00");
$totalTime = date("H:i",strtotime($totalTime)+$secs);

echo $totalTime. "\n";
*/

function addTimes($times) {
    
    if (is_array($times)) {
        
        $length = sizeof($times);
        
        $totalTime = "00:00";
        
		for($x=0; $x < $length; $x++){

		    $secs = strtotime($times[$x])-strtotime("00:00:00");
            $totalTime = date("H:i",strtotime($totalTime)+$secs);
		    
		}
		
		return $totalTime;
    }
    
}

$time = array("4:00");
$time2 = array("1:00", "2:00");
$time3 = array("3:20", "1:30", "5:00");

echo addTimes($time). "\n";
echo addTimes($time2). "\n";
echo addTimes($time3). "\n";