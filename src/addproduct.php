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
	$errorMessage = "";
	$_SESSION["umbcbazaar_addProductMessage"] = ""; 
	
	if(isset($_GET["productid"])) {
		$productid = htmlspecialchars($_GET["productid"]);
		// regular expression to check if it is a number
		if(!preg_match("/^[0-9]+$/", $productid)) {
			$productid = "";
			$errorMessage .= "Invalid productid format.<br/>\n";
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
	if($productid != "" && $pr == 1) {
		$row = $db1 -> select("SELECT name, description, price, trade, publish, image FROM products WHERE productid = " . $productid . " AND userid = " . $userid . " LIMIT 1");
		if(count($row[0]) > 0) {
			$name = hexstr($row[0]["name"]);
			$description = hexstr($row[0]["description"]);
			$price = $row[0]["price"];
			$trade = $row[0]["trade"];
			$publish = $row[0]["publish"];
			$image = $row[0]["image"];
		}
		else {
			$errorMessage .= "Productid #" . $productid . " not found.<br/>\n";
		}
		$db1 -> close();
	}
	elseif($pr == 2) {
		if(isset($_GET["name"])) {
			$name = trim(htmlspecialchars($_GET["name"]));
		}
		if(isset($_GET["description"])) {
			$description = trim(htmlspecialchars($_GET["description"]));
		}
		if(isset($_GET["price"])) {
			$price = htmlspecialchars($_GET["price"]);
			// regular expression to check if it is a float
			if(!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
				$price = 0.0;
			}
		}
		if(isset($_GET["trade"])) {
			$trade = htmlspecialchars($_GET["trade"]);
			// regular expression to check if it is a number
			if(!preg_match("/^[0-1]$/", $trade)) {
				$trade = 0;
			}
		}
		if(isset($_GET["publish"])) {
			$publish = htmlspecialchars($_GET["publish"]);
			// regular expression to check if it is a number
			if(!preg_match("/^[0-1]$/", $publish)) {
				$publish = 0;
			}
		}
		if($price == 0.0 && $trade == 0) {
			$errorMessage .= "Must have at least a price or trade option.<br/>\n";
		}
		if(!empty($_FILES["fileToUpload"])) {
			$target_dir = "/afs/umbc.edu/users/j/g/jguansi1/pub/www/CMSC447/uploads/";
			$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
			$uploadOk = 1;
			$baseFileName = "" . $_FILES["fileToUpload"]["name"];
			if($baseFileName == "") {
				$uploadOk = 0;
			}
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			// Check if image file is an actual image or fake image
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($uploadOk == 1 && $check == false) {
				$errorMessage .= "Uploaded file is not an actual image file.<br/>\n";
				$uploadOk = 0;
			}
			if($uploadOk == 1 && $_FILES["fileToUpload"]["size"] > 500000) {
				$errorMessage .= "Uploaded file is too large (500kb limit).<br/>\n";
				$uploadOk = 0;
			}
			if($uploadOk == 1 && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" )) {
				$errorMessage .=  "Uploaded file is not a JPG, JPEG, PNG or GIF file.<br/>\n";
				$uploadOk = 0;
			}
			if($uploadOk == 1) {
				//$image = addslashes(file_get_contents($_FILES['fileToUpload']['tmp_name']));
				$image = file_get_contents($_FILES['fileToUpload']['tmp_name']);
			}
		}
		// need to have at least a name and description
		if($name != "" && $description != "" && $errorMessage == "") {
			$updateSQL = false;
			if($productid != "") {
				$row = $db1 -> select("SELECT COUNT(*) FROM products WHERE productid = " . $productid . " AND userid = " . $userid);
				if($row[0]["COUNT(*)"] > 0) {
					$updateSQL = true;
				}
				else {
					$errorMessage .= "Cannot update productid #" . $productid . ". Added a new entry instead.<br/>\n";
				}
			}
			$query1 = "";
			if($updateSQL == true) {
				// prepare an update statement
				$query1 .= "UPDATE products SET name = '" . strhex($name) . "', description = '" . strhex($description) . "', ";
				$query1 .= "searchname = '" . strhex(strtoupper($name)) . "', searchdescription = '" . strhex(strtoupper($description)) . "', ";
				$query1 .=  "price = " . $price . ", trade = " . $trade . ", publish = " . $publish . ", lastaccess = SYSDATE()";
				if($image != NULL) {
					$query1 .= ", image = '" . base64_encode($image) . "'";
				}
				$query1 .= " WHERE userid = " . $userid . " AND productid = " . $productid;
			}
			else {
				// prepare an insert statement
				$query1 .= "INSERT INTO products (userid, name, description, price, trade, publish, searchname, searchdescription";
				if($image != NULL) {
					$query1 .= ", image";
				}
				$query1 .= ") VALUES (" . $userid . ", '" . strhex($name) . "', '" . strhex($description) . "', " . $price . ", " . $trade . ", " . $publish;
				$query1 .= ", '" . strhex(strtoupper($name)) . "', '" . strhex(strtoupper($description)) . "'";
				if($image != NULL) {
					$query1 .= ", '" . base64_encode($image) . "'";
				}
				$query1 .= ") ";
			}
			//echo $query1;
			$db1 -> query($query1);
			$db1 -> close();
			$_SESSION["umbcbazaar_addProductMessage"] = $errorMessage; 
			// redirect to the seller's product page
			header("Location: sellproduct.php");
			exit();
		}
	}
	include("top.php");
	include("middle.html");
	echo "<span class=\"errorMessage\">" . $errorMessage . "</span><br/><br/>";
	?>
			Seller's Product or Service Information.<br/><br/>
			<a href="sellproduct.php?pr=1"><input class="button1" type="button" value="&nbsp;&nbsp;Go Back to Product / Service List&nbsp;&nbsp;"/></a>
			<br/><br/>
			<form action="addproduct.php" method="post" onsubmit="return checkProduct()" enctype="multipart/form-data">
				<input type="hidden" name="pr" id="pr" value="2" />
				<?php
				if($productid != "") {
					echo "<input type=\"hidden\" name=\"productid\" id=\"productid\" value=\"" . $productid . "\" />";
				}
				?>
				<table border="0" align="center">
				<tr>
				<td>Name</td><td><input class="loginInput" type="text" id="name" name="name" title="Please enter the product name"  value="<?php echo $name; ?>" placeholder="Name of product or service" maxlength="150" required autofocus /></td>
				</tr>
				<tr>
				<td colspan="2">Trade Option&nbsp;
				<input class="radioStyle" type="radio" name="trade" id="trade" value="1" <?php if($trade == 1) { echo "checked"; } ?> required />&nbsp;Yes
				<input class="radioStyle" type="radio" name="trade" id="trade" value="0" <?php if($trade == 0) { echo "checked"; } ?> />&nbsp;No
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Publish&nbsp;
				<input class="radioStyle" type="radio" name="publish" id="publish" value="1" <?php if($publish == 1) { echo "checked"; } ?> required />&nbsp;Yes
				<input class="radioStyle" type="radio" name="publish" id="publish" value="0" <?php if($publish == 0) { echo "checked"; } ?> />&nbsp;No
				</td>
				</tr>
				<tr>
				<td colspan="2">Price&nbsp;<input class="inputStyle" type="text" id="price" name="price" width="20" title="Please enter a non-negative numeric value for the price. Zero (0) value means you are only doing trade option." value="<?php echo $price; ?>" placeholder="Enter price for the product or service" maxlength="50" pattern="^[0-9]*\.?[0-9]+" required /></td>
				</tr>
				<tr>
				<td colspan="2">
				Description<br/>
				<textarea rows="15" cols="45" class="loginTextArea" id="description" name="description" title="Please enter your product description" placeholder="Type your product or service description here" required ><?php echo $description; ?></textarea>
				</td>
				</tr>
				<?php
				if($image != NULL && $pr != 2) {
					echo "<tr><td colspan='2'>Current Image<br/>";
					echo '<img src="data:image/jpeg;base64,'. $image .'" width="400" />';
					echo "</td></tr>";
				}
				?>
				<tr>
				<td colspan="2">
				Select image to upload<br/>
				<input class="fileStyle" type="file" name="fileToUpload" id="fileToUpload" accept="image/*"  />
				<script>
				function checkProduct() {
					var message = "";
					var returnValue = true;
					var current_price = document.querySelector("#price").value;
					var uploadFile = document.getElementById("fileToUpload").files[0];
					if(uploadFile.size > 512000) {
						returnValue = false;
						message += "The selected upload file is too big (max is 500 kb).\n";
					}
					if(!uploadFile.name.match(/^.+\.(jpg|jpeg|png|gif)$/i)) {
						message += "Invalid file upload type. \n";
						returnValue = false;
					}
					if(isNaN(current_price) || current_price < 0) {
							message += "The price must be a non-negative numeric value.\n";
							returnValue = false;
					}
					if(returnValue == false) {
						alert(message);
					}
					return returnValue;
				}
				
				</script>
				</td>
				</tr>
				
				<tr>
				<td colspan="2"><br/><input class="button1" type="submit" value="&nbsp;Submit&nbsp;" /></td>
				</tr>
				</table>
			</form>
	<?php
	include("bottom.html");
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>