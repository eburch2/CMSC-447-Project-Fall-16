<?php

require("DBConnection.php");
$EMAIL_SERVER = "http://100.16.216.147";

function curl($url) {
	// Defining the basic cURL function
	$ch = curl_init();  // Initialising cURL
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
	$data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
	curl_close($ch);    // Closing cURL
	return $data;   // Returning the data from the function
}
function randomHex($howMany) {
	// generate random hex values
	$hexValue = "";
	for($i=0; $i < $howMany; $i++) {
		$hexValue .= dechex(mt_rand(0,15));
	}
	return $hexValue;
}
function encryptData($plaintext, $key) {
	// using SHA 512 hashing using the passed key -- 10000 rounds
	$rounds = '$6$rounds=10000$';
	$password = crypt($plaintext, $rounds . $key . '$');
	return $password;
}
function hashPassword($plaintext) {
	// the key for password hashing is stored in htaccess folder
	// using a 16 character salt
	$key = randomHex(16);
	return encryptData($plaintext, $key);
}
function passwordFormat($pass123) {
	$password_min = 10;
	$password_max = 50;	
	// password must bet at between 10 and 50 characters long
	if(!(strlen($pass123) >= $password_min && strlen($pass123) <= $password_max)) {
		$pass123 = "";
	}
	if(!preg_match("/.*[A-Z]+.*/", $pass123)) {
		// must contain at least 1 uppercase
		$pass123 = "";
	}
	if(!preg_match("/.*[a-z]+.*/", $pass123)) {
		// must contain at least 1 lowercase
		$pass123 = "";
	}
	if(!preg_match("/.*[0-9]+.*/", $pass123)) {
		// must contain at least 1 number
		$pass123 = "";
	}
	if(!preg_match("/.*[!@#\$%]+.*/", $pass123)) {
		// must conain at least 1 of these characters
		$pass123 = "";
	}
	return $pass123;
}
function hex2bin($str) {
	// convert the values passed to hex values
	// text values in the databse are stored as hex -- prevent SQL injection
	$sbin = "";
	$len = strlen( $str );
	for ( $i = 0; $i < $len; $i += 2 ) {
		$sbin .= pack( "H*", substr( $str, $i, 2 ) );
	}
	return $sbin;
}
function strhex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}
function hexstr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}
function sessionInfo() {
	// return an array of the user's information from the session
	$returnValue = array();
	if(!isset($_SESSION["LAST_ACTIVITY"])) {
		// this session variable is not set -- the user is NOT LOGGED IN
		return $returnValue;
	}
	// check if the session's last activity is more than 30 minutes, and then invalidate it
	if(time() - $_SESSION["LAST_ACTIVITY"] > 1800) {
		session_unset();
		session_destroy();
		session_start();
		return $returnValue;
	}
	// update last activity timestamp
	$_SESSION["LAST_ACTIVITY"] = time();
	// check if the student info is stored in the session
	if(isset($_SESSION["umbcbazaar_username"]) && isset($_SESSION["umbcbazaar_password"])) {
		// make sure the user credentials stored in session variables are valid
		$db1 = new DBConnection;
		$row = $db1 -> select("SELECT COUNT(*) FROM users WHERE username = \"" . bin2hex($_SESSION["umbcbazaar_username"]) . "\" AND password = \"" . bin2hex($_SESSION["umbcbazaar_password"]). "\" AND status >= 10 LIMIT 1");
		if($row[0]["COUNT(*)"] > 0) {
			array_push($returnValue, $_SESSION["umbcbazaar_userid"]);
			array_push($returnValue, $_SESSION["umbcbazaar_username"]);
			array_push($returnValue, $_SESSION["umbcbazaar_password"]);
			array_push($returnValue, $_SESSION["umbcbazaar_umbcid"]);
			array_push($returnValue, $_SESSION["umbcbazaar_firstname"]);
			array_push($returnValue, $_SESSION["umbcbazaar_lastname"]);
		}
		else {
			// invalidate the session as they are no longer valid
			session_unset();
			session_destroy();
			session_start();
		}
		$db1 -> close();
	}
	// return the values
	return $returnValue;
}

?>