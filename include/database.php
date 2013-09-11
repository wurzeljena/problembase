<?php
	// GENERAL SQL DATABASE INTERFACE
	interface SQLDatabase {
		function query($stmt);
		function querySingle($stmt, $entire_row);

		// prepare statement: placeholders are $1, $2, ...; to be used in this order
		function prepare($stmt);
		function exec($stmt);

		function lastInsertRowid($table, $col);
		function escape($val);

		function ftsCond($col, $search);

		function close();
	}

	define("SQLTYPE_NULL", 0);
	define("SQLTYPE_INTEGER", 1);
	define("SQLTYPE_FLOAT", 2);
	define("SQLTYPE_TEXT", 3);
	define("SQLTYPE_BLOB", 4);

	interface SQLStmt {
		function bind($param, $value, $type);
		function exec();
	}

	interface SQLResult {
		function fetchArray();
		function fetchAssoc();
	}

	// SQLITE3 IMPLEMENTATION
	class SQLiteDB implements SQLDatabase {
		public $db;

		function __construct($path) {
			$this->db = new SQLite3($path);
			$this->db->exec("PRAGMA foreign_keys=on;");
		}

		function query($stmt) {
			return new SQLiteRes($this->db->query($stmt));
		}

		function querySingle($stmt, $entire_row) {
			return $this->db->querySingle($stmt, $entire_row);
		}

		function prepare($stmt) {
			$mod_stmt = preg_replace('/\$\d/', "?", $stmt);
			$obj = $this->db->prepare($mod_stmt);
			return new SQLiteStmt($obj);
		}

		function exec($stmt) {
			$this->db->exec($stmt);
		}

		function lastInsertRowID($table, $col) {
			return $this->db->lastInsertRowID();
		}

		function escape($val) {
			return $this->db->escapeString($val);
		}
		
		function ftsCond($col, $search) {
			return "$col MATCH '$search'";
		}

		function close() {
			$this->db->close();
		}
	}

	class SQLiteStmt implements SQLStmt {
		private $stmt;
		static private $sqlite3_map = array(
			SQLTYPE_NULL => SQLITE3_NULL,
			SQLTYPE_INTEGER => SQLITE3_INTEGER,
			SQLTYPE_FLOAT => SQLITE3_FLOAT,
			SQLTYPE_TEXT => SQLITE3_TEXT,
			SQLTYPE_BLOB => SQLITE3_BLOB);

		function __construct($stmt) {
			$this->stmt = $stmt;
		}

		function bind($param, $value, $type) {
			$this->stmt->bindValue($param, $value, self::$sqlite3_map[$type]);
		}

		function exec() {
			return new SQLiteRes($this->stmt->execute());
		}
	}

	class SQLiteRes implements SQLResult {
		private $result;

		function __construct($result) {
			$this->result = $result;
		}

		function fetchArray() {
			return $this->result->fetchArray(SQLITE3_NUM);
		}

		function fetchAssoc() {
			return $this->result->fetchArray(SQLITE3_ASSOC);
		}
	}

	// POSTGRESQL IMPLEMENTATION
	class PostgresDB implements SQLDatabase {
		public $conn;

		function __construct($desc) {
			$this->conn = pg_connect($desc) or die("Could not connect: " . pg_last_error());
		}

		function query($stmt) {
			return new PostgresResult(pg_query($this->conn, $stmt));
		}

		function querySingle($stmt, $entire_row) {
			$res = $this->query($stmt);
			if ($entire_row)
				return $res->fetchAssoc();
			else {
				$array = $res->fetchArray();
				return $array[0];
			}
		}

		function prepare($stmt) {
			$hash = md5($stmt);
			pg_prepare($this->conn, $hash, $stmt);
			return new PostgresStmt($this->conn, $hash);
		}

		function exec($stmt) {
			pg_exec($this->conn, $stmt);
		}

		function lastInsertRowID($table, $col) {
			$res = pg_query("SELECT currval(pg_get_serial_sequence('$table', '$col'))");
			$row = pg_fetch_row($res);
			return $row[0];
		}

		function escape($val) {
			return pg_escape_string($val);
		}
		
		function ftsCond($col, $search) {
			return "to_tsvector('german', $col) @@ to_tsquery('german', '$search')";
		}

		function close() {
			pg_close($this->conn);
		}
	}

	class PostgresStmt implements SQLStmt {
		private $conn;
		private $name;
		private $params = Array();

		function __construct($conn, $name) {
			$this->conn = $conn;
			$this->name = $name;
		}

		function bind($param, $value, $type) {
			$this->params[$param] = $value;
		}

		function exec() {
			return new PostgresResult(pg_execute($this->conn, $this->name, $this->params));
		}
	}

	class PostgresResult implements SQLResult {
		private $result;

		function __construct($result) {
			$this->result = $result;
		}

		function fetchArray() {
			return pg_fetch_row($this->result);
		}

		function fetchAssoc() {
			return pg_fetch_assoc($this->result);
		}
	}

	function Problembase() {
		if (isset($_ENV['DATABASE_URL'])) {
			$cred = parse_url($_ENV['DATABASE_URL']);
			$db_name = basename($cred['path']);
			return new PostgresDB("host={$cred['host']} dbname=$db_name port=5432 user={$cred['user']} "
				."password={$cred['pass']}".($cred['host']=="localhost" ? "" : " sslmode=require")." options='--client_encoding=UTF8'");
		}
		else
			return new SQLiteDB(DOCROOT."/sql/problembase.sqlite");
	}
?>
