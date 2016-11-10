<?php
session_start();
require("CommonMethods.php");

// accept either GET or POST HttpRequest
if($_POST) {
	$_GET = $_POST;
}

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$productid = "";
	$pr = 0;
	$userid = $_SESSION["umbcbazaar_userid"];
	$name = "";
	$description = "";
	$price = 0.0;
	$trade = 0;
	$publish = 0;
	$image = NULL;
	$errorMessage = $_SESSION["umbcbazaar_addProductMessage"];
	
	$active = array();
	$inactive = array();
	
	$db1 = new DBConnection;
	$rows = $db1 -> select("SELECT productid, name, publish FROM products WHERE userid = " . $userid . " ORDER BY lastaccess DESC");
	
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
			List of Products and Services<br/><br/>
			<a href="addproduct.php?pr=1"><input class="button2" type="button" value="&nbsp;&nbsp;Add a New Product or Service&nbsp;&nbsp;"/></a>
			<br/><br/>
			<table border="3" align="center">
			<tr><td width="700" height="50" bgcolor="black" ><font color="#ffcc00">Active / Published</font><br/></td></tr>
			<?
			$counter = 0;
			foreach($rows as $row) {
				if($row["publish"] == 1) {
					echo "<tr><td><a href='addproduct.php?pr=1&productid=" . $row["productid"] . "'><div>";
					echo "Product ID: " . $row["productid"] . "<br/>\n";
					echo "Name: " . hexstr($row["name"]) . "<br/>\n";
					echo "</div></a></td></tr>";
					$counter = $counter + 1;
				}
			}
			if($counter == 0) {
				echo '<tr><td>No entries</td></tr>';
			}
			?>
			</table>
			<br/><br/>
			
			<table border="3" align="center">
			<tr><td width="700" height="50" bgcolor="black" ><font color="#ffcc00">Inactive / Not Published</font><br/></td></tr>
			<?
			$counter = 0;
			foreach($rows as $row) {
				if($row["publish"] == 0) {
					echo "<tr><td><a href='addproduct.php?pr=1&productid=" . $row["productid"] . "'><div>";
					echo "Product ID: " . $row["productid"] . "<br/>\n";
					echo "Name: " . hexstr($row["name"]) . "<br/>\n";
					echo "</div></a></td></tr>";
					$counter = $counter + 1;
				}
			}
			if($counter == 0) {
				echo '<tr><td>No entries</td></tr>';
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