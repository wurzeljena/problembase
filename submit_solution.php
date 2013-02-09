<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		if (is_string($value))
			$$key = $pb->escapeString($value);
		else
			$$key = $value;

	if (isset($delete)) {
		$problem_id = $pb->querySingle("SELECT problem_id FROM solutions WHERE id=$id", false);
		$pb->exec("DELETE FROM solutions WHERE id=$id");
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
		if (isset($id))
			$pb->exec("UPDATE solutions SET solution='$solution', proposer_id=$proposer_id WHERE id=$id");
		else {
			$pb->exec("INSERT INTO solutions(problem_id, solution, proposer_id) VALUES ($problem_id, '$solution', $proposer_id)");
			$id = $pb->lastInsertRowID();
		}
	}

	$pb->close();

	// redirect to task.php
	header('Location: task.php?id='.$problem_id);
?>
