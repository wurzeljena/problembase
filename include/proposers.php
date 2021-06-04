<?php
	class Proposer {
		// Named array containing the data
		private $data;

		// Construct from array
		function __construct(array $data) {
			$this->data = $data;
		}

		// Get ID
		function get_id() : int { return $this->data["id"]; }

		// Print Name
		function get_name() : string {
			return htmlspecialchars($this->data["name"], ENT_QUOTES);
		}

		// Print Name and Location
		function to_string(bool $html) : string {
			if ($html) {
				$urlname = str_replace(" ", "_", $this->data['name']);
				$urllocation = str_replace(" ", "_", $this->data['location']);
				return "<a href='".WEBROOT."/proposers/$urlname'>{$this->data['name']}</a>, "
					."<a href='".WEBROOT."/proposers/$urlname/$urllocation'>{$this->data['location']}</a>"
					.(isset($this->data['country']) ? " ({$this->data['country']})" : "");
			}
			else {
				return "{$this->data['name']}, {$this->data['location']}"
					.(isset($this->data['country']) ? " ({$this->data['country']})" : "");
			}
		}

		// Return JSON
		function json_encode() : string { return json_encode($this->data); }

		// Write to database
		function write(SQLDatabase $pb) {
			// prepare data
			$data = array_map(function($par) use ($pb) {return $pb->escape($par);}, $this->data);

			// Update or insert?
			if (isset($data["id"])) {
				$update = "UPDATE proposers SET name='{$data["name"]}', location='{$data["location"]}'"
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
		function problem_count() : int { return $this->data["count_problems"]; }
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

		function get(SQLDatabase $pb, array $fields, ?string $name = null, ?string $location = null) {
			$proposers = $pb->query("SELECT ".implode(", ", $fields)." FROM proposers"
				.(is_null($name) ? "" : " WHERE name='{$pb->escape($name)}'")
				.(is_null($location) ? "" : " AND location='{$pb->escape($location)}'"));

			$this->__construct($proposers);
		}

		function from_file(SQLDatabase $pb, int $id) {
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

		function json_encode() : string {
			$json = array_map(function(Proposer $prop) {return $prop->json_encode();}, $this->data);
			return "[".implode(", ", $json)."]";
		}

		function to_string(?string $remarks, bool $html) : string {
			$props = array_map(function(Proposer $prop) use ($html)
				{return $prop->to_string($html);}, $this->data);
			$props = implode(" und ", $props);

			if ($props) {
				// if there is a ~ in the remarks, they serve as template
				if (!is_null($remarks) && strpos($remarks, "~") !== false)
					$res = str_replace("~", $props, $remarks);
				else
					$res = $props;
			}
			else    // print just the remarks, if there are no proposers
				$res = $remarks ?? "";

			// Return as HTML5 author information, if desired
			if ($html)
				return "<address>$res</address>";
			else
				return $res;
		}

		// Print proposer statistic
		function print_statistic() {
			$props = array_map(
				function(Proposer $prop) {
					$first = "<td>".$prop->to_string(true)."</td>";
					$second = "<td>".$prop->problem_count()."</td>";
					return $first.$second;
				}, $this->data);

			print "<table class='stat'>\n<thead><tr><th>Autor</th><th>Aufgaben</th></tr></thead>\n<tbody>\n<tr>";
			print implode("</tr><tr>", $props);
			print "</tr></tbody></table>";
		}

		function write(SQLDatabase $pb) {
			foreach ($this->data as &$proposer)
				$proposer->write($pb);
		}

		function set_for_file(SQLDatabase $pb, int $id) {
			$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
			$stmt = $pb->prepare("INSERT INTO fileproposers (file_id, proposer_id) VALUES ($id, $1)");
			foreach ($this->data as $proposer)
				$proposer->exec($stmt);
		}

		// Make tag statistic
		function tag_statistic(SQLDatabase $pb, int $limit = 5) : TagList {
			$ids = array_map(function(Proposer $prop) {return $prop->get_id();}, $this->data);
			$res = $pb->query("SELECT name, description, color, hidden, private_user, "
				."count(problems.file_id) AS count_problems "
				."FROM tag_list LEFT JOIN tags ON tags.id=tag_list.tag_id "
				."LEFT JOIN problems ON tag_list.problem_id=problems.file_id "
				."WHERE ".($_SESSION["editor"] ? "" : "public=1 AND ")
				."EXISTS (SELECT file_id FROM fileproposers "
				."WHERE fileproposers.file_id=problems.file_id AND "
				."fileproposers.proposer_id IN (".implode(",", $ids)."))"
				.Tag::tag_restr(ACCESS_READ)." GROUP BY id, name, description, "
				."color, hidden ORDER BY count_problems DESC LIMIT $limit");

			// create TagList
			return new TagList($res);
		}

		// How many are we?
		function count() : int { return count($this->data); }
	}

	// Print a datalist containing all names of proposers from the past
	function proposers_datalist(SQLDatabase $pb)
	{
		$proposers = new ProposerList;
		$proposers->get($pb, array("DISTINCT name"));
		$proposers->print_datalist();
	}

	// Print the proposer form for the problems and solutions pages
	function proposer_form(SQLDatabase $pb, string $form, ProposerList $proposers)
	{
		proposers_datalist($pb);
		print "<div id='proplist'><input type='hidden' name='propnums'/></div>";
		print "<input type='button' value='Autor hinzufügen' onclick='propForm.addProp();'/>";

		print "<script type='text/javascript'>";
		print "var propForm = new PropForm('$form', ";
		print $proposers->json_encode();
		print ");</script>";
	}
?>
