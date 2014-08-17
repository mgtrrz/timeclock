<?php
require('./lib/password.php');
if (isset($_GET['p'])) {
    echo password_hash($_GET['p'], PASSWORD_BCRYPT);
}