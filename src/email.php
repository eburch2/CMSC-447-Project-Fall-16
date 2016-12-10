<?php
session_start();
require("CommonMethods.php");

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	
	$user = "";
	$errorMessage = "";
	$msg = "";
	$pr = 0;
	$h = 0;
	if(!empty($_GET["user"])) {
		$user = htmlspecialchars($_GET["user"]);
		// regular expression to check if the username is alphanumeric
		if(!preg_match("/^[A-Za-z0-9]+$/", $user)) {
			$user = "";
			$errorMessage .= "Invalid username format.<br/>\n";
		}
	}
	if(isset($_GET["msg"])) {
		$msg = trim(htmlspecialchars($_GET["msg"]));
	}
	if(isset($_GET["pr"])) {
		$pr = htmlspecialchars($_GET["pr"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-1]$/", $pr)) {
			$pr = 0;
		}
	}
	if(isset($_GET["h"])) {
		$h = htmlspecialchars($_GET["h"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-1]$/", $h)) {
			$h = 0;
		}
	}
	if($h == 1) {
		if(!preg_match("/^[a-f0-9]+$/", $msg)) {
			$msg = "";
		}
		else {
			$msg = hex2bin($msg);
		}
	}
	$db1 = new DBConnection;
	$query1 = "SELECT umbcid FROM users WHERE username = '" . bin2hex($user) . "' AND status >= 10 LIMIT 1";
	$rows = $db1 -> select($query1);
	$db1 -> close();
	
	include("top.php");
	include("middle.html");
	
	if(count($rows[0]) > 0) {
		if($rows[0]["umbcid"] != "" && $pr == 1 && $msg != "") {
			echo "<h2>Sending the email...<br/>";
			$msg = "Message sent by: " . $_SESSION["umbcbazaar_username"] . "\nUMBC ID: " . $_SESSION["umbcbazaar_umbcid"] . "\n\n\n" . $msg;
			$sendEmailURL = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $rows[0]["umbcid"] . "&subject=" . bin2hex("Message by: " . $_SESSION["umbcbazaar_username"]) . "&body=" . bin2hex(str_replace("\n", "<br/>", $msg));
			$emailStatus = curl($sendEmailURL);
			echo "Message Status: " . $emailStatus . "</h2><br/>";
		}
		else {
			?>
			<form action="email.php" method="get" >
				<input type="hidden" name="pr" id="pr" value="1" />
				<input type="hidden" name="user" id="user" value="<?php echo $user; ?>" />
				<table border="0" align="center">
				<tr><td colspan="2">To: <?php echo $user; ?></td></tr>
				<tr>
				<td colspan="2">
				Message<br/>
				<textarea rows="15" cols="45" class="loginTextArea" id="msg" name="msg" title="Please enter your message here" placeholder="Type your message here" required ><?php echo $msg; ?></textarea>
				</td>
				</tr>
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="&nbsp;Submit&nbsp;" /></td>
				</tr>
				</table></form>
			<?php
		}
	}
	else {
		echo "<h2>Invalid entry: username not found</h2></br>";
	}

	
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>