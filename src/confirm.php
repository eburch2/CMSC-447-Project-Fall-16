<?php
session_start();
require("CommonMethods.php");

$umbcid = "";
$randomConfirm = "";
$errorMessage = "";

if(!empty($_GET["id1"])) {
	$umbcid = htmlspecialchars(strtoupper($_GET["id1"]));
	// regular expression to check the umbcid
	if(!preg_match("/^[A-Z]{2}[0-9]{5}$/", $umbcid)) {
		$umbcid = "";
	}
}
if(!empty($_GET["id2"])) {
	$randomConfirm = htmlspecialchars(strtolower($_GET["id2"]));
	// regular expression to check if the value are hexidecimal
	if(!preg_match("/^[a-f0-9]+$/", $randomConfirm)) {
		$randomConfirm = "";
	}
}

if($umbcid != "" && $randomConfirm != "") {
	$db1 = new DBConnection;
	// check if username is already taken
	$row = $db1 -> select("SELECT userid FROM users WHERE umbcid = \"" . $umbcid . "\" AND confirmation = \"" . $randomConfirm . "\" AND status = 2 ORDER BY datecreated DESC LIMIT 1");
	if(count($row) > 0) {
		// change the status from 0 (waiting for confirmation) to 10 (active user -- using 10 to make an allowance in case we need to implement other status number)
		$db1 -> query("UPDATE users SET status = 10 WHERE userid = " . $row[0]["userid"]);
		$errorMessage .= "Account Confirmation Complete!";
	}
	$db1 -> close();
}

include("top.php");
?>
<font color="white"></font>
<?php
include("middle.html");
if($errorMessage != "") {
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
	<a href="login.php"><input class="button2" type="button" value="Log in to your Account"/></a>
	<?php
}
else {
	echo "<span class=\"errorMessage\">Account confirmation failed. Please make sure that you have the correct confirmation link.</span><br/><br/>";
}

include("bottom.html");
?>