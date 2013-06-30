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

	function proposer_form($pb, $form, $file_id)
	{
		$proposers = $pb->query("SELECT id, name, location, country FROM fileproposers "
			."JOIN proposers ON fileproposers.proposer_id=proposers.id WHERE file_id=$file_id");

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
		// get proposers (and remarks)
		$proposers = $pb->query("SELECT name, location, country FROM fileproposers "
			."JOIN proposers ON fileproposers.proposer_id=proposers.id WHERE file_id=$id");
		$remarks = $pb->querySingle("SELECT remarks FROM {$type}s WHERE file_id=$id", false);

		// create their list
		$props = array();
		while(list($name, $location, $country) = $proposers->fetchArray(SQLITE3_NUM))
			$props[] = "$name, $location".(isset($country) ? " ($country)" : "");
		$props = implode(" und ", $props);

		if ($props) {
			// if there is a ~ in the remarks, they serve as template
			if (strpos($remarks, "~") !== false)
				print str_replace("~", $props, $remarks);
			else
				print $props;
		}
		else		// print just the remarks, if there are no proposers
			print $remarks;
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
	if (isset($_GET['prop_query'])) {
		$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
		$proposers = $pb->query("SELECT id, location, country FROM proposers WHERE name='{$pb->escapeString($_GET['prop_query'])}'");
		header("Content-Type: application/json");
		print "[";	$num = 0;
		while($proposer = $proposers->fetchArray(SQLITE3_ASSOC))
			print (($num++ > 0) ? ", " : "").json_encode($proposer);
		print "]";
		$pb->close();
	}
?>
