<?php
session_start();
require("CommonMethods.php");

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	
	$productid = "";
	if(!empty($_GET["productid"])) {
		$productid = htmlspecialchars(strtoupper($_GET["productid"]));
		if(!preg_match("/^[0-9]+$/", $productid)) {
			$productid = "";
		}
	}
	$rows = array();
	if($productid != "") {
		$db1 = new DBConnection;
		$query1 = "SELECT products.productid, products.name, products.description, products.image, products.price, products.trade, users.username FROM products, users WHERE products.productid = " . $productid . " AND products.userid = users.userid AND users.status >= 10 AND publish = 1 ";
		$rows = $db1 -> select($query1);
		//echo $query1;
		$db1 -> close();
	}
	
	include("top.php");
	include("middle.html");
	echo "<input type='button' class='button1' onclick='window.history.back();' value='&larr;&nbsp;BACK&nbsp;'/><br/>";
	if(count($rows) > 0) {
		echo "<center><div class='productDetail'><span class='productDetail'><h2>" . hex2bin($rows[0]["name"]) . "</h2>\n";
		echo "By: " . hex2bin($rows[0]["username"]) . "<br/>";
		echo "<a href='email.php?user=" . hex2bin($rows[0]["username"]) . "&h=1&msg=" . bin2hex("This message is regarding\nProduct Listing ID: " . $rows[0]["productid"] . "\n\n") . "'>[ Contact the seller about the listing ]</a><br/>";
		$temp1 = "";
		$temp2 = "";
		if($rows[0]["price"] > 0) {
			$temp2 = "<br/><br/><font color='red' size='6' >Price: $" . number_format($rows[0]["price"], 2) . "</font>";
			$temp1 = "BUY";
		}
		if($rows[0]["trade"] > 0) {
			if($temp1 != "") {
				$temp1 .= " / ";
			}
			$temp1 .= "TRADE";
		}
		if($temp1 != "") {
			echo $temp2 . "<br/><a href='checkout.php?productid=" . $productid . "'><input type='button' class='button1' value='&nbsp;" . $temp1 . "&nbsp;'/></a><br/><br/>";
		}
		
		if($rows[0]["image"] != NULL || $rows[0]["image"] != "") {
			echo '<img src="data:image/jpeg;base64,'. $rows[0]["image"] .'" width="400"/><br/>';
		}
		echo "<br/>" . str_replace("\n", "<br/>", hex2bin($rows[0]["description"])) . "</br>";
		
		echo "</span></div></center><br/>";
		echo "<table border='0' align='center'>\n";
		
		
		
		
		
	}
	else {
		echo "<span class=\"errorMessage\">The product id is not found. <br/>Please try your search again using the search bar.</span><br/><br/>";
	}
	
	
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>