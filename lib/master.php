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

	function load($what) {
		define("MASTER_LOADED", true);
		$root = $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'];

		if ($what & INC_HEAD)		include "$root/include/head.php";
		if ($what & INC_PROPOSERS)	include "$root/include/proposers.php";
		if ($what & INC_TAGS)		include "$root/include/tags.php";
		if ($what & INC_TASKLIST)	include "$root/lib/view/tasklist.php";
		if ($what & INC_SOLLIST)	include "$root/lib/view/solutionlist.php";

		if ($what & LOAD_DB) {
			include "$root/include/database.php";
			return Problembase();
		}
	}
	
	function http_error($code, $msg = null) {
		switch ($code) {
			case 403:
				include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/pages/error403.php';
				exit();
			case 404:
				$error = $msg;
				include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/pages/error404.php';
				exit();
		}
	}
?>
