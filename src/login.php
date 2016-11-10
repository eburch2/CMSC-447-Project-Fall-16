<?php

session_start();
require("CommonMethods.php");

$userLoggedIn = false;
$failedLogin = false;
$errorMessage = "";
// check the session if there is already a user logged in
// check if the session is expired (no activity within 30 minutes) and retrieve user info stored in the session
$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$userLoggedIn = true;
}
else {
	// create an instance of the database connection class
	$db1 = new DBConnection;
	$username = "";
	$password=  "";
	$password_min = 10;
	$password_max = 50;
	$attempts = 0;
	$maxAttempt = 15;
	
	// get the username from the HttpRequest
	if(!empty($_POST["username"])) {
		$username = htmlspecialchars($_POST["username"]);
		// regular expression to check if the username is alphanumeric
		if(!preg_match("/^[A-Za-z0-9]+$/", $username)) {
			$username = "";
			$errorMessage .= "Invalid username format.<br/>\n";
		}
	}
	if(!empty($_POST["password"])) {
		$password = htmlspecialchars($_POST["password"]);
		// password must bet at between 10 and 50 characters long
		if(!(strlen($password) >= $password_min && strlen($password) <= $password_max)) {
			$password = "";
		}
		if(!preg_match("/.*[A-Z]+.*/", $password)) {
			// must contain at least 1 uppercase
			$password = "";
		}
		if(!preg_match("/.*[a-z]+.*/", $password)) {
			// must contain at least 1 lowercase
			$password = "";
		}
		if(!preg_match("/.*[0-9]+.*/", $password)) {
			// must contain at least 1 number
			$password = "";
		}
		if(!preg_match("/.*[!@#\$%]+.*/", $password)) {
			// must conain at least 1 of these characters
			$password = "";
		}
		if($password == "") {
			$errorMessage .= "Invalid password format.<br/>\n";
		}
	}
	// get the ip address of the user
	// login is only allowed up to 15 attempts
	$rows = $db1 -> select("SELECT attempt FROM failedLogin WHERE ip = '" . $_SERVER["REMOTE_ADDR"] . "' LIMIT 1");
	if(count($rows[0]) > 0) {
		$attempts = $rows[0]["attempt"];
		if($attempts > $maxAttempt) {
			$failedLogin = true;
			$errorMessage .= $_SERVER["REMOTE_ADDR"] . " exceeded login attempts.<br/>Reset your password to reattempt.<br/>\n";
		}
	}
	// only do database check if a proper username and password were submitted
	if($username != "" && $password != "" && $attempts <= $maxAttempt) {
		// data in the database is stored as hex to prevent SQL injection
		$rows = $db1 -> select("SELECT userid, umbcid, firstname, lastname, password FROM users WHERE username = \"" . bin2hex($username). "\" AND status >= 10 LIMIT 1");
		if(count($rows[0]) > 0) {
			$currentHashPass = hex2bin($rows[0]["password"]);
			$currentSalt = substr($currentHashPass, 16, 16);
			// check if the hash of the entered password matches the current hash password
			if(encryptData($password, $currentSalt) == $currentHashPass) {
				// login is successful -- add another layer of security
				// update the stored hash password with a new salt
				$newHashPass = hashPassword($password);
				// update the password and lastaccess for the user and reset the attempt counter
				$db1 -> query("UPDATE users SET password = \"" . bin2hex($newHashPass) . "\", lastaccess = SYSDATE() WHERE userid = " . $rows[0]["userid"]);
				$query1 = "INSERT INTO failedLogin (ip, attempt) VALUES ('" . $_SERVER["REMOTE_ADDR"] . "', 0) ON DUPLICATE KEY UPDATE attempt = 0 ";
				mysql_query($query1, $db1 -> $connection);
				// save the user info in the session
				$userLoggedIn = true;
				$_SESSION["umbcbazaar_userid"] = $rows[0]["userid"];
				$_SESSION["umbcbazaar_username"] = $username;
				$_SESSION["umbcbazaar_password"] = $newHashPass;
				$_SESSION["umbcbazaar_umbcid"] = $rows[0]["umbcid"];
				$_SESSION["umbcbazaar_firstname"] = hex2bin($rows[0]["firstname"]);
				$_SESSION["umbcbazaar_lastname"] = hex2bin($rows[0]["lastname"]);
				$_SESSION["LAST_ACTIVITY"] = time();
			}
		}
		if($userLoggedIn == false) {
			$failedLogin = true;
			$errorMessage .= "Invalid username/password.<br/>\n";
			// update the attempt counter -- stored in the database instead of session to prevent botters
			$query1 = "INSERT INTO failedLogin (ip, attempt) VALUES ('" . $_SERVER["REMOTE_ADDR"] . "', " . ($attempts + 1) . ") ON DUPLICATE KEY UPDATE attempt = " . ($attempts + 1) . " ";
			mysql_query($query1, $db1 -> $connection);
		}
	}
	// close the database connection
	$db1 -> close();
}

if($userLoggedIn == true) {
	// the user is already logged in, so redirect to the class selection page
	header("Location: index.php");
	exit();
}
else {
	include("top.php");
	include("middle.html");
	if($failedLogin == true) {
		echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	}
	?>
	Existing User: Enter login information<br/><br/>
	<form action="login.php" method="post" >
	<table border=0 align="center">
		<tr>
			<td>Username</td><td><input class="loginInput" type="text" id="username" name="username" title="Please enter your firstname. Only alphanumeric are allowed. Must be at least 7 characters long" value="<?php if(!empty($_POST["username"])){ echo $_POST["username"]; } ?>" placeholder="Username at least 7 characters long" maxlength="50" pattern="^[A-Za-z0-9]{7,50}" autofocus required /></td>
		</tr>
		<tr>
			<td>Password</td><td><input class="loginInput" type="password" id="password" name="password" title="Please enter your password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Password at least 10 characters long" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$" required /></td>
		</tr>
		<tr>
			<td colspan="2">
			<br/>
			<input class="button1" type="submit" value="Log in" />
			<br/><br/>
			<a href="resetpass.php">Forgot your password?</a>
			</td>
		</tr>
	</table>
	</form>
	<br/><br/>
	With a UMBC Bazaar account, you'll be able to buy, sell or trade<br/>items and services within the UMBC community.<br/><br/>
	<a href="register.php"><input class="button1" type="button" value="&nbsp;Create a New Account&nbsp;"/></a>
	
	<script>
		document.querySelector("#username").addEventListener("input", formatUsername);
	</script>

	<?php
	include("bottom.html");
}
?>