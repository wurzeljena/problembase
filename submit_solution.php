<?php
	session_start();
	if (!isset($_SESSION['user_id']))
		die("&Auml;nderungen nur angemeldet m&ouml;glich!");
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $pb->escapeString($value);

	if (isset($delete)) {
		$file_id = $pb->querySingle("SELECT file_id FROM solutions WHERE id=$id");
		$problem_id = $pb->querySingle("SELECT problem_id FROM solutions WHERE id=$id", false);
		$pb->exec("DELETE FROM solutions WHERE id=$id");
		$pb->exec("DELETE FROM files WHERE files.rowid=$file_id");
	}
	else {
		// write proposer
		if (!isset($proposer_id)) {
			$insert = "INSERT INTO proposers (name, location";
			if ($country != "")
				$insert .= ", country";
			$insert .= ") VALUES ('$proposer', '$location'";
			if ($country != "")
				$insert .= ", '$country'";
			$insert .= ")";

			$pb->exec($insert);
			$proposer_id = $pb->lastInsertRowID();
		}

		// write into db
		if ($published != "") {
			list($month, $year) = explode("/", $published);
			if ($year > 50)		// translate YY to 19JJ/20JJ
				$year += 1900;
			if ($year <= 50)
				$year += 2000;
		}
		else
			$month = $year = "NULL";
		if (isset($id)) {
			$file_id = $pb->querySingle("SELECT file_id FROM solutions WHERE id=$id");
			$pb->exec("UPDATE files SET content='$solution' WHERE rowid=$file_id");
			$pb->exec("UPDATE solutions SET proposer_id=$proposer_id, "
				."remarks='$remarks', year=$year, month=$month WHERE id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('$solution')");
			$file_id = $pb->lastInsertRowID();
			$pb->exec("INSERT INTO solutions(problem_id, file_id, proposer_id, remarks, year, month) "
				."VALUES ($problem_id, $file_id, $proposer_id, '$remarks', $year, $month)");
		}
	}

	$pb->close();

	// redirect to task.php
	header('Location: task.php?id='.$problem_id);
?>
