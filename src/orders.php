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
	$rows = array();
	$pr = 0;
	$resultCount = 0;
	$offset = 1;
	if(isset($_GET["pr"])) {
		$pr = htmlspecialchars($_GET["pr"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-1]$/", $pr)) {
			$pr = 0;
		}
	}
	if(!empty($_GET["offset"])) {
		$offset = htmlspecialchars(strtoupper($_GET["offset"]));
		if(!preg_match("/^[0-9]+$/", $offset)) {
			$offset = 1;
		}
	}
	$db1 = new DBConnection;
	$payoptions = array("", "Cash on Delivery", "Trade - My Trade Options", "Trade - Product Listing");
	if($pr == 0) {
		// get all the orders where the user is the buyer
		$query0 = "SELECT orders.orderid, orders.sellerid, users.username, orders.productid, products.name, products.image, orders.price, orders.payoption, orders.payid, orders.comments , orders.datecreated ";
		$temp1 = "FROM orders, users, products WHERE orders.buyerid = " . $userid . " AND orders.sellerid = users.userid AND orders.productid = products.productid ";
		$query00 = "SELECT COUNT(*) " . $temp1;
		$query0 .= $temp1;
		$query0 .= "ORDER BY orders.datecreated DESC LIMIT 10 ";
		If($offset > 1) {
			$query0 .= "OFFSET " . (10 * ($offset - 1));
		}
		$rows = $db1 -> select($query0);
		$temp2 = $db1 -> select($query00);
		$resultCount = $temp2[0]["COUNT(*)"];
		
	}
	elseif($pr == 1) {
		// get all the orders where the user is the seller
		$query0 = "SELECT orders.orderid, orders.buyerid, users.username, orders.productid, products.name, products.image, orders.price, orders.payoption, orders.payid, orders.comments, orders.datecreated ";
		$temp1 = "FROM orders, users, products WHERE orders.sellerid = " . $userid . " AND orders.buyerid = users.userid AND orders.productid = products.productid ";
		$query00 = "SELECT COUNT(*) " . $temp1;
		$query0 .= $temp1;
		$query0 .= "ORDER BY orders.datecreated DESC LIMIT 10 ";
		If($offset > 1) {
			$query0 .= "OFFSET " . (10 * ($offset - 1));
		}
		$rows = $db1 -> select($query0);
		$temp2 = $db1 -> select($query00);
		$resultCount = $temp2[0]["COUNT(*)"];
	}
	
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
	<h2>My Transactions <a href="orders.php?pr=0"><input class="button<?php if($pr == 0) { echo "2"; } else { echo "3"; } ?>" type="button" value="&nbsp;AS BUYER&nbsp;"  /></a><a href="orders.php?pr=1"><input class="button<?php if($pr == 1) { echo "2"; } else { echo "3"; } ?>" type="button" value="&nbsp;AS SELLER&nbsp;"/></a>
	</h2><br/>
	<br/>
	<?php
	$link = "";
	$totalPages = ceil($resultCount / 10);
	if($offset > $totalPages) {
		$offset = $totalPages;
	}
	$spacer = 1;
	$link .= '<a href="orders.php?pr=' . $pr . '&offset=1"><input class="button1" type="button" value="1"/></a>&nbsp;';
	if($offset-$spacer > 2) {
		$link .= "&mdash;&mdash;&nbsp";
	}
	for($i=2; $i < $totalPages; $i++) {
		if($i >= ($offset - $spacer) && $i <= ($offset + $spacer)) {
			$link .= '<a href="orders.php?pr=' . $pr . '&offset=' . $i . '"><input class="button1" type="button" value="' . $i . '"/></a>&nbsp;';
		}
	}
	if($totalPages > 1) {
		if($offset+$spacer < $totalPages - 1 && $totalPages > ($spacer*2 + 2)) {
			$link .= "&mdash;&mdash;&nbsp";
		}
		$link .= '<a href="orders.php?pr=' . $pr . '&offset=' . $totalPages . '"><input class="button1" type="button" value="' . $totalPages . '"/></a>&nbsp;';
	}
	if($resultCount > 0) {
		echo "" . $link . "<br/><br/>";
	}
	
	
	$query0 = "SELECT orders.orderid, orders.buyerid, users.username, orders.productid, products.name, orders.price, orders.payoption, orders.payid, orders.comments, orders.datecreated ";
		$temp1 = "FROM orders, users, products WHERE orders.sellerid = " . $userid . " AND orders.buyerid = users.userid AND orders.productid = products.productid ";
	
	foreach($rows as $row) {
		
	}
	?>
		<?
		$counter = 0;
		foreach($rows as $row) {
			
			?>
		<table border="3" align="center" width="700">
		<tr bgcolor="black" height="50" >
		<td><font color="#ffcc00">Transaction ID</font></td>
		<td><font color="#ffcc00">Transaction Date</font></td>
		<td><font color="#ffcc00">Transacted User</font></td>
		<td><font color="#ffcc00">Product ID</font></td>
		<td><font color="#ffcc00">Pay Option</font></td>
		<td><font color="#ffcc00">Email</font></td>
		</tr>
			<?php
			$temp2 = "" . $row["hours"];
			$temp3 = "$" . number_format($row["itemvalue"], 2);
			if($tradelist[$row["tradeid"]][2] == 0) {
				$temp3 = "--";
			}
			else {
				$temp2 = "--";
			}
			echo "<tr><td>" . $row["orderid"] . "</td><td>" . $row["datecreated"] . "</td><td>" . hex2bin($row["username"]) . "</td><td>" . $row["productid"] . "</td><td>" . $payoptions[$row["payoption"]] . "</td>";
			echo "<td><a href='email.php?user=" . hex2bin($row["username"]) . "&h=1&msg=" . bin2hex("This message is regarding\nOrder ID: " . $row["orderid"] . "\n" . "Product listing ID: " . $row["productid"] . "\n\n") . "'><input type='button' class='button2' value='&nbsp;EMAIL&nbsp;' /></a></td>";
			
			echo "</tr>";
			
			$counter = $counter + 1;
			echo "<tr><td>";
			if($row["image"] != NULL || $row["image"] != "") {
				echo '<img src="data:image/jpeg;base64,'. $row["image"] .'" width="150"/>';
			}
			else {
				echo '<img src="No_Image_Available.png" width="150" />';
			}
			echo "</td><td colspan='5'>";
			echo hex2bin($row["name"]) . "<br/>\n";
			if ($row["price"] > 0) {
				echo "<font color='red'>Order Total: $" . number_format($row["price"], 2) . "</font><br/>\n";
			}
			echo "<br/><br/>";
			$temp1 = $row["payoption"];
			if($temp1 == 2) {
				echo $payoptions[$temp1] . "<br/>";
				echo "Usertrade ID: " . $row["payid"] . "</br>";
				$temprow = $db1 -> select("SELECT description, hours, itemvalue FROM usertrade WHERE usertradeid = " . $row["payid"]);
				if($temprow[0]["hours"] > 0) {
					echo "Hours: " . $temprow[0]["hours"] . "<br/>";
				}
				else {
					echo "Item Value: $" . number_format($temprow[0]["itemvalue"], 2) . "<br/>";
				}
				echo hex2bin($temprow[0]["description"]) . "<br/>";
			}
			elseif($temp1 == 3) {
				echo $payoptions[$temp1] . "<br/>";
				echo "Product ID: " . $row["payid"] . "</br>";
				$temprow = $db1 -> select("SELECT name, price, image FROM products WHERE productid = " . $row["payid"] . " LIMIT 1");
				echo hex2bin($temprow[0]["name"]) . "<br/>";
				if($temprow[0]["price"] > 0) {
					echo "Price: $" . number_format($temprow[0]["price"], 2) . "<br/>";
				}
				if($temprow[0]["image"] != NULL || $temprow[0]["image"] != "") {
					echo '<img src="data:image/jpeg;base64,'. $temprow[0]["image"] .'" width="150"/><br/>';
				}
			}
			echo "<br/>Comments:<br/>" . str_replace("\n", "<br\>", hex2bin($row["comments"])) . "\n";
			echo "</td></tr>";
			echo "</table><br/>";
		}
		if($counter == 0) {
			echo '<h3 align="center">No entries</h3>';
		}
		?>
			<br/><br/>

			
	<?php
	if($resultCount > 0) {
		echo "" . $link . "<br/><br/>";
	}
	
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>