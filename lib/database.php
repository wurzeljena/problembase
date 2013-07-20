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
			return new SQLiteResult($this->db->query($stmt));
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
			return new SQLiteResult($this->stmt->execute());
		}
	}

	class SQLiteResult implements SQLResult {
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

	function Problembase() {
		return new SQLiteDB($_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/sqlite/problembase.sqlite');
	}
?>
