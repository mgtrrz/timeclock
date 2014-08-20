<?php

echo "<form method='post'><input type='text' name='number'><button type='submit' value='submit'>submit</button></form>\n";

if (isset($_POST['number'])) {
    $time = $_POST['number'];
    
    if (preg_match('/^(([0-1]?[0-9]|2[0-3]):[0-5][0-9]|24:00)$/', $time , $matches))
        echo "$time is very good!";
    else
        echo "$time is very bad!!! NO";
}