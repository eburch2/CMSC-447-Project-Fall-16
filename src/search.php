<?php
session_start();
require("CommonMethods.php");

/*
// accept either GET or POST HttpRequest
if($_POST) {
	$_GET = $_POST;
}
*/

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$keyword = array();
	$offset = 1;
	if(!empty($_GET["keyword"])) {
		$temp = htmlspecialchars($_GET["keyword"]);
		//$temp = preg_replace("/[^A-Za-z0-9\s]/", "", $temp);
		$temp = explode(" ", $temp);
		foreach($temp as $val) {
			$val = str_replace(" ", "", $val);
			if(strlen($val) > 2) {
				array_push($keyword, $val);
			}
		}
	}
	if(count($keyword) == 0) {
		// this is to check if the keywords are stored in the session for multiple page result
		if(isset($_SESSION["umbcbazaar_keywords"])) {
			$keyword = $_SESSION["umbcbazaar_keywords"];
		}
	}
	else {
		// make sure to save the latest set of keywords
		$_SESSION["umbcbazaar_keywords"] = $keyword;
	}
	if(!empty($_GET["offset"])) {
		$offset = htmlspecialchars(strtoupper($_GET["offset"]));
		if(!preg_match("/^[0-9]+$/", $offset)) {
			$offset = 1;
		}
	}
	$rows = array();
	$resultCount = 0;
	// only do a database search if there are keywords that are greater than 2 in length
	if(count($keyword) > 0) {
		// build the search string
		$temp = "";
		$firstElement = true;
		foreach($keyword as $val) {
			if($firstElement == false) {
				$temp .= " OR ";
			}
			else {
				$firstElement = false;
			}
			$temp .= "searchname LIKE \"%" . strhex(strtoupper($val)) . "%\" OR searchdescription LIKE \"%" . strhex(strtoupper($val)) . "%\"";
		}
		$temp .= ") AND publish = 1 ";
		$query1 = "SELECT productid, name, image, description, products.userid, products.price, products.trade, users.username FROM products, users WHERE products.userid = users.userid AND (" . $temp . " LIMIT 10";
		$query2 = "SELECT COUNT(*) FROM products WHERE (" . $temp;
		if($offset > 1) {
			$query1 .= " OFFSET " . (10 * ($offset - 1));
		}
		$db1 = new DBConnection;
		// get the total number of search reuslts
		//echo $query1;
		$temp1 = $db1 -> select($query2);
		$resultCount = $temp1[0]["COUNT(*)"];
		//echo $resultCount;
		// get the search results
		$rows = $db1 -> select($query1);
		$db1 -> close();
	}
	include("top.php");
	include("middle.html");
	
	if(count($rows) > 0) {
		echo "<table border='0' align='center'>\n";
		
		$link = "";
		$totalPages = ceil($resultCount / 10);
		if($offset > $totalPages) {
			$offset = $totalPages;
		}
		$spacer = 1;
		$link .= '<a href="search.php?offset=1"><input class="button1" type="button" value="1"/></a>&nbsp;';
		if($offset-$spacer > 2) {
			$link .= "&mdash;&mdash;&nbsp";
		}
		for($i=2; $i < $totalPages; $i++) {
			if($i >= ($offset - $spacer) && $i <= ($offset + $spacer)) {
				$link .= '<a href="search.php?offset=' . $i . '"><input class="button1" type="button" value="' . $i . '"/></a>&nbsp;';
			}
		}
		if($totalPages > 1) {
			if($offset+$spacer < $totalPages - 1 && $totalPages > ($spacer*2 + 2)) {
				$link .= "&mdash;&mdash;&nbsp";
			}
			$link .= '<a href="search.php?offset=' . $totalPages . '"><input class="button1" type="button" value="' . $totalPages . '"/></a>&nbsp;';
		}
		echo "" . $link . "<br/><br/>";
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
		}
		echo "<br/>" . $link . "<br/><br/>";
	}
	else {
		echo "<span class=\"errorMessage\">No Results Found!</span><br/><br/>";
	}
	?>
	<?php
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>