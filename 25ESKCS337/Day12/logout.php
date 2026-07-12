<?php
require_once 'config.php';

// destroy session and go to login
session_unset();
session_destroy();
redirect('login.php');
?>
