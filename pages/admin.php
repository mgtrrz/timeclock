<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

if (!$user->isAdmin()) {
	include('./template/default/header.php');
    $msg = new Message("You do not have sufficient privileges to view this page.", "alert-danger");
    $msg->displayMessage();
    include('./template/default/footer.php');
    exit;
    
} else {
    include_once('./includes/admin.class.php');
    $admin = new Admin();
    
    $usersWorking = $admin->usersWorking();
    
    if (isset($_POST['admin_punch'])) {
	    $punchSettings = array(
        	"event" => "Punch*",
        	"note" => "Added by ".$user->getUserRealName(TRUE)
		);
	    
	    $SubUser = new User($_POST['user']);
	    $result = $SubUser->punch($punchSettings);
	    
	    $employeeName = $SubUser->getUserRealName();
	    
	    if ($result['success'] == 1) {
	    	$successMessage = "Successfully punched ".$employeeName['first_name']." ".$employeeName['last_name'].".";
			$alertMessage = "alert-success";
	    } else {
		    $successMessage = $result['return'];
			$alertStyle = "alert-danger";
	    }
		
		new Message($successMessage, $alertMessage, TRUE);
		
		unset($_POST['admin_punch']);
	    header("Location: /admin");
	    exit();
	    
    }
    
    include('./template/default/header.php');
}
?>

<hr>

<ul class="nav nav-pills">
  <li <?php if($requestURI[1] == "") { echo 'class="active"'; } ?>><a href="/admin">Dashboard</a></li>
  <li <?php if($requestURI[1] == "users") { echo 'class="active"'; } ?>><a href="/admin/users">Add/Modify Users</a></li>
  <li <?php if($requestURI[1] == "settings") { echo 'class="active"'; } ?>><a href="/admin/settings">Global Settings</a></li>
</ul>

<hr>

<?php

if ($requestURI[1] == "") {
    include('./pages/admin_main.php');
} elseif ($requestURI[1] == "users") {
    include('./pages/admin_users.php');
}