<?php
//  include_once 'login.php';
// include_once('conn.php');
$logout_time=date("Y-m-d H:i:s",time());
// $login_time=$_SESSION['login']; 
// $staffID=$_SESSION['staffID'];

// mysqli_query($con,"insert into log(staffID,loginDateTime,logoutDateTime) values('$staffID','$login_time','$logout_time')");

// foreach($_SESSION as $key=> $value){
//    unset($_SESSION[$key]); 
// }

// Clear local storage in the user's browser using JavaScript
// echo '<script>localStorage.clear();</script>';

session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();
?>
<script>
 localStorage.clear();
 window.location.href='login.html';
</script>
<!-- <?php
// Redirect the user to the login page (change the URL as needed)
//  header("Location: login.html");
// exit;
?> -->



