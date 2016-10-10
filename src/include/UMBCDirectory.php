<?php
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
function getStudentInfo($studentID) {
	// firstname, lastname, email
	$returnValue = array();
	// get the scraped UMBC directory webpage
	$scraped_website = curl("http://www.umbc.edu/search/directory/?search=" . $studentID);
	// placeholders for specific student info
	$placeholder1 = "<div class=\"name\" itemprop=\"name\">";
	$placeholder2 = "</div>";
	$placeholder3 = "<a itemprop=\"email\" href=\"mailto:";
	$placeholder4 = "\">";
	$index1 = strpos($scraped_website, $placeholder1);
	// a record is found matching the studentID
	if($index1 > 0) {
		$index1 += strlen($placeholder1);
		// get the end index of the student's name
		$index2 = strpos($scraped_website, $placeholder2, $index1);
		// substring the student's name from the scraped webpage
		$temp = substr($scraped_website, $index1, $index2 - $index1);
		// set firstname from the beginning of the string up to the last space
		array_push($returnValue, substr($temp, 0, strrpos($temp, " ")));
		// set the lastname from the index of the last space to the end
		array_push($returnValue, substr($temp, strrpos($temp, " ") + 1));
		// matching email address
		$index3 = strpos($scraped_website, $placeholder3, $index1);
		$index3 += strlen($placeholder3);
		// get the end index of the email
		$index4 = strpos($scraped_website, $placeholder4, $index3);
		// set the email address from the substring of the scraped webpage
		array_push($returnValue, substr($scraped_website, $index3, $index4 - $index3));
		// echo $returnValue[0] . "," . $returnValue[1] . "," . $returnValue[2];
	}
	return $returnValue;
}

?>