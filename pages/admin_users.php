<?php

// Must be added to all pages
if (!defined('TIMECLOCK')) die();

$allUsers = $admin->getAllUsers();

if (is_numeric($requestURI[2])) {
    include('./pages/admin_user_edit.php');
} else {
?>

<h2>Modify Users</h2>

<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label for="select" class="col-lg-2 control-label">Users</label>
      <div class="col-lg-10">
        <select class="form-control" id="select" name="form" onchange="location = this.options[this.selectedIndex].value;">
          <option>Select User</option>
          <?php
          
          foreach ($allUsers as $user) {
              echo '<option value="/admin/users/'.$user['staff_id'].'" >'.$user['first_name'].' '.$user['last_name'].'</option>';
          }
          
          ?>
        </select>
      </div>
    </div>
  </fieldset>
</form>

<hr>

<h2>Create User</h2>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label for="inputEmail" class="col-lg-2 control-label">Email</label>
      <div class="col-lg-10">
        <input type="text" class="form-control" id="inputEmail" placeholder="Email">
      </div>
    </div>
    
    <div class="form-group">
      <label for="inputFirstName" class="col-lg-2 control-label">First name</label>
      <div class="col-lg-10">
        <input type="text" class="form-control" id="inputFirstName" placeholder="First name">
      </div>
    </div>
    
    <div class="form-group">
      <label for="inputLastName" class="col-lg-2 control-label">Last name</label>
      <div class="col-lg-10">
        <input type="text" class="form-control" id="inputLastName" placeholder="Last name">
      </div>
    </div>
    
    <div class="form-group">
      <label for="inputUserName" class="col-lg-2 control-label">User name</label>
      <div class="col-lg-10">
        <input type="text" class="form-control" id="inputUserName" placeholder="User name">
      </div>
    </div>
    
    <div class="form-group">
      <label for="inputChtrUser" class="col-lg-2 control-label">Chtr.me Username</label>
      <div class="col-lg-10">
        <input type="text" class="form-control" id="inputChtrUser" placeholder="Chtr.me username">
      </div>
    </div>
    
    
    <div class="form-group">
        <label class="col-lg-2 control-label">Schedule Type</label>
      <div class="col-lg-10">
        <div class="radio">
          <label>
            <input type="radio" name="freelance" id="optionsRadios1" value="0" checked="">
            Scheduled
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="freelance" id="optionsRadios2" value="1">
            Freelance
          </label>
        </div>
      </div>
    </div>
    
    
    <div class="col-lg-10">
    <label class="col-lg-2 control-label">&nbsp;</label>
      <div class="checkbox">
        <label>
          <input type="checkbox"> Will be an Admin user
        </label>
      </div>
    </div>
    
    <div class="col-lg-10">
    <label class="col-lg-2 control-label">&nbsp;</label>
      <div class="checkbox">
        <label>
          <input type="checkbox"> Receive email alerts
        </label>
      </div>
    </div>
    
    <div class="col-lg-10">
    <label class="col-lg-2 control-label">&nbsp;</label>
      <div class="checkbox">
        <label>
          <input type="checkbox"> Receive chtr.me alerts
        </label>
      </div>
    </div>
    <br>
    
    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </div>
    
  </fieldset>
</form>
<?php
}