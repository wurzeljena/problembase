<?php
	class ProposerList {
		// Array consisting of named arrays which represent the proposers.
		private $data = array();

		function fromserialdata(array $nums, array $proposer, array $proposer_id,
				array $location, array $country) {
			foreach ($nums as $num) {
				$cur = array("name" => $proposer[$num],
					"location" => $location[$num], "country" => $country[$num]);
				if ($proposer_id[$num] != "-1")
					$cur["id"] = $proposer_id[$num];
				$this->data[] = $cur;
			}
		}

		function get(SQLDatabase $pb, array $fields, $name=null) {
			$proposers = $pb->query("SELECT ".implode(", ", $fields)." FROM proposers"
				.($name ? " WHERE name='{$pb->escape($name)}'" : ""));

			while($proposer = $proposers->fetchAssoc())
				$this->data[] = $proposer;
		}

		function from_file(SQLDatabase $pb, $id) {
			$proposers = $pb->query("SELECT id, name, location, country FROM fileproposers "
				."JOIN proposers ON fileproposers.proposer_id=proposers.id WHERE file_id=$id");
			while($proposer = $proposers->fetchAssoc())
				$this->data[] = $proposer;
		}

		function print_datalist() {
			print '<datalist id="proposers">';
			foreach ($this->data as $proposer)
				print '<option value="'.htmlspecialchars($proposer["name"]).'">';
			print '</datalist>';
		}

		function json_encode() {
			$json = array_map("json_encode", $this->data);
			return "[".implode(", ", $json)."]";
		}

		function print_list($remarks) {
			$props = array();
			foreach ($this->data as $proposer)
				$props[] = "{$proposer['name']}, {$proposer['location']}"
					.(isset($proposer['country']) ? " ({$proposer['country']})" : "");
			$props = implode(" und ", $props);

			if ($props) {
				// if there is a ~ in the remarks, they serve as template
				if (strpos($remarks, "~") !== false)
					print str_replace("~", $props, $remarks);
				else
					print $props;
			}
			else    // print just the remarks, if there are no proposers
				print $remarks;
		}

		function write(SQLDatabase $pb) {
			foreach ($this->data as &$proposer) {
				if (!isset($proposer["id"])) {
					$insert = "INSERT INTO proposers (name, location";
					if ($proposer["country"] != "")
						$insert .= ", country";
					$insert .= ") VALUES ('{$pb->escape($proposer['name'])}', '{$pb->escape($proposer['location'])}'";
					if ($country[$num] != "")
						$insert .= ", '{$pb->escape($proposer['country'])}'";
					$insert .= ")";

					$pb->exec($insert);
					$proposer["id"] = $pb->lastInsertRowID("proposers", "id");
				}
			}
		}

		function set_for_file(SQLDatabase $pb, $id) {
			$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
			$stmt = $pb->prepare("INSERT INTO fileproposers (file_id, proposer_id) VALUES ($id, $1)");
			foreach ($this->data as $proposer) {
				$stmt->bind(1, $proposer["id"], SQLTYPE_INTEGER);
				$stmt->exec();
			}
		}
	}

	// Print a datalist containing all names of proposers from the past
	function proposers_datalist(SQLDatabase $pb)
	{
		$proposers = new ProposerList;
		$proposers->get($pb, array("DISTINCT name"));
		$proposers->print_datalist();
	}

	// Print the proposer form for the problems and solutions pages
	function proposer_form(SQLDatabase $pb, $form, $file_id)
	{
		proposers_datalist($pb);
		print "<div id='proplist'><input type='hidden' name='propnums'/></div>";
		print "<input type='button' value='Autor hinzuf&uuml;gen' onclick='propForm.addProp();'/>";

		print "<script type='text/javascript'>";
		print "var propForm = new PropForm('$form', ";
		$proposers = new ProposerList;
		$proposers->from_file($pb, $file_id);
		print $proposers->json_encode();
		print ");</script>";
	}
?>
