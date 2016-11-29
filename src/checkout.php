<?php
session_start();
require("CommonMethods.php");
			
function getEmailBody($orderid, $seller, $buyer, $productid, $name, $description, $price, $method, $moreinfo, $comments) {
	$body = "Order ID: " . $orderid . "<br/>\n";
	$body .= "Seller: " . hex2bin($seller) . "</a><br/>\n";
	$body .= "<a href='http://userpages.umbc.edu/~jguansi1/CMSC447/productDetail.php?productid=" . $productid . "'>Product ID: " . $productid . "</a><br/>\n";
	$body .= "Product Name: " . hex2bin($name) . "<br/>\n";
	$body .= "Price: " . number_format($price, 2) . "<br/>\n";
	$body .= "<br/>\n";
	$body .= "Buyer: " . $buyer . "<br/>\n";
	$body .= "Payment Method: " . $method . "<br/>\n";
	if($moreinfo != "") {
		$body .= "" . $moreinfo . "<br/>\n";
	}
	$body .= "<br/>\n<br/>Comments: " . $comments . "<br/>\n";
	return $body;
}
$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	
	$productid = "";
	$pr = 0;
	$userid = $_SESSION["umbcbazaar_userid"];
	$errorMessage = "";
	$comments = "";
	if(!empty($_GET["productid"])) {
		$productid = htmlspecialchars(strtoupper($_GET["productid"]));
		if(!preg_match("/^[0-9]+$/", $productid)) {
			$productid = "";
		}
	}
	if(isset($_GET["pr"])) {
		$pr = htmlspecialchars($_GET["pr"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-3]$/", $pr)) {
			$pr = 0;
		}
	}
	if(isset($_GET["comments"])) {
		$comments = trim(htmlspecialchars($_GET["comments"]));
	}
	$rows = array();
	$db1 = new DBConnection;
	
	if($productid != "") {
		$query1 = "SELECT products.productid, products.name, products.description, products.price, products.trade, products.userid, users.username, users.umbcid FROM products, users WHERE products.productid = " . $productid . " AND products.userid = users.userid AND users.status >= 10 AND publish = 1 ";
		$rows = $db1 -> select($query1);
	}
	
	include("top.php");
	include("middle.html");
	if(count($rows) > 0) {
		if($pr == 1) {
			// checkout using cash on delivery
			if ($rows[0]["price"] > 0) {
				$query1 = "INSERT INTO orders (buyerid, sellerid, productid, price, payoption, payid, comments) ";
				$query1 .= "VALUES (" . $userid . ", " . $rows[0]["userid"] . ", " . $rows[0]["productid"] . ", " . $rows[0]["price"] . ", " . $pr .", 0, \"" . bin2hex($comments) . "\")";
				$db1 -> query($query1);
				$orderID =  mysql_insert_id($db1 -> $connection);
				// build the email body
				$body = getEmailBody($orderID, $rows[0]["username"], $_SESSION["umbcbazaar_username"], $productid, $rows[0]["name"], $rows[0]["description"], $rows[0]["price"], "Cash on Delivery", "", $comments);
				// send confirmation email
				echo "Processing...";
				$sendEmailURL0 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $_SESSION["umbcbazaar_umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
				$sendEmailURL1 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $rows[0]["umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
				curl($sendEmailURL0);
				curl($sendEmailURL1);
				$query3 = "UPDATE products SET publish = 0, lastaccess = SYSDATE() WHERE productid = " . $productid;
				$db1 -> query($query3);
				$errorMessage .= "Order ID# " . $orderID . " received. An email has been sent to you and the seller for verification.<br/>\n";
			}
			else {
				$errorMessage .= "Cash on delivery is not available for this product.<br/>\n";
			}
		}
		elseif ($pr == 2) {
			// checkout using trade options
			if ($rows[0]["trade"] == 1) {
				$usertradeid = "";
				if(isset($_GET["usertradeid"])) {
					$usertradeid = htmlspecialchars($_GET["usertradeid"]);
					// regular expression to check if it is a number
					if(!preg_match("/^[0-9]+$/", $usertradeid)) {
						$usertradeid = "";
						$errorMessage .= "Invalid usertradeid format.<br/>\n";
					}
				}
				if($usertradeid != "") {
					$query0 = "SELECT usertrade.usertradeid, trades.name, usertrade.description, usertrade.hours, usertrade.itemvalue FROM usertrade, trades WHERE usertrade.usertradeid = " . $usertradeid . " AND ";
					$query0 .= "usertrade.tradeid = trades.tradeid AND usertrade.userid = " . $userid . " AND usertrade.logicaldelete >= 1 ORDER BY usertrade.lastaccess DESC";
					$rows1 = $db1 -> select($query0);
					if(count($rows1[0]) > 0) {
						$query1 = "INSERT INTO orders (buyerid, sellerid, productid, price, payoption, payid, comments) ";
						$query1 .= "VALUES (" . $userid . ", " . $rows[0]["userid"] . ", " . $rows[0]["productid"] . ", " . $rows[0]["price"] . ", " . $pr .", " . $usertradeid . ", \"" . bin2hex($comments) . "\") ";
						$db1 -> query($query1);
						$orderID =  mysql_insert_id($db1 -> $connection);
						
						$moreInfo = "Trade option: " . $rows1[0]["name"] . "<br/>\n";
						if($rows1[0]["hours"] > 0) {
							$moreInfo .= "Hours: " . $rows1[0]["hours"]. "<br/>\n";
						}
						if($rows1[0]["itemvalue"] > 0) {
							$moreInfo .= "Item Value: " . $rows1[0]["itemvalue"]. "<br/>\n";
						}
						$moreInfo .= "Description: " . hex2bin($rows1[0]["description"]);
						$body = getEmailBody($orderID, $rows[0]["username"], $_SESSION["umbcbazaar_username"], $productid, $rows[0]["name"], $rows[0]["description"], $rows[0]["price"], "Trade using trade options", $moreInfo, $comments);
						// send confirmation email
						echo "Processing...";
						$sendEmailURL0 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $_SESSION["umbcbazaar_umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
						$sendEmailURL1 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $rows[0]["umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
						curl($sendEmailURL0);
						curl($sendEmailURL1);
						$query3 = "UPDATE products SET publish = 0, lastaccess = SYSDATE() WHERE productid = " . $productid;
						$db1 -> query($query3);
						$errorMessage .= "Order ID# " . $orderID . " received. An email has been sent to you and the seller for verification. The seller can either accept or reject the trade offer.<br/>\n";
					}
					else {
						$errorMessage .= "Invalid trade option. Could not find the buyer's item with given usertradeid.<br/>\n";
					}
					
				}
				else {
					$errorMessage .= "Invalid usetradeid format. <br/>\n";
				}
			}
			else {
				$errorMessage .= "Trade option is not available for this product.<br/>\n";
			}
		}
		elseif ($pr == 3) {
			// checkout using buyer's product listing
			if ($rows[0]["trade"] == 1) {
				$buyerproductid = "";
				if(isset($_GET["buyerproductid"])) {
					$buyerproductid = htmlspecialchars($_GET["buyerproductid"]);
					// regular expression to check if it is a number
					if(!preg_match("/^[0-9]+$/", $buyerproductid)) {
						$buyerproductid = "";
						$errorMessage .= "Invalid buyerproductid format.<br/>\n";
					}
				}
				if($buyerproductid != "") {
					$query0 = "SELECT productid, name, description, price FROM products WHERE userid = " . $userid . " AND productid = " . $buyerproductid . " AND publish >= 1 LIMIT 1 ";
					$rows1 = $db1 -> select($query0);
					if(count($rows1[0]) > 0) {
						$query1 = "INSERT INTO orders (buyerid, sellerid, productid, price, payoption, payid, comments) ";
						$query1 .= "VALUES (" . $userid . ", " . $rows[0]["userid"] . ", " . $rows[0]["productid"] . ", " . $rows[0]["price"] . ", " . $pr .", " . $buyerproductid . ", \"" . bin2hex($comments) . "\") ";
						$db1 -> query($query1);
						$orderID =  mysql_insert_id($db1 -> $connection);
						$moreInfo = "<a href='http://userpages.umbc.edu/~jguansi1/CMSC447/productDetail.php?productid=" . $rows1[0]["productid"] . "' >Trading Product ID: " . $rows1[0]["productid"] . "</a><br/>\n";
						if($rows1[0]["price"] > 0) {
							$moreInfo .= "Price: $ " . number_format($rows1[0]["price"], 2) . "<br/>\n";
						}
						$moreInfo .= "Name: " . hex2bin($rows1[0]["name"]);
						//$moreInfo .= "Description: " . hex2bin($rows1[0]["description"]);
						$body = getEmailBody($orderID, $rows[0]["username"], $_SESSION["umbcbazaar_username"], $productid, $rows[0]["name"], $rows[0]["description"], $rows[0]["price"], "Trade with a product listing", $moreInfo, $comments);
						// send confirmation email
						echo "Processing...";
						$sendEmailURL0 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $_SESSION["umbcbazaar_umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
						$sendEmailURL1 = $EMAIL_SERVER . "/sendemail.php?umbcid=" . $rows[0]["umbcid"] . "&subject=" . bin2hex("Order # " . $orderID . " Received") . "&body=" . bin2hex($body);
						curl($sendEmailURL0);
						curl($sendEmailURL1);
						$query3 = "UPDATE products SET publish = 0, lastaccess = SYSDATE() WHERE productid = " . $productid;
						$db1 -> query($query3);
						$errorMessage .= "Order ID# " . $orderID . " received. An email has been sent to you and the seller for verification. The seller can either accept or reject the trade offer.<br/>\n";
					}
					else {
						$errorMessage .= "Invalid trade option. Could not find buyer's item with given productid.<br/>\n";
					}
					
				}
				else {
					$errorMessage .= "Invalid buyerproductid format. <br/>\n";
				}
			}
			else {
				$errorMessage .= "Trade option is not available for this product.<br/>\n";
			}
		}
		echo "<br/><span class=\"errorMessage\">" . $errorMessage . "</span><br/>";
		
		if($pr != 1 && $pr != 2 && $pr != 3) {
			echo "<center><h2>Checkout Process</h2>Name: " . hex2bin($rows[0]["name"]) . "<br/>\n";
			if ($rows[0]["price"] > 0) {
				$cashOption = true;
				echo "<font color='red'>Price: $" . number_format($rows[0]["price"], 2) . "</font><br/>";
			}
			?>
			<form ACTION="checkout.php" method="get" onSubmit="return checkBeforeSubmit()">
			<input type="hidden" name="pr" id="pr" value="0"/>
			<input type="hidden" name="productid" id="productid" value="<?php echo $productid; ?>" />
			<textarea rows="8" cols="45" class="loginTextArea" id="comments" name="comments" placeholder="Type comments and other useful information here like place and time to meet" required ><?php echo $comments; ?></textarea><br/>
			<?php
			if($cashOption == true) {
				?>
				<button class="button1" type="submit" name="btn1" id="btn1" value="1">CHECKOUT -- CASH ON DELIVERY</button>
				<script>
					document.getElementById("btn1").onclick = function() {
					document.querySelector("#pr").value = 1;
				};
				</script>
				<br/><br/>
				<?php
			}
			if ($rows[0]["trade"] == 1) {
				$tradeOption = true;
				
				$query4 = "SELECT usertrade.usertradeid, trades.name, usertrade.description, usertrade.hours, usertrade.itemvalue FROM usertrade, trades ";
				$query4 .= "WHERE usertrade.userid = " . $userid . " AND usertrade.tradeid = trades.tradeid AND usertrade.logicaldelete >= 1 ORDER BY usertrade.lastaccess DESC";
				$tradelist = $db1 -> select($query4);
				
				$query5 = "SELECT productid, name, price FROM products WHERE userid = " . $userid . " AND publish >= 1 ORDER BY lastaccess DESC";
				$productlist = $db1 -> select($query5);
				
				?>
				<h2>Trade Options</h2>
				<?php
				if (count($tradelist) > 0) {
				?>
					<select class="loginInput" id="usertradeid" name="usertradeid" title="Select trade option" maxlength="150" required autofocus >
					<option VALUE="0"> </option>
					<?php
					foreach ($tradelist as $listing1) {
						$displayOut = "<option value='" . $listing1["usertradeid"] . "' ";
						$displayOut .= ">ID#" . $listing1["usertradeid"] .  " -- ";
						$temp5 = $listing1["name"];
						if(strlen($temp5) > 100) {
							$temp5 = substr($temp5, 0, 100);
						}
						$displayOut .= $temp5;
						if($listing1["hours"] > 0) {
							$displayOut .= " -- Hours: " . $listing1["hours"];
						}
						if($listing1["itemvalue"] > 0) {
							$displayOut .= " -- Item Value: $" . $listing1["itemvalue"];
						}
						$displayOut .= "</option>\n";
						echo $displayOut;
					}
					?>
					</select><br/>
					<button class="button1" type="submit" name="btn2" id="btn2" value="2" >CHECKOUT -- MY TRADE OPTIONS</button>
					<br/><br/>
					<script>
						document.getElementById("btn2").onclick = function() {
						document.querySelector("#pr").value = 2;
					};
					</script>
					<?php
				}
				if (count($productlist) > 0) {
				?>
					<select class="loginInput" id="buyerproductid" name="buyerproductid" title="Select trade option" maxlength="150" required autofocus >
					<option VALUE="0"> </option>
					<?php
					foreach ($productlist as $listing1) {
						$displayOut = "<option value='" . $listing1["productid"] . "' ";
						$displayOut .= ">ID#" . $listing1["productid"] .  " -- ";
						$temp5 = hex2bin($listing1["name"]);
						if(strlen($temp5) > 100) {
							$temp5 = substr($temp5, 0, 100);
						}
						$displayOut .= $temp5;
						if($listing1["price"] > 0) {
							$displayOut .= " -- Price: $" . number_format($listing1["price"], 2);
						}
						$displayOut .= "</option>\n";
						echo $displayOut;
					}
					?>
					</select><br/>
					<button class="button1" type="submit" name="btn3" id="btn3" value="3">CHECKOUT -- TRADE MY PRODUCTS</button>
					<br/><br/>
					<script>
						document.getElementById("btn3").onclick = function() {
						document.querySelector("#pr").value = 3;
					};
					</script>
					<?php
				}
				if ((count($productlist) == 0) && (count($tradelist) == 0)) {
					echo "There are no 'My Trade Option' or 'Product List' to choose from.<br/>\n";
				}
			}
			echo "<br/><br/>";
			echo "</form></center>";
			?>
			<script>
			function checkBeforeSubmit() {
				var returnValue = true;
				var msg = "";
				var pr = document.querySelector("#pr").value;
				var usertradeid = document.querySelector("#usertradeid").value;
				var buyerproductid = document.querySelector("#buyerproductid").value;
				if(pr == 2 && usertradeid == 0) {
					msg = "You must select a 'My Trade Option'.\n";
					returnValue = false;
				}
				if(pr == 3 && buyerproductid == 0) {
					msg = "You must select a product to trade.\n";
					returnValue = false;
				}
				if(returnValue == false) {
					alert(msg);
				}
				return returnValue;
				
			}
			</script>
			<?php
			
		}
	}
	else {
		echo "<span class=\"errorMessage\">The product id is not found. <br/>Please try your search again using the search bar.</span><br/><br/>";
	}
	$db1 -> close();
	
	
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>