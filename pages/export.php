<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();


download_send_headers("data_export_" . date("Y-m-d") . ".csv");
echo array2csv($array);
die();
