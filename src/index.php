<?php
session_start();
require("CommonMethods.php");

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	
	$offset = 1;
	if(!empty($_GET["offset"])) {
		$offset = htmlspecialchars(strtoupper($_GET["offset"]));
		if(!preg_match("/^[0-9]+$/", $offset)) {
			$offset = 1;
		}
	}
	$db1 = new DBConnection;
	$row = $db1 -> select("SELECT COUNT(*) FROM products WHERE publish = 1 ORDER BY lastaccess DESC ");
	$rowCount = $row[0]["COUNT(*)"];
	$query1 = "SELECT products.productid, products.name, products.image, products.price, products.trade, users.username FROM products, users WHERE products.userid = users.userid AND publish = 1 ORDER BY products.lastaccess DESC LIMIT 10";
	if($offset > 1) {
		$query1 .= " OFFSET " . (($offset - 1) * 10);
	}
	$rows = $db1 -> select($query1);
	$db1 -> close();
	
	
	
	include("top.php");
	include("middle.html");
	
	if(count($rows) > 0) {
		echo "<table border='0' align='center'>\n";
		
		$link = "";
		$totalPages = ceil($rowCount / 10);
		if($offset > $totalPages) {
			$offset = $totalPages;
		}
		$spacer = 1;
		$link .= '<a href="index.php?offset=1"><input class="button1" type="button" value="1"/></a>&nbsp;';
		if($offset-$spacer > 2) {
			$link .= "&mdash;&mdash;&nbsp";
		}
		for($i=2; $i < $totalPages; $i++) {
			if($i >= ($offset - $spacer) && $i <= ($offset + $spacer)) {
				$link .= '<a href="index.php?offset=' . $i . '"><input class="button1" type="button" value="' . $i . '"/></a>&nbsp;';
			}
		}
		if($totalPages > 1) {
			if($offset+$spacer < $totalPages - 1 && $totalPages > ($spacer*2 + 2)) {
				$link .= "&mdash;&mdash;&nbsp";
			}
			$link .= '<a href="index.php?offset=' . $totalPages . '"><input class="button1" type="button" value="' . $totalPages . '"/></a>&nbsp;';
		}
		
		
		echo "<br/>" . $link . "<br/><br/>";
		foreach($rows as $entry) {
			echo "<a href='productDetail.php?productid=" . $entry["productid"] . "'><div class='productDetail2'><span>";
			if($entry["image"] != NULL || $entry["image"] != "") {
				echo '<img src="data:image/jpeg;base64,'. $entry["image"] .'" width="200"/>';
			}
			else {
				echo '<img src="No_Image_Available.png" width="200" />';
			}
			echo "</span><span class='productInfo'><br/>" . hexstr($entry["name"]) . "<br/>By: "  . hex2bin($entry["username"]) . "<br/><br/>";
			if($entry["price"] > 0) {
				echo "Buy for $" . number_format($entry["price"], 2) . "<br/>";
			}
			if($entry["trade"] == 1) {
				echo "Trade Option Available<br/>";
			}
			echo "</span>";
			echo "</div></a><br/>";
			// echo "<tr><td colspan='2'><hr/></td></tr>";
		}
		echo "<br/>" . $link . "</br><br/>";

		
		
		
	}
	else {
		echo "<span class=\"errorMessage\">No Results Found!</span><br/><br/>";
	}
	
	
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>