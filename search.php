<?php session_start(); ?>
<!DOCTYPE html>
    <?php
    // Profile image locations
    $image_dir = "images";
    $upload_dir = "twister/$image_dir";
    require("heading.php");
    require('TwisterDb.php');
    $twisterDb = new TwisterDb();
    $searching = $_GET['search'];
    $search = $twisterDb->GetSearchResults($searching);
    $foundResults = sizeof($search);
    $username = $_SESSION["username"];
    $followingCounter = $twisterDb->GetFollowingCount($username);
    $followingList = $twisterDb->GetFollowingUsernames($username);
    $found = 0;
?>

<html>
<head><title>Twister - Search</title>
<link rel="stylesheet" href="bootstrap.min.css">
<link rel="stylesheet" href="styles.css">
</head>
<body> 

<div class="container">

<form method="post" action="add_follow.php">
<p>Found <?=$foundResults?> results for <strong><?=$searching?></strong>:</p>
<table class="table table-striped"><tbody>
<?php 


    for($i = 0; $i < $foundResults; $i++)
    {
        $person = $search[$i]['username'];
        $pic = "$image_dir/$person.jpg";
        $time = $search[$i]['timestamp'];
		$niceTime = Nicetime("$time");
?>
        <tr>
            <td width="50">
                <img src="<?=$pic?>" alt='profile picture' border='0'width = "100" height = "100">
            </td>
            <td valign='top'>
                <span class='username'><?=$search[$i]["username"]?></span>
                &nbsp;<span class='status'>
                <?php 
                    $twistMessage = $search[$i]["message"];
                    $boldedSearching = "<b style = 'color: red'>$searching</b>";
                    print_r(str_replace($searching, $boldedSearching, $twistMessage));
                ?> </span>
                <br />
                <span class='timestamp'><?=$niceTime?></span>
            </td>
            
            <?php

            $found = 0;

            for($j = 0; $j < $followingCounter; $j++)
            {
                if($person == $followingList[$j]["follows"])
                {
                    $found++;
                }
            }

            if($person != $_SESSION['username'] && $found != 1)
            {
            ?>

            <td>
                <button type="submit" class="btn btn-primary" aria-label="follow" 
                name="follow" value="<?=$person?>">Follow</button>
            </td>
            
            <?php
            }
            else
            {
                echo "<td></td>";	
            }
            echo"</tr>";
    }

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
</tbody></table>
</form>
</div>

</body>
</html>