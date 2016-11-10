<?php
session_start();
require("CommonMethods.php");

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	
	$firstname = "";
	$lastname = "";
	$currentPassword = "";
	$newPassword = "";
	$phone = "";
	$alternateEmail = "";
	$pr = 0;
	$errorMessage = "";
	
	if($pr == 0) {
		$db1 = new DBConnection;
		// make sure the user credentials are valid by checking the username and password
		$query1 = "SELECT firstname, lastname, phone, alternateEmail FROM users WHERE umbcid = \"" . $_SESSION["umbcbazaar_umbcid"] . "\" AND userid = " . $_SESSION["umbcbazaar_userid"] . " AND ";
		$query1 .= "username = \"" . bin2hex($_SESSION["umbcbazaar_username"]) . "\" AND password = \"" . bin2hex($_SESSION["umbcbazaar_password"]) . "\" ";
		//echo $query1;
		$row = $db1 -> select($query1);
		if(count($row[0]) > 0) { 
			$firstname = hex2bin($row[0]["firstname"]);
			$lastname = hex2bin($row[0]["lastname"]);
			$phone = hex2bin($row[0]["phone"]);
			$alternateEmail = hex2bin($row[0]["alternateEmail"]);
		}
		else {
			$db1 -> close();
			// the stored session user credentials are invalid
			// redirect to the login page
			session_unset();
			session_destroy();
			session_start();
			header("Location: login.php");
			exit();
		}
		$db1 -> close();
	}
	if(isset($_POST["pr"])) {
		$pr = htmlspecialchars($_POST["pr"]);
		if($pr != 2) {
			$pr = 0;
		}
	}
	if($pr == 2) {
		if(!empty($_POST["firstname"])) {
			$firstname = htmlspecialchars($_POST["firstname"]);
			// regular expression to check the firstname
			if(!preg_match("/^[\w\s\d\.'\-]+$/", $firstname)) {
				$firstname = "";
				$errorMessage .= "Invalid firstname format.<br/>\n";
			}
		}
		if(!empty($_POST["lastname"])) {
			$lastname = htmlspecialchars($_POST["lastname"]);
			// regular expression to check the lastname
			if(!preg_match("/^[\w\s\d\.'\-]+$/", $lastname)) {
				$lastname = "";
				$errorMessage .= "Invalid lastname format.<br/>\n";
			}
		}
		if(!empty($_POST["currentPassword"])) {
			$currentPassword = htmlspecialchars($_POST["currentPassword"]);
			$currentPassword = passwordFormat($currentPassword);
			if($currentPassword == "") {
				$errorMessage .= "Invalid current password format.<br/>\n";
			}
		}
		if($currentPassword != "") {
			// hash the submitted password with the current salt
			$hashPass = encryptData($currentPassword, substr($_SESSION["umbcbazaar_password"], 16, 16));
			if($hashPass != $_SESSION["umbcbazaar_password"]) {
				$currentPassword = "";
				$errorMessage .= "Current password is invalid.<br/>\n";
			}
		}
		if(!empty($_POST["password"])) {
			$newPassword = htmlspecialchars($_POST["password"]);
			if($newPassword != htmlspecialchars($_POST["password1"])) {
				// the password and confirm password fields must match
				$errorMessage .= "New password does not match.<br/>\n";
				$newPassword = "";
			}
			if($newPassword != "") {
				$newPassword = passwordFormat($newPassword);
				if($newPassword == "") {
					$newPassword .= "Invalid new password format.<br/>\n";
				}
			}
		}
		if($newPassword != "" && encryptData($newPassword, substr($_SESSION["umbcbazaar_password"], 16, 16)) == $_SESSION["umbcbazaar_password"]) {
			$errorMessage .= "New password is the same as the current password.<br/>\n";
		}
		if(!empty($_POST["alternateEmail"])) {
			$alternateEmail = htmlspecialchars($_POST["alternateEmail"]);
			if(!preg_match("/^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+/", $alternateEmail)) {
				// check if the email matches the pattern
				//$alternateEmail = "";
				$errorMessage .= "Invalid email format.<br/>\n";
			}
		}
		else {
			$alternateEmail = "";
		}
		if(!empty($_POST["phone"])) {
			$phone = htmlspecialchars($_POST["phone"]);
			if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
				// check if the phone matches the pattern
				//$phone = "";
				$errorMessage .= "Invalid phone number format</br/>\n";
			}
		}
		else {
			$phone = "";
		}
		// update the user information
		if($firstname != "" && $lastname != "" && $errorMessage == "") {
			$db1 = new DBConnection;
			$passwordChange = false;
			$query1 = "UPDATE users set firstname = \"" . bin2hex($firstname) . "\", lastname = \"" . bin2hex($lastname) . "\", lastaccess = SYSDATE()";
			$query1 .= ", phone = \"" . bin2hex($phone) . "\"";
			$query1 .= ", alternateEmail = \"" . bin2hex($alternateEmail) . "\"";
			if($currentPassword != "" && $newPassword != "") {
				$hashPass = hashPassword($newPassword);
				$query1 .= ", password = \"" . bin2hex($hashPass) . "\"";
				$_SESSION["umbcbazaar_password"] = $hashPass;
				$passwordChange = true;
			}
			$query1 .= " WHERE umbcid = \"" . $_SESSION["umbcbazaar_umbcid"] . "\" AND userid = " . $_SESSION["umbcbazaar_userid"];
			$db1 -> query($query1);
			$_SESSION["umbcbazaar_firstname"] = $firstname;
			$_SESSION["umbcbazaar_lastname"] = $lastname;
			$db1 -> close();
			$errorMessage = "User Profile Updated!";
			
			// if the password was changed then send a confirmation email
			if($passwordChange == true) {
				$emailBody = "User profile has been updated.<br/>\n This is a confirmation that your password has been changed on " . date('Y-m-d H:i:s') . ". <br/>\n";
				$emailBody .= "If you did not make these changes, then go to the main login page and reset your password.<br/>\n";
				$sendEmailURL = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $_SESSION["umbcbazaar_umbcid"] . "&subject=" . bin2hex("Password Changed") . "&body=" . bin2hex($emailBody);
				if($alternateEmail != "") {
					$sendEmailURL .= "&altEmail=" . bin2hex($alternateEmail);
				}
				//echo $sendEmailURL . "<br/>\n";
				$emailStatus = curl($sendEmailURL);
			}
		}
		
	}
	
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";	
	?>
			Account Profile<br/><br/>
			<form action="profile.php" onsubmit="return submitCheck2()" method="post" >
				<input type="hidden" name="pr" id="pr" value="2" />
				<table border=0 align="center">
				<tr>
				<td>Firstname</td><td><input class="loginInput" type="text" id="firstname" name="firstname" title="Please enter your firstname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="<?php echo $firstname; ?>" placeholder="First name" maxlength="50" pattern="^[\w][\-\s\w\d\.']*" required /></td>
				</tr>
				<tr>
				<td>Lastname</td><td><input class="loginInput" type="text" id="lastname" name="lastname" title="Please enter your lastname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="<?php echo $lastname; ?>" placeholder="Last name" maxlength="50" pattern="^[\w][-\s\w\d\.']*" required /></td>
				</tr>
				<tr>
				<td>Alternate Email</td><td><input class="loginInput" type="text" id="alternateEmail" name="alternateEmail" title="Optional alternate email." value="<?php echo $alternateEmail; ?>" placeholder="Optional alternate email" maxlength="50" pattern="^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+" /></td>
				</tr>
				<tr>
				<td>Phone</td><td><input class="loginInput" type="text" id="phone" name="phone" title="Optional phone number. It is a 10 digit number separated by dashes in this format XXX-XXX-XXXX." value="<?php echo $phone; ?>" placeholder="Optional phone number" maxlength="12" pattern="^[0-9]{3}-[0-9]{3}-[0-9]{4}" /></td>
				</tr>
				<tr>
				<td colspan="2"><br/>Change Account Password</td>
				</tr>
				<tr>
				<td>Current Password</td><td><input class="loginInput" type="password" id="currentPassword" name="currentPassword" title="Please enter current your password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Password at least 10 characters long" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$"  /></td>
				</tr>
				<tr>
				<td>New Password</td><td><input class="loginInput" type="password" id="password" name="password" title="Please enter your new password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Password at least 10 characters long" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$"  /></td>
				</tr>
				<tr>
				<td>Re-enter New Password</td><td><input class="loginInput" type="password" id="password1" name="password1" title="Please reenter your new password. At least 10 characters long. Must have at least one uppercase, lowercase, number and !@#$%" value="" placeholder="Re-enter password" maxlength="50" pattern="^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$" /></td>
				</tr>
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="Update Profile" /></td>
				</tr>
				</table>
			</form>
			<script>
				document.querySelector("#phone").addEventListener("input", formatPhone);
				document.querySelector("#alternateEmail").addEventListener("input", formatEmail);
				document.querySelector("#firstname").addEventListener("input", formatName);
				document.querySelector("#lastname").addEventListener("input", formatName);
				
				function submitCheck2() {
					var message = "";
					var returnValue = true;
					var altEmail = document.querySelector("#alternateEmail").value;
					if(altEmail != "") {
						if(!altEmail.match(/^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+$/)) {
							message += "Invalid alternate email. \n";
							returnValue = false;
						}
					}
					var phone = document.querySelector("#phone").value;
					if(phone != "") {
						if(!phone.match(/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/)) {
							message += "Invalid phone number. \n";
							returnValue = false;
						}
					}
					var curPass = document.querySelector("#currentPassword").value;
					if(curPass != "") {
						if(!curPass.match(/^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$/)) {
							message += "Invalid current password. Must be alphanumeric only and these special characters !@#$%\n";
							returnValue = false;
						}
					}
					if(document.querySelector("#password").value != document.querySelector("#password1").value) {
						message += "New password does not match.\n";
						returnValue = false;
					}
					var newPass = document.querySelector("#password").value;
					if(newPass != "") {
						if(!newPass.match(/^(?=.*?[A-Z])(?=(.*[a-z]){1,})(?=(.*[\d]){1,})(?=(.*[!@#\$%]){1,})(?!.*\s).{10,50}$/)) {
							message += "Invalid current password. Must be alphanumeric only and these special characters !@#$%\n";
							returnValue = false;
						}
					}
					if((curPass != "" || newPass != "") && (curPass == "" || newPass == "")) {
						message = "User must enter their current and new passwords. \n";
						returnValue = false;
					}
		
					if(returnValue == false) {
						alert(message);
					}
					return returnValue;
				}
			</script>
			
			<br/><br/>
			
	<?php
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>