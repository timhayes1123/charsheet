<?php

class dbconn {
	#### Database connection object
    private static $connObj;
    private $db;

    public static function getConnectionBuild() {
        if (!self::$connObj) {
            self::$connObj = new dbconn();
        }
        return self::$connObj;
    }

    public function getConnection() {
		$servername = "localhost";
		$username = "webappuser";
		$password = "**********";
		$dbName = "newschema";
    try {
			if (!$this->db) {
				$this->db = new PDO("mysql:host=$servername;dbname=$dbName", $username, $password);
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
		} catch(PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
      return $this->db;
    }
}

?>
