<?php 
include_once('./includes/functions.php'); 
session();
destroy_session();

header('Location: /login.php?logout');
exit;
?>
