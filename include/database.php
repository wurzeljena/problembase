<?php
	// GENERAL SQL DATABASE INTERFACE
	interface SQLDatabase {
		function query(string $stmt) : SQLResult;
		function querySingle(string $stmt, bool $entire_row);

		// prepare statement: placeholders are $1, $2, ...; to be used in this order
		function prepare(string $stmt) : SQLStmt;
		function exec(string $stmt);

		function lastInsertRowid(string $table, string $col) : int;
		static function escape(?string $val) : ?string;
		static function boolean_statement(string $val) : string;

		function ftsCond(string $col, string $search) : string;

		function close();
	}

	// Constants for $type in SQLStmt::bind.
	define("SQLTYPE_NULL", 0);
	define("SQLTYPE_INTEGER", 1);
	define("SQLTYPE_FLOAT", 2);
	define("SQLTYPE_TEXT", 3);
	define("SQLTYPE_BLOB", 4);

	interface SQLStmt {
		function bind(string $param, $value, int $type);
		function exec() : SQLResult;
	}

	interface SQLResult {
		function fetchArray();
		function fetchAssoc();
	}

	// SQLITE3 IMPLEMENTATION
	class SQLiteDB implements SQLDatabase {
		public SQLite3 $db;

		function __construct(string $path) {
			$this->db = new SQLite3($path);
			$this->db->exec("PRAGMA foreign_keys=on;");
		}

		function query(string $stmt) : SQLResult {
			return new SQLiteRes($this->db->query($stmt));
		}

		function querySingle(string $stmt, bool $entire_row) {
			return $this->db->querySingle($stmt, $entire_row);
		}

		function prepare(string $stmt) : SQLStmt {
			$mod_stmt = preg_replace('/\$\d/', "?", $stmt);
			$obj = $this->db->prepare($mod_stmt);
			return new SQLiteStmt($obj);
		}

		function exec(string $stmt) {
			$this->db->exec($stmt);
		}

		function lastInsertRowID(string $table, string $col) : int {
			return $this->db->lastInsertRowID();
		}

		static function escape(?string $val) : ?string {
			return is_null($val) ? null : SQLite3::escapeString($val);
		}

		static function boolean_statement(string $val) : string {
			return $val;
		}

		function ftsCond(string $col, string $search) : string {
			return "$col MATCH '$search'";
		}

		function close() {
			$this->db->close();
		}
	}

	class SQLiteStmt implements SQLStmt {
		private SQLite3Stmt $stmt;
		static private $sqlite3_map = array(
			SQLTYPE_NULL => SQLITE3_NULL,
			SQLTYPE_INTEGER => SQLITE3_INTEGER,
			SQLTYPE_FLOAT => SQLITE3_FLOAT,
			SQLTYPE_TEXT => SQLITE3_TEXT,
			SQLTYPE_BLOB => SQLITE3_BLOB);

		function __construct(SQLite3Stmt $stmt) {
			$this->stmt = $stmt;
		}

		function bind(string $param, $value, int $type) {
			$this->stmt->bindValue($param, $value, self::$sqlite3_map[$type]);
		}

		function exec() : SQLResult {
			return new SQLiteRes($this->stmt->execute());
		}
	}

	class SQLiteRes implements SQLResult {
		private SQLite3Result $result;

		function __construct(SQLite3Result $result) {
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

		function __construct(string $desc) {
			$this->conn = pg_connect($desc) or die("Could not connect: " . pg_last_error());
		}

		function query(string $stmt) : SQLResult {
			return new PostgresResult(pg_query($this->conn, $stmt));
		}

		function querySingle(string $stmt, bool $entire_row) {
			$res = $this->query($stmt);
			if ($entire_row)
				return $res->fetchAssoc();
			else {
				$array = $res->fetchArray();
				return $array[0];
			}
		}

		function prepare(string $stmt) : SQLStmt {
			$hash = md5($stmt);
			pg_prepare($this->conn, $hash, $stmt);
			return new PostgresStmt($this->conn, $hash);
		}

		function exec(string $stmt) {
			pg_exec($this->conn, $stmt);
		}

		function lastInsertRowID(string $table, string $col) : int {
			$res = pg_query("SELECT currval(pg_get_serial_sequence('$table', '$col'))");
			$row = pg_fetch_row($res);
			return $row[0];
		}

		static function escape(?string $val) : ?string {
			return is_null($val) ? null : pg_escape_string($val);
		}

		static function boolean_statement(string $val) : string {
			return "(".$val.")::int";
		}

		function ftsCond(string $col, string $search) : string {
			return "to_tsvector('german', $col) @@ to_tsquery('german', '$search')";
		}

		function close() {
			pg_close($this->conn);
		}
	}

	class PostgresStmt implements SQLStmt {
		private $conn;
		private string $name;
		private $params = Array();

		function __construct($conn, string $name) {
			$this->conn = $conn;
			$this->name = $name;
		}

		function bind(string $param, $value, $type) {
			$this->params[$param] = $value;
		}

		function exec() : SQLResult {
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
