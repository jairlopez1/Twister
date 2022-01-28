<?php 
session_start();
session_destroy();  // Deletes all session vars 
?>

<html>
<head><title>MyFaceSpace - Logout</title>
<link rel="stylesheet" href="bootstrap.min.css">
<link rel="stylesheet" href="styles.css">

</head>
<body>

<header class="container">

<nav class="navbar-header navbar-default" style="margin-bottom:15px">
	<div class="container">
		<a href="./">Twister</a>
	</div>
</nav>

</header>

<div class="container">
You have been logged out.  
<a href="login.php">Log back in.</a>
</div>

</body>
</html>