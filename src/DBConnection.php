<?php

class DBConnection {
	// PHP 4.3 standard does not have any public, private, protected variables
	// global variable for the class
	var $connection = false;
	function DBConnection() {
		// constructor
		$this -> connect();
	}
	function __destruct() {
		// destructor
		$this -> close();
	}
	function connect() {
		 // Try and connect to the database
        if($this -> $connection == false) {
            // Load configuration file
			// $config = parse_ini_file("./config/databaseConfig.ini");
			$config = array("hostname" => "studentdb-maria.gl.umbc.edu", "username" => "jguansi1", "password" => "Lupanghinirang@123", "dbname" => "jguansi1");
			$this -> $connection = mysql_connect($config["hostname"], $config["username"], $config["password"], true);
        }
		// There are no try catch exception handling in PHP 4.3
		if(!$this -> $connection) {
			echo "failed host connection";
			exit();
		}
		if(!mysql_select_db($config["dbname"], $this -> $connection)) {
			echo "Failed database access";
			exit();
		}    
    }
	function query($query) {
        // Connect to the database
		if($this -> $connection == false) {
			$this -> connect();
		}
        $result = mysql_query($query, $this -> $connection);
        return $result;
    }
	function select($query) {
        $rows = array();
        $result = $this -> query($query);
        if($result === false) {
            return $rows;
        }
		if (mysql_num_rows($result) > 0) {
			 while($row = mysql_fetch_assoc($result)) {
				array_push($rows, $row);
			}
		}
        return $rows;
    }
	function close() {
		// close the database connection
		if($this -> $connection != false) {
			mysql_close($this -> $connection);
		}
	}
}
//$ins1 = new DBConnection;
//$ins1 -> query("test");
//$conn = $ins1 -> connect();

?>