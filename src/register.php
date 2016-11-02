<?php

session_start();
require("CommonMethods.php");
require("DBConnection.php");
require("UMBCDirectory.php");

$errorMessage = "";
$displayForm = true;
// check the session if there is already a user logged in
// check if the session is expired (no activity within 30 minutes) and retrieve user info stored in the session
$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	// the user is already logged in, they cannot register an account without signing out
	// redirect to the main page
	header("Location: index.php");
	exit();
}
else {
	$umbcid = "";
	$username = "";
	$username_min = 7;
	$username_max = 50;
	$password = "";
	$password_min = 10;
	$password_max = 50;
	$firstname = "";
	$lastname = "";
	$captcha = "";

	if(!empty($_POST["umbcid"])) {
		$umbcid = htmlspecialchars(strtoupper($_POST["umbcid"]));
		// regular expression to check the umbcid
		if(!preg_match("/^[A-Z]{2}[0-9]{5}$/", $umbcid)) {
			$umbcid = "";
		}
	}
	if(!empty($_POST["username"])) {
		$username = htmlspecialchars($_POST["username"]);
		// username must bet at between 7 and 50 characters long
		if(!(strlen($username) >= $username_min && strlen($username) <= $username_max)) {
			$username = "";
		}
		// regular expression to check if the username is alphanumeric
		if(!preg_match("/^[A-Za-z0-9]+$/", $username)) {
			$username = "";
		}
	}
	if(!empty($_POST["password"])) {
		$password = htmlspecialchars($_POST["password"]);
		if($password != htmlspecialchars($_POST["password1"])) {
			// the password and confirm password fields must match
			$password = "";
		}
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
	}
	if(!empty($_POST["firstname"])) {
		$firstname = htmlspecialchars($_POST["firstname"]);
		// regular expression to check the firstname
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $firstname)) {
			$firstname = "";
		}
	}
	if(!empty($_POST["lastname"])) {
		$lastname = htmlspecialchars($_POST["lastname"]);
		// regular expression to check the lastname
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $lastname)) {
			$lastname = "";
		}
	}
	if(!empty($_POST["captcha"])) {
		$captcha = htmlspecialchars($_POST["captcha"]);
		// regular expression to check the lastname
		if($captcha != $_SESSION["umbcbazaar_captcha"]) {
			$captcha = "";
		}
	}
	// check if format-wise, the variables are valid
	if($umbcid != "" && $username != "" && $password != "" && $firstname != "" && $lastname != "" && $captcha != "") {
		// check if the UMBC ID is actually valid using the UMBC directory
		if(count(getStudentInfo($umbcid)) > 0) {
			$db1 = new DBConnection;
			// check if username is already taken
			$row = $db1 -> select("SELECT COUNT(*) FROM users WHERE username = \"" . bin2hex($username) . "\" ");
			if($row[0]["COUNT(*)"] > 0) {
				$errorMessage .= "Username already taken.";
			}
			else {
				// check if the umbc id is already associated with another account
				$row = $db1 -> select("SELECT COUNT(*) FROM users WHERE umbcid = \"" . $umbcid . "\" ");
				if($row[0]["COUNT(*)"] > 0) {
					$errorMessage .= "UMBC ID already associated with an account.";
				}
				else {
					// generate a random confirmation number
					$randomConfirm = "";
					for($i=0; $i < 75; $i++) {
						$randomConfirm .= dechex(rand(0,15));
					}
					$hashPass = hashPassword($password);
					// insert the record into the user database
					$query1 = "INSERT INTO users (umbcid, username, password, firstname, lastname, confirmation, status, type, lastaccess, datecreated) ";
					$query1 .= "VALUES ('" . $umbcid . "', '". bin2hex($username) ."', '" . bin2hex($hashPass) . "', '" . bin2hex($firstname) . "', '"  . bin2hex($lastname) . "', ";
					$query1 .= "'" . $randomConfirm . "', 0, 1, SYSDATE(), SYSDATE()) ";
					//echo $query1;
					mysql_query($query1, $db1 -> $connection);
					// $db1 -> query($query1);
					
					$emailBody = "Use the link below to confirm your account. <br/>\n\n";
					$url = "http://userpages.umbc.edu/~jguansi1/CMSC447/confirm.php?id1=" . $umbcid . "&id2=" . $randomConfirm;
					$emailBody .= "<a href='" . $url . "'>" . $url . "</a> <br/>\n\n";
					
					
					$sendEmailURL = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $umbcid . "&subject=" . bin2hex("Account Confirmation") . "&body=" . bin2hex($emailBody);
					$emailStatus = curl($sendEmailURL);
					//echo $emailStatus;
					$errorMessage = "A confirmation email has been sent to your UMBC email address. Please check your email and follow the directions on how to activate your account.";
					$displayForm = false;
				}
			}
			$db1 -> close();
		}
	}
	
	
}

include("top.html");
?>
<font color="white"></font>
<?php
include("middle.html");
echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
if($displayForm == true) {
?>
			Please enter your student information.<br/><br/>
			<form action="register.php" onsubmit="return submitCheck()" method="post" >
				<table border=0 align="center">
				<tr>
				<td>UMBC ID</td><td><input class="loginInput" type="text" id="umbcid" name="umbcid" title="Please enter your student ID. The format needs to be 2 characters followed by 5 digits (AAXXXXX)."  value="" placeholder="AAXXXXX   A=char X=digit" maxlength="7" pattern="[A-Za-z]{2}[0-9]{5}" required autofocus /></td>
				</tr>
				<tr>
				<td>Username</td><td><input class="loginInput" type="text" id="username" name="username" title="Please enter your firstname. Only alphanumeric are allowed. Must be at least 7 characters long" value="" placeholder="Username at least 7 characters long" maxlength="50" pattern="^[A-Za-z0-9]{7,50}" required /></td>
				</tr>
				<tr>
				<td>Password</td><td><input class="loginInput" type="password" id="password" name="password" title="Please enter your password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Password at least 10 characters long" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$" required /></td>
				</tr>
				<tr>
				<td>Re-enter Password</td><td><input class="loginInput" type="password" id="password1" name="password1" title="Please enter your password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Re-enter password" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$" required /></td>
				</tr>
				<tr>
				<td>Firstname</td><td><input class="loginInput" type="text" id="firstname" name="firstname" title="Please enter your firstname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="" placeholder=">> Disabled << Enter a valid UMBC ID" maxlength="50" pattern="^[\w][\-\s\w\d\.']*" disabled required /></td>
				</tr>
				<tr>
				<td>Lastname</td><td><input class="loginInput" type="text" id="lastname" name="lastname" title="Please enter your lastname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="" placeholder=">> Disabled << Enter a valid UMBC ID" maxlength="50" pattern="^[\w][-\s\w\d\.']*" disabled required /></td>
				</tr>
				<tr>
				<td>Captcha</td>
				<td><img src="captcha.php" width="240" height="50" /><br/><input class="loginInput" type="text" id="captcha" name="captcha" width="100" title="Please enter the text from the image above." placeholder="Match the text in the image" value="" required /></td>
				</tr>
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="Register" /></td>
				</tr>
				</table>
			</form>
			<script>
				document.querySelector("#umbcid").addEventListener("input", getStudentInfo);
				document.querySelector("#username").addEventListener("input", formatUsername);
				document.querySelector("#firstname").addEventListener("input", formatName);
				document.querySelector("#lastname").addEventListener("input", formatName);
			</script>
<?php
}
else {
	?>
	<a href="login.php"><input class="button2" type="button" value="Log in to your Account"/></a>
	<?php
}
include("bottom.html");
?>