<?php
	// start session and set flags
	session_start();
	if (!isset($_SESSION['user_id'])) {
		$_SESSION['user_id'] = -1;
		$_SESSION['root'] = $_SESSION['editor'] = 0;
	}

	// load includes and the database
	define("LOAD_DB",          1);
	define("INC_HEAD",         2);
	define("INC_PROPOSERS",    4);
	define("INC_TAGS",         8);
	define("INC_PROBLEMS",    16 | INC_PROPOSERS | INC_TAGS);
	define("INC_SOLUTIONS",   32 | INC_PROPOSERS | INC_PROBLEMS);
	define("INC_EVALUATIONS", 64);

	// define root directories
	define("DOCROOT", $_SERVER['DOCUMENT_ROOT'].(isset($_ENV['PBROOT']) ? $_ENV['PBROOT'] : ""));
	define("WEBROOT", isset($_ENV['WEBROOT']) ? $_ENV['WEBROOT'] : "");

	// load includes, when needed
	function load($what) {
		define("MASTER_LOADED", true);

		if ($what & INC_HEAD)        include DOCROOT."/include/head.php";
		if ($what & INC_PROPOSERS)   include DOCROOT."/include/proposers.php";
		if ($what & INC_TAGS)        include DOCROOT."/include/tags.php";
		if ($what & INC_PROBLEMS)    include DOCROOT."/lib/view/problems.php";
		if ($what & INC_SOLUTIONS)   include DOCROOT."/lib/view/solutions.php";
		if ($what & INC_EVALUATIONS) include DOCROOT."/lib/view/evaluations.php";

		if ($what & LOAD_DB) {
			include DOCROOT."/include/database.php";
			return Problembase();
		}
	}

	// define access rights
	define("ACCESS_READ", 1);       // just reading something
	define("ACCESS_MODIFY", 2);     // modifying an existing object
	define("ACCESS_WRITE", 4);      // writing a new object

	// some parameters
	define("TASKS_PER_PAGE", 10);

	// throw a HTTP error
	function http_error($code, $msg = null) {
		switch ($code) {
			case 403:
				include DOCROOT."/pages/error403.php";
				exit();
			case 404:
				$error = $msg;
				include DOCROOT."/pages/error404.php";
				exit();
		}
	}
?>
