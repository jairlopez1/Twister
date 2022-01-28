<?php
session_start();

const TAZ_USERNAME = 'dlemus';

require('TwisterDb.php');    
$twisterDb = new TwisterDb();

$username = $_SESSION['username'];
$followUsername = $_POST['follow'];
$addFollower = $twisterDb->AddFollow($username, $followUsername);

header("Location: index.php");
?>