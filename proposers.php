<?php
	function proposers_datalist($pb)
	{
		$proposers = $pb->query("SELECT DISTINCT name FROM proposers");

		print '<datalist id="proposers">';
		while($proposer = $proposers->fetchArray(SQLITE3_NUM)) {
			print '<option value="'.htmlspecialchars($proposer[0]).'">';
		}
		print '</datalist>';
	}

	function proposer_form($pb, $form, $type, $type_id)
	{
		$proposers = $pb->query("SELECT id, name, location, country FROM {$type}proposers "
			."JOIN proposers ON {$type}proposers.proposer_id=proposers.id WHERE {$type}_id=$type_id");

		proposers_datalist($pb);
		print "<div id='proplist'><input type='hidden' name='propnums'/></div>";
		print "<input type='button' value='Autor hinzuf&uuml;gen' onclick='propForm.addProp();'/>";

		print "<script type='text/javascript'>";
		print "var propForm = new PropForm('$form', [";
		$num = 0;
		while($proposer = $proposers->fetchArray(SQLITE3_ASSOC))
			print (($num++ > 0) ? ", " : "").json_encode($proposer);
		print "]);</script>";
	}

	function printproposers($pb, $type, $id)
	{
		// get proposers
		$proposers = $pb->query("SELECT name, location, country FROM {$type}proposers "
			."JOIN proposers ON {$type}proposers.proposer_id=proposers.id WHERE {$type}_id=$id");

		// print their list - or remarks, if there are none
		$first = true;
		while(list($name, $location, $country) = $proposers->fetchArray(SQLITE3_NUM)) {
			if (!$first)
				print " und ";
			else
				$first = false;
			print "$name, $location";
			if (isset($country))
				print " ($country)";
		}

		if ($first)
			print $pb->querySingle("SELECT remarks FROM {$type}s WHERE id=$id", false);
	}

	function writeproposers($pb, $nums, $proposer, $proposer_id, $location, $country) {
		foreach ($nums as $num) {
			if ($proposer_id[$num] == "-1") {
				$insert = "INSERT INTO proposers (name, location";
				if ($country[$num] != "")
					$insert .= ", country";
				$insert .= ") VALUES ('{$pb->escapeString($proposer[$num])}', '{$pb->escapeString($location[$num])}'";
				if ($country[$num] != "")
					$insert .= ", '{$pb->escapeString($country[$num])}'";
				$insert .= ")";

				$pb->exec($insert);
				$proposer_id[$num] = $pb->lastInsertRowID();
			}
		}

		return $proposer_id;
	}

	// answer to Ajax queries for proposers
	if (isset($_REQUEST['prop_query'])) {
		$pb = new SQLite3('sqlite/problembase.sqlite');
		$proposers = $pb->query("SELECT id, location, country FROM proposers WHERE name='".$pb->escapeString($_REQUEST['prop_query'])."'");
		header("Content-Type: application/json");
		print "[";	$num = 0;
		while($proposer = $proposers->fetchArray(SQLITE3_ASSOC))
			print (($num++ > 0) ? ", " : "").json_encode($proposer);
		print "]";
		$pb->close();
	}
?>
