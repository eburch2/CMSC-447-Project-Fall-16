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
		$query1 = "SELECT products.productid, products.name, products.description, products.image, products.price, products.trade, users.username FROM products, users WHERE products.productid = " . $productid . " AND products.userid = users.userid AND publish = 1 ";
		$rows = $db1 -> select($query1);
		//echo $query1;
		$db1 -> close();
	}
	
	include("top.php");
	include("middle.html");
	echo "<input type='button' class='button1' onclick='window.history.back();' value='&larr;&nbsp;BACK&nbsp;'/><br/>";
	if(count($rows) > 0) {
		echo "<center><div class='productDetail'><span class='productDetail'><h2>" . hex2bin($rows[0]["name"]) . "</h2>\n";
		echo "By: " . hex2bin($rows[0]["username"]) . "";
		if($rows[0]["price"] > 0) {
			echo "<h2><font color='red'>Price: $" . number_format($rows[0]["price"], 2) . "</font></h2>";
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