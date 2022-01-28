<?php
session_start();

const TAZ_USERNAME = 'dlemus';
	
require('TwisterDb.php');
$twisterDb = new TwisterDb();

$twisterDb = new TwisterDb();
$username = $_SESSION['username'];
$followUsername = $_POST['remove'];
$removeFollower = $twisterDb->RemoveFollow($username, $followUsername);

header("Location: index.php");
?>