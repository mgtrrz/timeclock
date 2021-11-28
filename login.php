<?php
include_once('./includes/functions.php'); 
session_start();

if (isset($_GET['logout'])) {
    $alert = "Successfully logged out!";
    $alertStyle = "alert-success";
}

if (isset($_POST['user']) && isset($_POST['pass'])) {
    // Checking to see if our two variables are empty
    if ($_POST['user'] == "") {
        $warning = "Username empty";
        $alertStyle = "alert-danger";
    } elseif ($_POST['pass'] == "") {
        $warning = "Password empty";
        $alertStyle = "alert-danger";
    } else {
        // We've got some input, let's check it.
        $cred = array(
            "username" => preg_replace("[^A-Za-z0-9]", "",$_POST['user']),
            "password" => $_POST['pass']
        );
        
        $result = communicate("check_password", $cred);
        
        if ($result) {
            initiate_session();
            $_SESSION['sid'] = $result['staff_id'];
            $_SESSION['fn'] = $result['first_name'];
            $_SESSION['ln'] = $result['last_name'];
            $toFrontPage = $_SERVER['SERVER_NAME'];
            header("Location: /");
            exit;
        } else {
            $alert = "Incorrect user or password";
            $alertStyle = "alert-danger";
        }
    }
} 

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">    

    <title>Log in to Timeclock</title>

    <!-- Bootstrap core CSS -->
    <link href="template/default/css/lumen_bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="template/default/css/login.css" rel="stylesheet">

  </head>

  <body>

    <div class="container">

      <form class="form-signin" role="form" method="post">
        <h2 class="form-signin-heading">Timeclock</h2>
        <input type="text" class="form-control" placeholder="User name" name="user" autocorrect="off" autocapitalize="off" value="<?php if (isset($_POST['user']) && $cred['username'] != "") { echo $cred['username']; } ?>" required <?php if (!isset($_POST['user'])) { echo "autofocus"; } ?>>
        <input type="password" class="form-control" placeholder="Password" name="pass" required <?php if (isset($_POST['user'])) { echo "autofocus"; } ?>>
        <div class="checkbox">
        </div>
        <?php if (isset($alert) && $alert != "") { echo '<div class="alert '.$alertStyle.'">'.$alert.'</div>'; $alert = "" ; } ?>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
      </form>

    </div> <!-- /container -->

  </body>
</html>
