<?php
header('Content-Type: application/json');
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
require("UMBCDirectory.php");
// get the student id
$studentID = "";
if(!empty($_GET["studentID"])) {
	$studentID = trim(strtoupper($_GET["studentID"]));
}
// format the output as an array that JavaScript can parse
echo "[";
if (strlen($studentID) == 7) {
	$info = getStudentInfo($studentID);
	// must be exactly 3 elements: firstname, lastname and email
	if(count($info) > 0) {
		echo "\"" . $info[0] . "\",\"" . $info[1] . "\",\"" . $info[2] . "\",\"" . $info[3] . "\"";
	}
}
echo "]";
?>