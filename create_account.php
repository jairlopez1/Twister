<!DOCTYPE html>
<html lang="en">
<head>
   <title>Twister - Create Account</title>
   <link rel="stylesheet" href="bootstrap.min.css">
   <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar-header navbar-default" style="margin-bottom:15px">
	<div class="container">
		<a href="./">Twister</a>
	
	</div>
</nav>
<div class="container">

<?php
session_start();

const TAZ_USERNAME = 'dlemus';
//require('heading.php');

if (!isset($_POST['username']))
{
?>
	<h2>Create Account</h2>
	
	<form class="form-horizontal" action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="5000000">

	<div class="form-group">
		<label for="username" class="col-md-2 control-label">Username</label>
		<div class="col-md-4">
			<input type="text" class="form-control" id="username" name="username" 
					required pattern="[A-Za-z0-9]{3,}" autofocus>
		</div>
	</div>
	<div class="form-group">
		<label for="about" class="col-md-2 control-label">About</label>
		<div class="col-md-4">
			<textarea rows="3" cols="40" class="form-control" id="about" name="about"
				placeholder="Tell us about yourself."></textarea>
		</div>
	</div>
	<div class="form-group">
		<label for="password" class="col-md-2 control-label">Password</label>
		<div class="col-md-4">
			<input type="password" class="form-control" id="password" name="password" 
				required>
		</div>
	</div>
	<div class="form-group">
		<label for="profileImage" class="col-md-2 control-label">Profile Image
			<br><span class="extraInfo">(JPEG only)</span>
		</label>
		<div class="col-md-4">
			<input type="file" class="form-control" id="profileImage" name="imgfile" 
				required accept="image/jpeg" class="btn btn-default">
		</div>
	</div>
	<div>
		<input type="submit" class="btn btn-primary" value="Create Account">
	</div>
	</form>

<?php
	}
	else
	{  // POST
		// To use functions that upload and resize images
		require('image_util.php');   

		// Access MySQL database 
		require('TwisterDb.php');
		$twisterDb = new TwisterDb();
			
		// Get submitted data and verify it wasn't left blank
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
			
		// Don't allow short usernames or usernames with non-alphanumeric characters
		if (strlen($username) < 3 || !ctype_alnum($username))
			ShowError("Please go back and enter a username that is at least 3 characters and only 
				composed of letters and numbers.");

		if ($password == '')
			ShowError("Please go back and enter a password.");		

		$image_dir = "images";
		$upload_dir = "twister/$image_dir";

		// This is the directory the uploaded images will be placed in.
		// It must have priviledges sufficient for the web server to write to it
		$upload_directory_full = "/home/" . TAZ_USERNAME . "/public_html/$upload_dir";
		if (!is_writeable($upload_directory_full)) 
			ShowError("The directory $upload_directory_full is not writeable.\n");

		// Get the password hash for inserting into the database
		$passwordHash = password_hash($password, PASSWORD_BCRYPT);
		if ($twisterDb->AddUserAccount($username, $passwordHash, $_POST['about']))
		{
			echo "<h2>Account Created</h2>\n" .
				"<p><img src='$image_dir/$username.jpg' style='float:left; margin: 0pt 10pt 10px 10px;'></p>" .
				"<p><a href='edit_account.php'>Edit Account</a></p>\n";
		
			// Set session variable for use in other pages
			$_SESSION['username'] = $username;
		}
		else
		{
			ShowError("Sorry, but the username <b>$username</b> already exists. Please go back and 
				choose another.");
		}
		
		$image_filename = "$upload_directory_full/$username.jpg";
		
		// Save the uploaded image to the given filename
		$error_msg = UploadSingleImage($image_filename);
		if ($error_msg != "")
			ShowError($error_msg);
			
		// Save uploaded image with a maximum width or height of 300 pixels
		CreateThumbnailImage($image_filename, $image_filename, 300);
		
		// Create a small thumbnail of the image to be used later
		$image_thumbnail = $username . "_thumb.jpg";
		CreateThumbnailImage($image_filename, "$upload_directory_full/$image_thumbnail", 60);
		
	}  // end POST
?>

	</div>
</body>
</html>

<?php
	function ShowError($error)
	{
?>
<h2>There was a problem.</h2>
<p style="color:red"><?= $error ?></p>
<p><a href="javascript:history.back()">Go back</a></p>
</body>
</html>
<?php
		exit;
	} 
?> 