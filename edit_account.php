<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Twiser: Edit Account</title>
	<link rel="stylesheet" href="bootstrap.min.css">
	<link rel="stylesheet" href="styles.css">
</head>
<body>

<?php

// TODO: Change the following
const TAZ_USERNAME = 'dlemus';

require('heading.php');

// Profile image locations
$image_dir = "images";
$upload_dir = "twister/$image_dir";
	
// Access MySQL database 
require('TwisterDb.php');
$twisterDb = new TwisterDb();

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$username = $_SESSION['username'];
	$profile_img = "$image_dir/$username.jpg";
	
	// Get user data from database
	$user = $twisterDb->GetUserInfo($username);
	$about = htmlspecialchars($user['about']);
?>
	<div class="container">
    <h2>Edit Account</h2>

    <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="5000000">

		<div class="form-group">
			<label for="username" class="col-md-2 control-label">Username</label>
			<div class="col-md-4">
				<?= $username ?>
			</div>
		</div>
		<div class="form-group">
			<label for="about" class="col-md-2 control-label">About</label>
			<div class="col-md-4">
				<textarea rows="3" cols="40" class="form-control" id="about" name="about" required autofocus><?= $about ?></textarea>
			</div>
		</div>
		<div class="form-group">
			<label for="password" class="col-md-2 control-label">Password</label>
			<div class="col-md-4">
				<input type="password" class="form-control" id="password" name="password">
			</div>
		</div>
		<div class="form-group">
			<label for="profileImage" class="col-md-2 control-label">Profile Image
				<br><span class="extraInfo">(JPEG only)</span>
			</label>
			
			<div class="col-md-4">
				<img src="<?= $profile_img ?>">
				<br>
				<input type="file" class="form-control" id="profileImage" name="imgfile" 
					accept="image/jpeg" class="btn btn-default">
			</div>
		</div>
				
		<div>
			<input type="submit" class="btn btn-primary" value="Save">
		</div>
    </form>
	</div>
</body>
</html>
<?php
	}

	else
	{  // POST
	require('image_util.php');   // To use functions that upload and resize images

	if (!isset($_SESSION['username']))
		ShowError("Username not set");	

	// Get submitted data 
	$about = trim($_POST['about']);
	$username = $_SESSION['username'];
	$password = trim($_POST['password']);

	if (ImageUploaded())
	{
		// This is the directory the uploaded images will be placed in.
		// It must have priviledges sufficient for the web server to write to it
		$upload_directory_full = "/home/" . TAZ_USERNAME . "/public_html/$upload_dir";
		if (!is_writeable($upload_directory_full)) 
			ShowError("The directory $upload_directory_full is not writeable.\n");


		$image_filename = "$upload_directory_full/$username.jpg";
		
		// Save the uploaded image to the given filename
		$error_msg = UploadSingleImage($image_filename);
		if ($error_msg != "")
			ShowError($error_msg);
		
		// Save uploaded image with a maximum width or height of 300 pixels
		CreateThumbnailImage($image_filename, $image_filename, 300);
		
		// Create a very small thumbnail of the image to be used later
		$image_thumbnail = $username . "_thumb.jpg";
		CreateThumbnailImage($image_filename, "$upload_directory_full/$image_thumbnail", 60);
	}

	// Get the Bcrypt hash of the password if it was changed 
	$passwordHash = '';
	if ($password != '')
	{		
		$passwordHash = password_hash($password, PASSWORD_BCRYPT);
	}
				
	$twisterDb->UpdateUserAccount($username, $passwordHash, $about);

	header("Location: index.php");
	} // end POST


	function ShowError($error)
	{
?>
    <h1>Unable to edit the account</h1>
    <p><?= $error ?></p>
	<p><a href="javascript:history.back()">Go back</a></p>
</body>
</html>
<?php
		exit;
	} 
?>