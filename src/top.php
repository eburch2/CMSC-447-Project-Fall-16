<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="styles123.css" >
	<link rel="icon" type="image/png" href="./icon.png" />
	<title>UMBC Bazaar</title>
<script src="login.js"></script>
</head>
<body>
	<div id="wrapper" class="wrapper" >
		<div class="headerStripe1" >&nbsp;</div>
		<div class="headerStripe2" >&nbsp;</div>
		<div class="headercss" id="headercss">
			<div class="headernav" >
				<div>
				<table border="0" style="border-color:white;white-space: nowrap;" width="100%" cellpadding="0" cellspacing="0" nowrap>
				<tr nowrap>
				<td class="logo" ><a alt="UMBC Bazaar" title="UMBC Bazaar" href="./index.php"><img class="logo" src="./retrievers.jpg" /></a></td>
				<td align="left" class="navTableCell" nowrap>
				NEW AND INTERESTING FINDS<br/>
				<i>Buy <font color="#ffffff">Sell</font> Trade<br/>
				Items and Services</i><br/>
<?php
if(count(sessionInfo()) > 0) {
?>
<form action="search.php" method="get">
<div class="searchTop"><input type="text" id="keyword" name="keyword" class="searchBar" title="Enter keyword to search for products or services. Alphanumeric characters and spaces only" autocomplete="off" placeholder="Enter search keyword" required /><input type="image" src="search1.png" class="imageSubmit" /></div>
</form>
<?php
}
?>
				</td>
				<td align="right" style="vertical-align: bottom;">
				
<?php
if(count(sessionInfo()) > 0) {
?>
	<div class="dropdown2">
		<button class="dropbtn2">My Account&nbsp;&#x25BC;</button>
		<div class="dropdown2-content">
			<a href="profile.php">Profile</a>
			<a href="sellproduct.php">My Products</a>
			<a href="listtrade.php">Trade Options</a>
			<a href="#">My Orders</a>
			<a href="#">Send a Message</a>
		</div>
	</div>
	<a href="logout.php"><input class="button3" type="button" value="Logout" /></a>&nbsp;&nbsp;<br/><br/>
<?php
}
?>