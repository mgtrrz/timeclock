<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

if (isset($_POST['change_password'])) {
	if ($_POST['p1'] == "" || $_POST['p2'] == "") {
		$alert = "Please fill both password fields";
	    $alertStyle = "alert-danger";
	} elseif ($_POST['p1'] != $_POST['p2']) {
		$alert = "Passwords do not match.";
	    $alertStyle = "alert-danger";
	} else {
		if ($result = $user->changePassword($_POST['p1'])) {
		    $alert = "Password successfully changed!";
	        $alertStyle = "alert-success";
		} else {
		    $alert = "Could not change password. Contact administrator.";
	        $alertStyle = "alert-danger";
		}
		
	}
}

if (isset($_POST['save_changes'])) {
    if ($user->setTimeFormat($_POST['time_format'])) {
        $alert = "Settings successfully changed!";
	    $alertStyle = "alert-success";
    } else {
        $alert = "Could not save settings. Please contact administrator.";
	    $alertStyle = "alert-danger";
    }
}

$userEmail = $user->getEmail();

?>

<h1>Edit Profile</h1>
<h2>General Settings</h2>
<form role="form" method="post">
  <div class="form-group">
    <label for="select" class="control-label">Time Format</label>
      <select class="form-control" id="select" name="time_format">
        <option <?php if ($user->timeFormat() == "12") echo "selected"; ?>>12 hr</option>
        <option <?php if ($user->timeFormat() == "24") echo "selected"; ?>>24 hr</option>
      </select>
  </div>
    <button type="submit" class="btn btn-default" name="save_changes">Save Changes</button>
</form>
<div id="alert">
	<?php if (isset($alert) && $alert != "") { echo '<div class="alert '.$alertStyle.' fade in"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'.$alert.'</div>'; $alert = "" ; $alertStyle = ""; } ?>
</div>

<hr>
<h2>Change password</h2>
<form role="form" method="post">
  <div class="form-group">
    <label for="exampleInputEmail1">New Password</label>
    <input type="password" class="form-control" id="p1" name="p1" placeholder="Password" required>
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Confirm New Password</label>
    <input type="password" class="form-control" id="p2" name="p2" placeholder="Confirm Password" required>
  </div>
  <button type="submit" class="btn btn-default" name="change_password">Change Password</button>
</form>
<div id="alert">
	<?php if (isset($alert) && $alert != "") { echo '<div class="alert '.$alertStyle.' fade in"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'.$alert.'</div>'; $alert = "" ; $alertStyle = ""; } ?>
</div>

<hr>

<h2>Change email</h2>
<form role="form" method="post">
  <div class="form-group">
    <label for="exampleInputEmail1">Update email address</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo $userEmail; ?>" required>
  </div>
  <button type="submit" class="btn btn-default" name="change_email">Change Email</button>
</form>

<hr>
