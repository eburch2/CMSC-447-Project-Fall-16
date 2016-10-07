<?php
class DatabaseConnection {
	// global variable for connection
	var $connection = false;
	function DatabaseConnection() {
		echo "Connecting to database...";
		$this -> connect();
	}
	function __destruct() {
		echo "Closing databse connection";
		$this -> close();
	}
	function connect() {
		 // Try and connect to the database
        if($this -> $connection == false) {
            // Load configuration as an array. Use the actual location of your configuration file 
			$config = parse_ini_file("/afs/umbc.edu/users/j/g/jguansi1/pub/databaseConfig.ini"); 
			$this -> $connection = mysql_connect($config["hostname"], $config["username"], $config["password"], true);
        }
		// Note: There is no try catch exception handling in PHP 4.3
		if(!$this -> $connection) {
			echo "failed connection";
			// could not connect to the host with credentials
			exit();
		}
		if(!mysql_select_db($config["dbname"], $this -> $connection)) {
			echo "Failed database access";
			// could not connect to the database
			exit();
		}
		echo "Connected!";
        //return $connection;	       
    }
	function query($query) {
        // Connect to the database
		if($this -> $connection == false) {
			$this -> connect();
			echo "query connect to db";
		}
		else {
			echo "Db already connected!";
		}
		echo "query called";
        // Query the database
        //$result = mysql_query($query, $connection);
        //return $result;
    }
	function select($query) {
        $rows = array();
        $result = query($query);
        if($result === false) {
            return false;
        }
        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
	function close() {
		// close the database connection
		if($connection != false) {
			mysql_close($connection);
		}
	}
}
$ins1 = new DatabaseConnection;
$ins1 -> query("test");
//$conn = $ins1 -> connect();

?>