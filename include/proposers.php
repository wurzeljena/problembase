<?php
	function proposers_datalist($pb)
	{
		$proposers = $pb->query("SELECT DISTINCT name FROM proposers");

		print '<datalist id="proposers">';
		while($proposer = $proposers->fetchArray()) {
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
		while($proposer = $proposers->fetchAssoc())
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
		while(list($name, $location, $country) = $proposers->fetchArray())
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
				$insert .= ") VALUES ('{$pb->escape($proposer[$num])}', '{$pb->escape($location[$num])}'";
				if ($country[$num] != "")
					$insert .= ", '{$pb->escape($country[$num])}'";
				$insert .= ")";

				$pb->exec($insert);
				$proposer_id[$num] = $pb->lastInsertRowID("proposers", "id");
			}
		}

		return $proposer_id;
	}

	// answer to Ajax queries for proposers
	if (isset($_GET['prop_query'])) {
		include '../lib/master.php';
		$pb = load(LOAD_DB);
		$proposers = $pb->query("SELECT id, location, country FROM proposers WHERE name='{$pb->escape($_GET['prop_query'])}'");
		header("Content-Type: application/json");
		print "[";	$num = 0;
		while($proposer = $proposers->fetchAssoc())
			print (($num++ > 0) ? ", " : "").json_encode($proposer);
		print "]";
		$pb->close();
	}
?>
