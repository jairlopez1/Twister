<?php
/* 
	Authors:
		- Jair Lopez
		- Denilson Lemus
*/

session_start();

if (!isset($_SESSION['login']))
{
	// Redirect to login.php
	header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

    <title>Twister</title>

    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

</head>
<body>

<?php

const TAZ_USERNAME = 'dlemus';

require('heading.php');

// Profile image locations
$image_dir = "images";
$upload_dir = "twister/$image_dir";
	
// Access MySQL database 
require('TwisterDb.php');
$twisterDb = new TwisterDb();

//****************************************
	$username = $_SESSION['username'];
	$profile_img = "$image_dir/$username.jpg";
	
	// Get user data from database
	$user = $twisterDb->GetUserInfo($username);
	$following = $twisterDb->GetFollowingCount($username);
	$follower = $twisterDb->GetFollowerCount($username);
	$twistCount = $twisterDb->GetTwistCount($username);
	$twists = $twisterDb->GetAllTwists($username);
	$about = htmlspecialchars($user['about']);
//****************************************
?>

<div class="container">
	<img src="<?=$profile_img?>" alt="Profile image" class="profilePic">
	<div class="profileInfo">
		<h3><?= $username ?></h3> 
		<p class="about"> <?= $about ?> &nbsp; &nbsp; <a href="edit_account.php">Edit</a></p>
		<p>
		Twists: <strong><?=$twistCount?></strong> &nbsp;
		Followers: <strong><?=$follower?></strong> &nbsp;
		Following: <strong><?=$following?></strong>
		</p>

		<form method="post" action="add_twist.php" class="form-inline statusForm">
			<textarea name="twist" id="twist" class="form-control" required rows="3" cols="70" maxlength="150" placeholder="What's happening?"></textarea>
			<div class="twistBlock">
				<button class="btn btn-primary" type="submit">Twist</button><br><br>
				<span id="charsLeft" class="statusGood" style = "color:blue">150</span>
			</div>
		</form>
	</div>
</div>

<div class="container twistList">
<form method="post" action="remove_follow.php">


<table class="table table-striped">
<tbody>
<?php
	$c = sizeof($twists);
	for($x = 0; $x < $c; $x++)
	{
		$person = $twists[$x]['username'];
		$pic = "$image_dir/$person.jpg";
		$time = $twists[$x]['timestamp'];
		$niceTime = Nicetime("$time");
?>
	<tr>
	<td width="50">
		<img src="<?=$pic?>" alt="profile picture" border="0" width = "100" height = "100">
	</td>
	<td valign="top">
		<span class="username"><?=$person?></span>
		&nbsp;
		<span class="status"> 
			<?=$twists[$x]['message']?>
		</span>
		<br>
		<span class="timestamp"><?=$niceTime?></span>
	</td>

	<?php

	if($person != $username)
	{
	?>
	<td>
		<button type="submit" class="btn btn-danger" aria-label="Unfollow" 
					name="remove" value="<?=$person?>">Unfollow</button>
	</td>

	<?php
	}
	else
	{
		echo "<td></td>";	
	}
	}
	?>
</form>
</tbody></table>

</div>
</body>

<script>
	var twist = document.querySelector("#twist");
	var charsLeft = document.querySelector("#charsLeft");

	twist.addEventListener("keydown", changeCounter);

	function changeCounter(){
		var chars = twist.value.split('');
		var count = 150 - chars.length;
		charsLeft.innerText = count;

		if(count > 20){
			charsLeft.style.color = "blue";
		}
		else{
			charsLeft.style.color = "red";
		}
	}

</script>

</html>

<?php
function NiceTime($date)
{
	if (empty($date))
		return "";
	// Can set timezone if not wanting to use default
	date_default_timezone_set('America/Chicago');
	$periods = [ "second", "minute", "hour", "day", "week", "month", "year", "decade" ];
	$lengths = [ "60", "60", "24", "7", "4.35", "12", "10" ];
	$now = time();
	$unix_date = strtotime($date);
	// check validity of date
	if (empty($unix_date))
		return "Bad date";
	// is it future date or past date
	if ($now > $unix_date)
	{
		$difference = $now - $unix_date;
		$tense = "ago";
	}
	else
	{
		$difference = $unix_date - $now;
		$tense = "from now";
	}
	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++)
		$difference /= $lengths[$j];

	$difference = round($difference);
	if ($difference != 1)
		$periods[$j].= "s";
	if ($difference == 0)
		return "just now";

	return "$difference $periods[$j] {$tense}";
}

?>