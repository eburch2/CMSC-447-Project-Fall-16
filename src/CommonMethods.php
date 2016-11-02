<?php

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
function encryptData($plaintext, $key) {
	// using SHA 512 hashing using the passed key -- 10000 rounds
	$rounds = '$6$rounds=10000$';
	$password = crypt($plaintext, $rounds . $key . '$');
	// remove the preceding values -- rounds and the salt key
	$password = str_replace($rounds . substr($key, 0, 16) . '$', '', $password);
	return $password;
}
function hashPassword($plaintext) {
	// the key for password hashing is stored in htaccess folder
	// using a 16 character salt
	$key = parse_ini_file("./config/key.ini");
	return encryptData($plaintext, $key["key"]);
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
		array_push($returnValue, $_SESSION["umbcbazaar_userid"]);
		array_push($returnValue, $_SESSION["umbcbazaar_username"]);
		array_push($returnValue, $_SESSION["umbcbazaar_password"]);
		array_push($returnValue, $_SESSION["umbcbazaar_umbcid"]);
		array_push($returnValue, $_SESSION["umbcbazaar_firstname"]);
		array_push($returnValue, $_SESSION["umbcbazaar_lastname"]);
	}
	// return the values
	return $returnValue;
}

?>