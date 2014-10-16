<?php
	class Proposer {
		// Named array containing the data
		private $data;

		// Construct from array
		function __construct(array $data) {
			$this->data = $data;
		}

		// Print Name
		function get_name() {
			return htmlspecialchars($this->data["name"]);
		}

		// Print Name and Location
		function to_string() {
			return "{$this->data['name']}, {$this->data['location']}"
				.(isset($this->data['country']) ? " ({$this->data['country']})" : "");
		}

		// Return JSON
		function json_encode() { return json_encode($this->data); }

		// Write to database
		function write(SQLDatabase $pb) {
			// prepare data
			$data = array_map(function($par) use ($pb) {return $pb->escape($par);}, $this->data);

			// Update or insert?
			if (isset($data["id"])) {
				$update = "UPDATE proposers name='{$data["name"]}', location='{$data["location"]}'"
					.($data["country"] != "" ? ", country='{$data["country"]}'": "")." WHERE id={$data["id"]}";

				$pb->exec($update);
			}
			else {
				$insert = "INSERT INTO proposers (name, location, country) VALUES "
					."('{$data["name"]}', '{$data["location"]}', "
					.($data["country"] != "" ? "'{$data["country"]}'": "null").")";

				$pb->exec($insert);
				$this->data["id"] = $pb->lastInsertRowID("proposers", "id");
			}
		}

		// execute statement for proposer
		function exec(SQLStmt $stmt) {
			$stmt->bind(1, $this->data["id"], SQLTYPE_INTEGER);
			$stmt->exec();
		}

		// get count
		function count() { return $this->data["count_problems"]; }
	}

	class ProposerList {
		// Array consisting of Proposer objects.
		private $data = array();

		// Construct from SQL query
		function __construct(SQLResult $res = null) {
			if ($res)
				while($proposer = $res->fetchAssoc())
					$this->data[] = new Proposer($proposer);
		}

		function fromserialdata(array $nums, array $proposer, array $proposer_id,
				array $location, array $country) {
			foreach ($nums as $num) {
				$cur = array("name" => $proposer[$num],
					"location" => $location[$num], "country" => $country[$num]);
				if ($proposer_id[$num] != "-1")
					$cur["id"] = $proposer_id[$num];
				$this->data[] = new Proposer($cur);
			}
		}

		function get(SQLDatabase $pb, array $fields, $name=null) {
			$proposers = $pb->query("SELECT ".implode(", ", $fields)." FROM proposers"
				.($name ? " WHERE name='{$pb->escape($name)}'" : ""));

			$this->__construct($proposers);
		}

		function from_file(SQLDatabase $pb, $id) {
			$proposers = $pb->query("SELECT id, name, location, country FROM fileproposers "
				."JOIN proposers ON fileproposers.proposer_id=proposers.id WHERE file_id=$id");

			$this->__construct($proposers);
		}

		function print_datalist() {
			print "<datalist id='proposers'>";
			foreach ($this->data as $proposer)
				print "<option value='{$proposer->get_name()}'>";
			print "</datalist>";
		}

		function json_encode() {
			$json = array_map(function(Proposer $prop) {return $prop->json_encode();}, $this->data);
			return "[".implode(", ", $json)."]";
		}

		function print_list($remarks) {
			$props = array_map(function(Proposer $prop) {return $prop->to_string();}, $this->data);
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

		// Print proposer statistic
		function print_statistic() {
			$props = array_map(
				function(Proposer $prop) {
					$first = "<td>".$prop->to_string()."</td>";
					$second = "<td>".$prop->count()."</td>";
					return $first.$second;
				}, $this->data);

			print "<table class='stat'>\n<thead><tr><th>Autor</th><th>Aufgaben</th></tr></thead>\n<tbody>\n<tr>";
			$props = implode("</tr><tr>", $props);
			print $props;
			print "</tr></tbody></table>";
		}

		function write(SQLDatabase $pb) {
			foreach ($this->data as &$proposer)
				$proposer->write($pb);
		}

		function set_for_file(SQLDatabase $pb, $id) {
			$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
			$stmt = $pb->prepare("INSERT INTO fileproposers (file_id, proposer_id) VALUES ($id, $1)");
			foreach ($this->data as $proposer)
				$proposer->exec($stmt);
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
