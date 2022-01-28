<?php 
/* 
	Authors:
		- Jair Lopez
		- Denilson Lemus
*/

class TwisterDb
{
	private const MYSQL_USERNAME = "dlemus";
	private const MYSQL_PASSWORD = "H01672281";
	private const MYSQL_DATABASE = "dlemus";

	private $mysqli;  
	
	// Constructor
	function __construct()
	{
		// Connect to the database
		$this->mysqli = new mysqli("localhost", self::MYSQL_USERNAME, self::MYSQL_PASSWORD, self::MYSQL_DATABASE);
	}
	
	// Adds the user info to the database and returns true if successful, false if
	// the username already exists in the database
	function AddUserAccount($username, $passwordHash, $about)
	{	
		$sql = "INSERT INTO TwisterUsers VALUES (?, ?, ?)";
		$stmt = $this->mysqli->prepare($sql);
		if ($stmt)
		{
			$stmt->bind_param("sss", $username, $passwordHash, $about);
			if ($stmt->execute())
				return true;
			elseif ($this->mysqli->errno === 1062)
				return false;
		}
		$this->ShowMySqlError($sql);
	}
	
	// Updates user info. Password is not changed if left to an empty string.
	function UpdateUserAccount($username, $passwordHash, $about) {			
		if ($passwordHash == "")
		{
			// No password submitted
			$sql = "UPDATE TwisterUsers SET about = ? WHERE username = ?";
			$stmt = $this->mysqli->prepare($sql);
			$stmt->bind_param("ss", $about, $username);
		}
		else
		{
			$sql = "UPDATE TwisterUsers SET about = ?, password = ? WHERE username = ?";
			$stmt = $this->mysqli->prepare($sql);
			$stmt->bind_param("sss", $about, $passwordHash, $username);
		}
		
		$stmt->execute();
	}

	// Returns number of twists created by a user
	function GetTwistCount($username)
	{	
		$sql = "SELECT COUNT(*) FROM Twists WHERE username = ?";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_array();
		return $row[0];
	}

	// Returns number of people the user is following 
	function GetFollowingCount($username)
	{				
		$sql = "SELECT count(*) FROM Followers WHERE username = ?";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_array();
		return $row[0];
	}

	// Returns number of people following the user
	function GetFollowerCount($username)
	{	
		$sql = "SELECT count(*) FROM Followers WHERE follows = ?";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_array();
		return $row[0];
	}

	// Returns associative array with info about the user, null of the username was not found
	// Example: [ username => "bsmith", password => "password-hash", about => "About me" ]
	function GetUserInfo($username)
	{	
		$sql = "SELECT * FROM TwisterUsers WHERE username = '$username'";
		$result = $this->mysqli->query($sql) or $this->ShowMySqlError($sql);

		if ($result->num_rows == 0)
			return null;
		else
			return $result->fetch_assoc();

	}
	
	// Returns an array of associative arrays of the user's most recent twists. Example:
	//  [0] => [twistid => 1, username => "bsmith", message => "My twist", timestamp = "2019-11-12 12:05:44"]
	//  [1] => [twistid => 3, "username" => "bsmith", message => "Another twist", timestamp = "2019-11-13 15:15:00"]
	//  [2] => [twistid => 6, "username" => "bsmith", message => "Test", timestamp = "2019-11-14 02:15:00"]
	function GetUserTwists($username)
	{
		$sql = "SELECT twistid, username, message, timestamp FROM Twists " .
			"WHERE username = '$username' ORDER BY timestamp DESC LIMIT 0, 20";
		$result = $this->mysqli->query($sql) or $this->ShowMySqlError($sql);
		$results = [];
		while ($row = $result->fetch_assoc())
		{
			array_push($results, $row);
		}
		return $results;
	}
	
	// Returns an array of associative arrays of the user's twists combined with twists
	// from those the user follows. Example:
	//  [0] => [twistid => 1, username => "bsmith", message => "My twist", timestamp = "2019-11-12 12:05:44"]
	//  [1] => [twistid => 2, username => "mcp", message => "Hello, user", timestamp = "2019-11-13 12:26:00"]
	//  [2] => [twistid => 3, username => "bsmith", message => "Another twist", timestamp = "2019-11-13 15:15:00"]
	//  [3] => [twistid => 6, username => "bsmith", message => "Test", timestamp = "2019-11-14 02:15:00"]
	function GetAllTwists($username)
	{		
		$sql = "SELECT twistid, username, message, timestamp FROM Twists WHERE username = '$username' UNION " .
				"SELECT twistid, username, message, timestamp from Twists WHERE username IN " .
				"(SELECT follows FROM Followers WHERE username = '$username') ORDER BY timestamp DESC LIMIT 0, 20";
	    $result = $this->mysqli->query($sql) or $this->ShowMySqlError($sql);
		$results = [];
		while ($row = $result->fetch_assoc())
		{
			array_push($results, $row);
		}
		return $results; 
	}
	
	// Returns associative array of key = usernames, value = 1 of people followed by user
	// Example: [ "jwhite" => 1, "mcp" => 1 ]
	function GetFollowingUsernames($username)	
	{
		//SELECT follows FROM Followers WHERE username = $username;
		$sql = "SELECT follows FROM Followers WHERE username = '$username'";
		$result = $this->mysqli->query($sql) or $this->ShowMySqlError($sql);

		$results = [];
		for($i = 0; $i < $this->GetFollowingCount($username); $i++)
		{
			$results[] = $result->fetch_assoc();
		}

		return $results;
	}
	
	// Returns array of associative arrays containing twists that match the search string. Example search for "twist":
	//  [0] => [twistid => 1, username => "bsmith", message => "My twist", timestamp = "2019-11-12 12:05:44"]
	//  [1] => [twistid => 3, username => "bsmith", message => "Another twist", timestamp = "2019-11-13 15:15:00"]
	//  [2] => [twistid => 7, username => "mcp", message => "Twister", timestamp = "2019-11-15 02:15:00"]
	function GetSearchResults($searchStr)
	{
		$sql = "SELECT twistid, username, message, timestamp FROM Twists
				WHERE message LIKE '%$searchStr%' ORDER BY timestamp DESC";
		$result = $this->mysqli->query($sql) or $this->ShowMySqlError($sql);
		$results = [];
		while ($row = $result->fetch_assoc())
		{
			array_push($results, $row);
		}
		return $results;
	}
	
	// Inserts new twist into the database
	function AddTwist($username, $text)
	{
		$sql = "INSERT INTO Twists (username, message, timestamp) VALUES (?, ?, NOW())";
		$stmt = $this->mysqli->prepare($sql);
		if ($stmt)
		{
			$stmt->bind_param("ss",$username, $text);
			if ($stmt->execute())
				return true;
			elseif ($this->mysqli->errno === 1062)
				return false;
		}
		$this->ShowMySqlError($sql);
	}
	
	// Insert new follower into the database
	function AddFollow($username, $followUsername)
	{
		$sql = "INSERT INTO Followers (username, follows) VALUES (?, ?)";
		$stmt = $this->mysqli->prepare($sql);
		if ($stmt)
		{
			$stmt->bind_param("ss",$username, $followUsername);
			if ($stmt->execute())
				return true;
			elseif ($this->mysqli->errno === 1062)
				return false;
		}
		$this->ShowMySqlError($sql);
		
	}
	
	// Remove a follower from the database
	function RemoveFollow($username, $followUsername)
	{
		$sql = "DELETE FROM Followers WHERE username = ? AND follows = ?;";
		$stmt = $this->mysqli->prepare($sql);
		if ($stmt)
		{
			$stmt->bind_param("ss",$username, $followUsername);
			if ($stmt->execute())
				return true;
			elseif ($this->mysqli->errno === 1062)
				return false;
		}
		$this->ShowMySqlError($sql);
	}
	
	// Display detailed MySQL error info for debugging purposes
	function ShowMySqlError($sql)
	{
		die("Error (" . $this->mysqli->errno . ") " . $this->mysqli->error .
			"<br>SQL = $sql\n");
	}
}
?>