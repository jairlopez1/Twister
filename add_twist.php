<?php
session_start();

const TAZ_USERNAME = 'dlemus';

require('TwisterDb.php');
$twisterDb = new TwisterDb();

$username = $_SESSION['username'];
$text = $_POST['twist'];
$postTwit = $twisterDb->AddTwist($username, $text);

header("Location: index.php");
?>