<?php
session_start();
require("CommonMethods.php");


function getLastOrderID($results) {
	if($result === false) {
         return $rows;
    }
	if (mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			array_push($rows, $row);
		}
		return $rows[0];
	}
	return array();
}

// accept either GET or POST HttpRequest
if($_POST) {
	$_GET = $_POST;
}
$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$usertradeid = "";
	$tradeid = "";
	$pr = 0;
	$userid = $_SESSION["umbcbazaar_userid"];
	$description = "";
	$hours = 0.0;
	$itemvalue = 0.0;
	$errorMessage = "";
	$_SESSION["umbcbazaar_addTradeOptionMessage"] = ""; 
	$tradelist = array();
	
	if(isset($_GET["usertradeid"])) {
		$usertradeid = htmlspecialchars($_GET["usertradeid"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-9]+$/", $usertradeid)) {
			$usertradeid = "";
			$errorMessage .= "Invalid usertradeid format.<br/>\n";
		}
	}
	if(isset($_GET["pr"])) {
		$pr = htmlspecialchars($_GET["pr"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-2]$/", $pr)) {
			$pr = 0;
		}
	}
	$db1 = new DBConnection;
	// all trade options
	
	$rows = $db1 -> select("SELECT tradeid, name, onetime_hourly FROM trades ");
	foreach($rows as $row) {
		array_push($tradelist, array($row["tradeid"], $row["name"], $row["onetime_hourly"]));
	}
	
	if($usertradeid != "" && $pr == 1) {
		// logical deleting a trade option
		$row = $db1 -> select("SELECT usertradeid FROM usertrade WHERE usertradeid = " . $usertradeid . " AND userid = " . $userid . " AND logicaldelete >= 1 LIMIT 1");
		if(count($row[0]) > 0) {
			// change to logicaldelete value
			$db1 -> query("UPDATE usertrade SET logicaldelete = 0, lastaccess = SYSDATE() WHERE usertradeid = " . $usertradeid);
			$errorMessage .= "Trade option " . $usertradeid . " deleted.<br/>\n";
		}
		else {
			$errorMessage .= "Usertradeid #" . $usertradeid . " not found.<br/>\n";
		}
		$db1 -> close();
		$_SESSION["umbcbazaar_addTradeOptionMessage"] = $errorMessage;
		header("Location: listtrade.php");
		exit();
	}
	elseif($pr == 2) {
		// add the trade option to the database
		if(isset($_GET["description"])) {
			$description = trim(htmlspecialchars($_GET["description"]));
		}
		if(isset($_GET["hours"])) {
			$hours = htmlspecialchars($_GET["hours"]);
			// regular expression to check if it is a float
			if(!preg_match("/^[0-9]*\.?[0-9]+$/", $hours)) {
				$hours = 0.0;
			}
		}
		if(isset($_GET["itemvalue"])) {
			$itemvalue = htmlspecialchars($_GET["itemvalue"]);
			// regular expression to check if it is a float
			if(!preg_match("/^[0-9]*\.?[0-9]+$/", $itemvalue)) {
				$itemvalue = 0.0;
			}
		}
		if(isset($_GET["tradeid"])) {
			$tradeid = htmlspecialchars($_GET["tradeid"]);
			// regular expression to check if it is a number
			if(!preg_match("/^[0-9]+$/", $tradeid)) {
				$tradeid = 0;
			}
		}
		// check if it is a valid tradeid and if it requires hour criteria
		$hoursNeeded = false;
		$validTradeID = false;
		foreach($tradelist as $listing1) {
			if ($listing1[0] == $tradeid) {
				$validTradeID = true;
				if($listing1[2] == 0) {
					$hoursNeeded = true;
					$itemvalue = 0;
				}
				else {
					$hoursNeeded = false;
					$hours = 0;
				}
				break;
			}
		}
		if($validTradeID == true && ( ($hoursNeeded == true && $hours > 0) || ($hoursNeeded == false && $itemvalue > 0) )) {
			$query1 = "";
			// prepare an insert statement
			$query1 .= "INSERT INTO usertrade (tradeid, userid, description, hours, itemvalue)";
			$query1 .= " VALUES (" . $tradeid . ", " . $userid . ", \"" . bin2hex($description) . "\", " . $hours . ", " . $itemvalue . ")";
			$db1 -> query($query1);
			$results =  mysql_insert_id($db1 -> $connection);
			$db1 -> close();
			$_SESSION["umbcbazaar_addTradeOptionMessage"] = "UserTrade ID # " . $results . "    option added."; 
			// redirect to the seller's product page
			header("Location: listtrade.php");
			exit();
		}
		else {
			$errorMessage .= "Must be a valid tradeid with corresponding hour or item value criteria.<br/>\n";
		}
	}
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>

			Adding a Trade Option<br/><br/>
			<a href="listtrade.php"><input class="button1" type="button" value="&nbsp;&nbsp;Go Back to Trade Option List&nbsp;&nbsp;"/></a>
			<br/><br/>
			<form action="tradeoptions.php" method="post" onsubmit="return checkTrade()" >
				<input type="hidden" name="pr" id="pr" value="2" />
				<table border="0" align="center">
				<tr>
				<td>Trade Option</td><td><select class="loginInput" id="tradeid" name="tradeid" title="Select trade option" maxlength="150" required autofocus >
				<option> </option>
				<?php
				foreach ($tradelist as $listing1) {
					$displayOut = "<option value='" . $listing1[0] . "' ";
					if($tradeid == $listing1[0]) {
						$displayOut .= "selected ";
					}
					$displayOut .= ">" . $listing1[1] . "</option>\n";
					echo $displayOut;
				}
				?>
				</select>
				</td>
				</tr>
				<tr>
				<td>Item Value</td><td><input class="inputStyle" type="text" id="itemvalue" name="itemvalue" width="20" title="Please enter a non-negative numeric value for item value if the trade option requires it." value="<?php echo $itemvalue; ?>" placeholder="Enter item value" maxlength="50" pattern="^[0-9]*\.?[0-9]+" required /></td>
				</tr>
				<tr>
				<td>Hours</td><td><input class="inputStyle" type="text" id="hours" name="hours" width="20" title="Please enter a non-negative numeric value for hours if the trade option requires it." value="<?php echo $hours; ?>" placeholder="Enter hours" maxlength="50" pattern="^[0-9]*\.?[0-9]+" required /></td>
				</tr>
				<tr>
				<td colspan="2">
				Description<br/>
				<textarea rows="15" cols="45" class="loginTextArea" id="description" name="description" title="Please enter your trade option description" placeholder="Type your description here" required ><?php echo $description; ?></textarea>
				</td>
				</tr>
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="&nbsp;Submit&nbsp;" /></td>
				</tr>
				</table>
			</form>
			<script>
				document.querySelector("#hours").addEventListener("input", formatDouble);
				document.querySelector("#itemvalue").addEventListener("input", formatDouble);
				function checkTrade() {
					var message = "";
					var returnValue = true;
					var tradelist = [];
					<?php
					foreach($tradelist as $listing1) {
						$msg1 = "tradelist[\"" . $listing1[0] . "\"] = " . $listing1[2] . ";\n";
						echo $msg1;
					}
					?>
					var tradeid = document.querySelector("#tradeid").value;
					var hours = document.querySelector("#hours").value;
					var itemvalue = document.querySelector("#itemvalue").value;
					if(tradeid <= 0) {
						message += "Must specify a trade option\n";
						returnValue = false;
					}
					if(tradelist[tradeid] == 0 && hours <= 0) {
						message += "Must specify hours criteria.\n";
						returnValue = false;
					}
					if(tradelist[tradeid] == 1 && itemvalue <= 0) {
						message += "Must specify item value criteria.\n";
						returnValue = false;
					}
					if(returnValue == false) {
						alert(message);
					}
					return returnValue;
				}
			</script>
	<?php
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>