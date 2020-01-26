<?php
/*
* Mysqli database class
*/
class db {
	private $_connection;
	private static $_instance; 
	private $_host 		= "localhost";
	private $_username	= "username";
	private $_password	= "password";
	private $_database	= "database_name";

	/*
	Get an instance of the Database
	@return Instance
	*/
	public static function getInstance() {
	    if(!self::$_instance) {
		self::$_instance = new self();
	    }
	    return self::$_instance;
	}

	private function __construct() {
	    $this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
	    if(mysqli_connect_error()) {
	        exit("Failed to conencto to MySQL: " . mysql_connect_error());
	    }
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { }

	// Get mysqli connection
	public function getConnection() {
	    return $this->_connection;
	}
}
?>
