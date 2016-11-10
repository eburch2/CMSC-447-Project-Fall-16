<?php
session_start();
require("CommonMethods.php");
require("UMBCDirectory.php");

$errorMessage = "";
$successful = false;
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
	$captcha = "";

	if(!empty($_POST["umbcid"])) {
		$umbcid = htmlspecialchars(strtoupper($_POST["umbcid"]));
		// regular expression to check the umbcid
		if(!preg_match("/^[A-Z]{2}[0-9]{5}$/", $umbcid)) {
			$umbcid = "";
			$errorMessage .= "Invalid UMBC ID format.<br/>\n";
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
		if($username == "") {
			$errorMessage .= "Invalid username format.<br/>\n";
		}
	}
	if(!empty($_POST["captcha"])) {
		$captcha = htmlspecialchars($_POST["captcha"]);
		// regular expression to check the lastname
		if($captcha != $_SESSION["umbcbazaar_captcha"]) {
			$captcha = "";
			$errorMessage .= "Invalid captcha.<br/>\n";
		}
	}
	if($umbcid != "" && $username != "" && $captcha != "") {
		$db1 = new DBConnection;
		// check if username is already taken
		$row = $db1 -> select("SELECT COUNT(*) FROM users WHERE username = \"" . bin2hex($username) . "\" AND umbcid = \"" . $umbcid . "\" AND status > 1 LIMIT 1 ");
		if($row[0]["COUNT(*)"] > 0) {
			$newPassword = "";
			$options = "ABCDEFGHIKJLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%";
			for($i=0; $i < 30; $i++) {
				$newPassword .= $options[mt_rand(0, strlen($options)-1)];
			}
			if(!preg_match("/.*[!@#\$%].*$/", $newPassword)) {
				$newPassword .= $options[mt_rand(62, strlen($options)-1)];
			}
			if(!preg_match("/.*[0-9].*$/", $newPassword)) {
				$newPassword .= chr(mt_rand(48, 57));
			}
			if(!preg_match("/.*[A-Z].*$/", $newPassword)) {
				$newPassword .= chr(mt_rand(65, 90));
			}
			if(!preg_match("/.*[a-z].*$/", $newPassword)) {
				$newPassword .= chr(mt_rand(97, 122));
			}
			//echo $newPassword;
			$hashPass = hashPassword($newPassword);
			$db1 -> query("UPDATE users SET password = '" . bin2hex($hashPass) . "' WHERE umbcid = '" . $umbcid . "' AND username = '" . bin2hex($username) . "' ");
			
			$emailBody = "Your password has been reset. Your new password is listed below:<br/>\n\n";
			$emailBody .= $newPassword;
			$sendEmailURL = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $umbcid . "&subject=" . bin2hex("Password Reset") . "&body=" . bin2hex($emailBody);
			$emailStatus = curl($sendEmailURL);
			$errorMessage .= "Password has been reset! Please check your email for further instructions.";
			$successful = true;
		}
		else {
			$errorMessage .= "Password reset failed! Please make sure you entered the correct UMBC ID and username for an active account.";
		}
		$db1 -> close();
	}
}

include("top.php");
?>
<font color="white"></font>
<?php
include("middle.html");
if($successful == true) {
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
	<a href="login.php"><input class="button2" type="button" value="Log in to your Account"/></a>
	<?php
}
else {
	if($errorMessage != "") {
		echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	}
	?>
	Please enter your student information.<br/><br/>
			<form action="resetpass.php"  method="post" >
				<table border=0 align="center">
				<tr>
				<td>UMBC ID</td><td><input class="loginInput" type="text" id="umbcid" name="umbcid" title="Please enter your student ID. The format needs to be 2 characters followed by 5 digits (AAXXXXX)."  value="<?php if(!empty($_POST["umbcid"])){ echo $_POST["umbcid"]; } ?>" placeholder="AAXXXXX   A=char X=digit" maxlength="7" pattern="[A-Za-z]{2}[0-9]{5}" required autofocus /></td>
				</tr>
				<tr>
				<td>Username</td><td><input class="loginInput" type="text" id="username" name="username" title="Please enter your firstname. Only alphanumeric are allowed. Must be at least 7 characters long" value="<?php if(!empty($_POST["username"])){ echo $_POST["username"]; } ?>" placeholder="Username at least 7 characters long" maxlength="50" pattern="^[A-Za-z0-9]{7,50}" required /></td>
				</tr>
				<tr>
				<td>Captcha</td>
				<td><img src="captcha.php" width="240" height="50" /><br/><input class="loginInput" type="text" id="captcha" name="captcha" width="100" title="Please enter the text from the image above." placeholder="Match the text in the image" value="" required /></td>
				</tr>
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="Reset Password" /></td>
				</tr>
				</table>
			</form>
			<script>
				document.querySelector("#umbcid").addEventListener("input", formatUMBCID);
				document.querySelector("#username").addEventListener("input", formatUsername);
			</script>
	<?php
}

include("bottom.html");
?>