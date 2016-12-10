<?php
session_start();
require("CommonMethods.php");

$userInfo = sessionInfo();
if(count($userInfo) > 0) {
	$db1 = new DBConnection;
	$query1 = "SELECT COUNT(*) FROM users where username = '" . bin2hex($_SESSION["umbcbazaar_username"]) . "' AND status >= 10 AND type >= 10 LIMIT 1";
	$row = $db1 -> select($query1);
	$db1 -> close();
	
	if($row[0]["COUNT(*)"] > 0) {
		$DBUSER="jguansi1";
		$DBPASSWD="qctagLw7jno6lTqWkDkI56T9KP3pDU@";
		$DATABASE="jguansi1";
		$SERVER = "studentdb-maria.gl.umbc.edu";
		$filename = "backup-" . date("Y-m-d") . ".sql";
		header( "Content-Type: text/plain");
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		$cmd = "mysqldump -h $SERVER -u $DBUSER --password=$DBPASSWD $DATABASE";   
		passthru( $cmd );
		exit(0);
	}
	else {
		// redirect to the error page
		header("Location: error.php");
		exit();
	}
}
else {
	// redirect to the login page
	header("Location: login.php");
	exit();
}
?>