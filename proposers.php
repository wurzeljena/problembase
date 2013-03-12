<?php
	function proposers_datalist($pb)
	{
		$proposers = $pb->query("SELECT name FROM proposers");
		
		print '<datalist id="proposers">';
		while($proposer = $proposers->fetchArray(SQLITE3_NUM)) {
			print '<option value="'.$proposer[0].'">';
		}
		print '</datalist>';
	}

	function proposer_form($pb, $form, $proposer_id)
	{
		$name = $location = $country = '';
		if (isset($proposer_id) && $proposer_id != -1) {
			$proposer = $pb->querySingle("SELECT * FROM proposers WHERE id=".$proposer_id, true);
			foreach ($proposer as $key=>$value)
				$$key = $value;
		}

		print "<input type='text' class='text' id='proposer' name='proposer' list='proposers' required "
			."placeholder='Einsender' style='width:165px;' value='$name' onblur='queryProp(\"$form\");'/>"
			."<input type='hidden' name='proposer_id' value='$proposer_id'>"
			."<input type='text' class='text' name='location' value='$location' required "
			."placeholder='Ort' style='width:110px;'/>"
			."<input type='text' class='text' name='country' value='$country' "
			."placeholder='Land' style='width:245px;'/> <br/>";
		proposers_datalist($pb);
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

	// answer to Ajax queries for proposers
	if (isset($_REQUEST['prop_query'])) {
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		$res = $pb->query("SELECT id, location, country FROM proposers WHERE name='".$pb->escapeString($_REQUEST['prop_query'])."'")
			->fetchArray(SQLITE3_ASSOC);
		print json_encode($res);
		$pb->close();
	}
?>
