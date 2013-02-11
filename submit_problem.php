<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		if (is_string($value))
			$$key = $pb->escapeString($value);
		else
			$$key = $value;

	if (isset($delete)) {
		$pb->exec("DELETE FROM problems WHERE id=$id");
		header('Location: index.php');
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
			$pb->exec("UPDATE problems SET problem='$problem', proposer_id=$proposer_id, "
				."remarks='$remarks', proposed=date('$proposed') WHERE id=$id");
		else {
			$pb->exec("INSERT INTO problems(problem, proposer_id, remarks, proposed) VALUES "
				."('$problem', $proposer_id, '$remarks', date('$proposed'))");
			$id = $pb->lastInsertRowID();
		}

		// write tags
		$pb->exec("DELETE FROM tag_list WHERE problem_id=$id");
		if ($tags != "") {
			$stmt = $pb->prepare("INSERT OR REPLACE INTO tag_list (problem_id, tag_id) VALUES ($id, :tag)");
			foreach (explode(",", $tags) as $value) {
				$stmt->bindValue(":tag", $value, SQLITE3_INTEGER);
				$stmt->execute();
			}
		}

		// redirect to task.php
		header('Location: task.php?id='.$id);
	}

	$pb->close();
?>
