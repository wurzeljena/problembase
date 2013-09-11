<?php
	// start session and set flags
	session_start();
	if (!isset($_SESSION['user_id'])) {
		$_SESSION['user_id'] = -1;
		$_SESSION['root'] = $_SESSION['editor'] = 0;
	}

	// load includes and the database
	define("LOAD_DB",		1);
	define("INC_HEAD",		2);
	define("INC_PROPOSERS",	4);
	define("INC_TAGS",		8);
	define("INC_TASKLIST",	16);
	define("INC_SOLLIST",	32);

	// define root directories
	define("DOCROOT", $_SERVER['DOCUMENT_ROOT'].(isset($_ENV['PBROOT']) ? $_ENV['PBROOT'] : ""));
	define("WEBROOT", isset($_ENV['WEBROOT']) ? $_ENV['WEBROOT'] : "");

	function load($what) {
		define("MASTER_LOADED", true);;

		if ($what & INC_HEAD)		include DOCROOT."/include/head.php";
		if ($what & INC_PROPOSERS)	include DOCROOT."/include/proposers.php";
		if ($what & INC_TAGS)		include DOCROOT."/include/tags.php";
		if ($what & INC_TASKLIST)	include DOCROOT."/lib/view/tasklist.php";
		if ($what & INC_SOLLIST)	include DOCROOT."/lib/view/solutionlist.php";

		if ($what & LOAD_DB) {
			include DOCROOT."/include/database.php";
			return Problembase();
		}
	}
	
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
