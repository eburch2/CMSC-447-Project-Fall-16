<?php
session_start();
require("CommonMethods.php");

$errorMessage = "";
$msg = "";
$pr = 0;
$captcha = "";

$sendTo = "YR77978";


if(isset($_POST["msg"])) {
	$msg = trim(htmlspecialchars($_POST["msg"]));
	if($subject == "") {
		$errorMessage .= "Please enter a message.<br/>\n";
	}
}
if(isset($_POST["pr"])) {
	$pr = htmlspecialchars($_POST["pr"]);
	// regular expression to check if it is a number
	if(!preg_match("/^[0-1]$/", $pr)) {
		$pr = 0;
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
include("top.php");
include("middle.html");

if($pr == 1 && $_SESSION["umbcbazaar_captcha"] != "" && $captcha == $_SESSION["umbcbazaar_captcha"] && $msg != "") {
	$db1 = new DBConnection;
	$query1 = "SELECT umbcid FROM users WHERE type >= 10 AND status >= 10 ORDER BY lastaccess DESC LIMIT 2";
	$rows = $db1 -> select($query1);
	$db1 -> close();
	echo "<h2>Sending the email...<br/>";
	foreach($rows as $row) {
		$sendEmailURL = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $row["umbcid"] . "&subject=" . bin2hex("Contact Us Message") . "&body=" . bin2hex(str_replace("\n", "<br/>", $msg));
		$emailStatus = curl($sendEmailURL);
	}
	echo "Your message has been sent. Thank you for contacting us.</h2><br/>";
}	
else {
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
	Please use the contact form below to get in touch with us. Thanks!<br/><br/>
	<form action="contactus.php" method="post" >
		<input type="hidden" name="pr" id="pr" value="1" />
		<table border="0" align="center">
		<td colspan="2">
		Message<br/>
		<textarea rows="15" cols="45" class="loginTextArea" id="msg" name="msg" title="Please enter your message here" placeholder="Type your message here" required ><?php echo $msg; ?></textarea>
		</td>
		</tr>
		<tr>
		<td>Captcha</td>
		<td><img src="captcha.php" width="240" height="50" /><br/><input class="loginInput" type="text" id="captcha" name="captcha" width="100" title="Please enter the text from the image above." placeholder="Match the text in the image" value="" required /></td>
		</tr>
		<tr>
		<td colspan="2"><br/><input class="button1" type="submit" value="&nbsp;Submit&nbsp;" /></td>
		</tr>
		</table></form>
	<?php
}
	
include("bottom.html");

?>