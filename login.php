<!DOCTYPE html>
<html>
<head><title>Twister - Login</title>
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
	
    <h2>Login to Twister</h2>

    <form action="/~dlemus/twister/login.php" method="POST" class="form-horizontal">

		<div class="form-group">
			<label for="username" class="col-md-1 control-label">Username</label>
			<div class="col-md-4">
				<input type="text" class="form-control" id="username" name="username" 
					value="" autofocus>
			</div>
		</div>
		<div class="form-group">
			<label for="username" class="col-md-1 control-label">Password</label>
			<div class="col-md-4">
				<input type="password" class="form-control" name="password"></td></tr>
			</div>
		</div>
		<input type="submit" class="btn btn-primary" value="Login">
    </form>
	
	<p><br>
	    Don't have an account? <a href="create_account.php">Create one</a> for free!
	</p>
	</div>
</body>
</html>

<?php 

// POST?
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// Get password hash from the database
	require("TwisterDb.php");
    $twisterDb = new TwisterDb();
	$user = $twisterDb->GetUserInfo($_POST['username']);
	$hash = $user['password'];
	
	//print_r($user);
	
	// Is the password hash from the database equivalent to
	// the hash of the password just POSTed?
	$newHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
	
    if (password_verify($_POST['password'], $hash))
    {
		//echo "<p>Welcome!</p>";
        session_start();
        
        $_SESSION['login'] = true;
		$_SESSION['username'] = $_POST['username'];
		
		// Redirect to index.php 
		header("Location: index.php");
	}
    else
    {
		echo "<p>Wrong username or password.</p>";
	}
}
?>