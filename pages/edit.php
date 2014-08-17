<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

if (isset($_POST['psub'])) {
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

$userEmail = $user->getEmail();

?>
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
  <button type="submit" class="btn btn-default" name="psub">Change Password</button>
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
  <button type="submit" class="btn btn-default" name="psub">Change Email</button>
</form>

<hr>

<h2>Chtr.me username</h2>
<form role="form" method="post">
  <div class="form-group">
    <label for="exampleInputEmail1">Receive alerts through Chtr.me</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="<?php echo $userEmail; ?>" required>
  </div>
  <button type="submit" class="btn btn-default" name="psub">Change Email</button>
</form>