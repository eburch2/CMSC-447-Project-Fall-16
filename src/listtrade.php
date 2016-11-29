<?php
session_start();
require("CommonMethods.php");

// accept either GET or POST HttpRequest
if($_POST) {
	$_GET = $_POST;
}

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$userid = $_SESSION["umbcbazaar_userid"];
	$errorMessage = $_SESSION["umbcbazaar_addTradeOptionMessage"];
	$_SESSION["umbcbazaar_addTradeOptionMessage"] = "";

	$db1 = new DBConnection;
	$tradelist = array();
	$rows1 = $db1 -> select("SELECT tradeid, name, onetime_hourly FROM trades ");
	foreach($rows1 as $row) {
		//array_push($tradelist, array($row["tradeid"], $row["name"], $row["onetime_hourly"]));
		$tradelist[ $row["tradeid"] ] = array($row["tradeid"], $row["name"], $row["onetime_hourly"]);
	}
	$rows = $db1 -> select("SELECT usertradeid, tradeid, description, hours, itemvalue FROM usertrade WHERE userid = " . $userid . " AND logicaldelete >= 1 ORDER BY lastaccess DESC");
	
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
			List of Products and Services<br/><br/>
			<a href="tradeoptions.php?pr=0"><input class="button2" type="button" value="&nbsp;&nbsp;Add a New Trade Option&nbsp;&nbsp;"/></a>
			<br/><br/>
			<table border="3" align="center" width="700">
			<tr bgcolor="black" height="50" >
			<td><font color="#ffcc00">Trade Option</font></td>
			<td><font color="#ffcc00">Description</font></td>
			<td><font color="#ffcc00">Hours</font></td>
			<td><font color="#ffcc00">Item Value</font></td>
			<td><font color="#ffcc00">Delete</font></td>
			</tr>
			<?
			$counter = 0;
			foreach($rows as $row) {
				$temp2 = "" . $row["hours"];
				$temp3 = "$" . number_format($row["itemvalue"], 2);
				if($tradelist[$row["tradeid"]][2] == 0) {
					$temp3 = "--";
				}
				else {
					$temp2 = "--";
				}
				echo "<tr><td>" . $tradelist[$row["tradeid"]][1] . "</td><td>" . hex2bin($row["description"]) . "</td><td>" . $temp2 . "</td><td>" . $temp3 . "</td>";
				echo "<td><a href='tradeoptions.php?pr=1&usertradeid=" . $row["usertradeid"] . "'><input type='button' class='button2' value='&nbsp;DELETE&nbsp;' /></a></td>";
				
				echo "</tr>";
				
				$counter = $counter + 1;
			}
			if($counter == 0) {
				echo '<tr><td colspan="4">No entries</td></tr>';
			}
			?>
			</table>
	
			
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