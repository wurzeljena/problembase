<?php
	class Tag {
		// Named array containing the plain data
		// The color is stored in string format
		private array $data = array();
		private bool $writeable = false;

		// Construct from database data set
		function __construct(array $data = array()) {
			if (count($data)) {
				$this->data = $data;
				if (isset($this->data['color']))
					$this->data['color'] = "#".substr("00000".dechex($this->data['color']),-6);
			}
			else {
				$this->writeable = true;
			}
		}

		// Is it valid?
		function is_valid() : bool {
			return (bool)$this->data;
		}

		// Overwrite certain parameters, if allowed
		function set(array $new_data) : bool {
			// Do we have the rights?
			if (!$this->writeable)
				return false;

			if (!((!$new_data["private_user"] && $_SESSION["editor"]) ||
					$new_data["private_user"] == $_SESSION["user_id"]))
				return false;

			//  1) Tag can't be both hidden and private
			//  2) We are not allowed to overwrite/choose IDs
			if ($new_data["hidden"] && (bool)$new_data["private_user"] ||
					isset($new_data["id"]))
				return false;

			// Overwrite data
			$this->data = array_merge($this->data, $new_data);
			return true;
		}

		function from_name(SQLDatabase $pb, string $name, int $rights = ACCESS_READ) : void {
			$split = explode("/", $name);
			if (count($split) == 1)
				$private = false;
			else if (count($split) == 2 && $split[0] == "private") {
				$private = true;
				$name = $split[1];
			}
			else
				return;

			$query = $pb->query("SELECT * FROM tags WHERE name='{$pb->escape($name)}' "
				."AND ".($private ? "private_user={$_SESSION["user_id"]}" : "private_user ISNULL")
				.self::tag_restr($rights));
			$tag = $query->fetchAssoc();

			if ($tag)
				$this->__construct($tag);
			if ($rights == ACCESS_MODIFY)
				$this->writeable = true;
		}

		// Get list name
		function getName() : string {
			return $this->data["name"].($this->data["private_user"] != null ? "*" : "");
		}

		// Get URL name, as used for queries
		function getURLName() : string {
			$prefix = ($this->data["private_user"] != null) ? "private/" : "";
			return $prefix.str_replace(" ", "_", $this->data["name"]);
		}

		function json_encode() {
			if ($this->data) {
				$data = $this->data;
				$data["private"] = (bool)$data["private_user"];
				unset($data["private_user"]);
				return json_encode($data);
			}
			else
				return null;
		}

		// Print JS code to write tag into the DOM-variable named $taglist.
		function js(string $taglist) : string {
			return "$taglist.appendChild(writeTag(".$this->json_encode()."));\n";
		}

		// Write tag to database
		function write(SQLDatabase $pb) : void {
			if (!$this->writeable) return;

			// Sanitize data
			$tag = array_map(function($par) use ($pb) {return $pb->escape($par);}, $this->data);
			$tag["color"] = (int)hexdec(substr($tag["color"], -6));
			if (!$tag["private_user"])
				$tag["private_user"] = "null";

			if (!isset($tag["id"]))
				$pb->exec("INSERT INTO tags (name, description, color, hidden, private_user) "
					."VALUES ('{$tag["name"]}', '{$tag["description"]}', {$tag["color"]}, {$tag["hidden"]}, {$tag["private_user"]})");
			else
				$pb->exec("UPDATE tags SET name='{$tag["name"]}', description='{$tag["description"]}', "
					."color={$tag["color"]}, hidden={$tag["hidden"]}, private_user={$tag["private_user"]} WHERE id={$tag["id"]}");
		}
		function delete(SQLDatabase $pb) : bool {
			// private or not?
			if ($this->data["private_user"] != null) {
				if ($this->data["private_user"] != $_SESSION["user_id"])
					return false;
			}
			else {
				if (!$_SESSION["editor"])
					return false;
			}
			$pb->exec("PRAGMA foreign_keys=on");
			$pb->exec("DELETE FROM tags WHERE id={$this->data["id"]}");
			return true;
		}

		// Execute SQL statement with $1 = $id
		function exec_id(SQLStmt $stmt) : void {
			$stmt->bind(1, $this->data["id"], SQLTYPE_INTEGER);
			$stmt->exec();
		}

		// Set or unset for problem
		function set_for_file(SQLDatabase $pb, int $id, bool $set) : bool {
			// are we allowed to set the tag?
			if (!($_SESSION['editor'] || $this->data["private_user"] == $_SESSION["user_id"]))
				return false;
			if ($set)
				$pb->exec("INSERT INTO tag_list(problem_id, tag_id) VALUES ($id, {$this->data["id"]})");
			else
				$pb->exec("DELETE FROM tag_list WHERE problem_id=$id AND tag_id={$this->data["id"]}");
			return true;
		}

		// (Extra) condition to select only tags the current user is allowed to see
		static function tag_restr(int $rights, bool $standalone = false) : string {
			$cond = array();
			if ($rights & ACCESS_READ)
				$cond[] = "(private_user ISNULL OR private_user={$_SESSION["user_id"]})";
			if (($rights & ACCESS_READ) && !$_SESSION['editor'])
				$cond[] = "hidden=0";
			if (($rights & ACCESS_MODIFY) && $_SESSION['editor'])
				$cond[] = "private_user ISNULL OR private_user={$_SESSION["user_id"]}";
			if (($rights & ACCESS_MODIFY) && !$_SESSION['editor'])
				$cond[] = "private_user={$_SESSION["user_id"]}";

			// return collapsed conditions
			if (count($cond))
				return ($standalone ? "" : " AND ").implode(" AND ", $cond);
			else
				return "";
		}

		// SQL condition to find problems with this tag
		function filter_condition() : string {
			return "EXISTS (SELECT tag_id FROM tag_list WHERE problems.file_id=tag_list.problem_id "
				."and tag_list.tag_id={$this->data["id"]})";
		}

		// Get proposer statistic
		function proposer_statistic(SQLDatabase $pb, int $limit = 5) : ProposerList {
			$res = $pb->query("SELECT name, location, country, count(fileproposers.file_id) AS count_problems "
				."FROM proposers JOIN fileproposers ON proposers.id=fileproposers.proposer_id "
				."JOIN problems ON fileproposers.file_id=problems.file_id WHERE EXISTS "
				."(SELECT tag_id FROM tag_list WHERE tag_list.problem_id=problems.file_id "
				.($_SESSION["editor"] ? "" : "AND public=1 ")."AND tag_id={$this->data["id"]}) "
				."GROUP BY name, location, country ORDER BY count_problems DESC LIMIT $limit");

			// make ProposerList
			return new ProposerList($res);
		}

		// get count
		function problem_count() : int { return $this->data["count_problems"]; }
	}

	class TagList {
		// Array consisting of Tags
		private array $data = array();

		// Construct from query
		function __construct(?SQLResult $res = null) {
			if (!is_null($res))
				while($tag = $res->fetchAssoc())
					$this->data[] = new Tag($tag);
		}

		// Construct from a comma-separated list of names
		function from_list(SQLDatabase $pb, string $list) : void {
			foreach (explode(",", $list) as $name) {
				$tag = new Tag();
				if ($name == "") continue;
				$tag->from_name($pb, $name);
				$this->data[] = $tag;
			}
		}

		function get(SQLDatabase $pb, array $fields, int $rights = ACCESS_READ) : void {
			$tags = $pb->query("SELECT ".implode(", ", $fields)." FROM tags WHERE ".Tag::tag_restr($rights, true));
			$this->__construct($tags);
		}

		function from_file(SQLDatabase $pb, int $id) : void {
			$tags = $pb->query("SELECT name, description, color, hidden, private_user FROM tag_list JOIN tags "
				."ON tag_list.tag_id=tags.id WHERE problem_id=$id".Tag::tag_restr(ACCESS_READ));
			$this->__construct($tags);
		}

		function json_encode() : string {
			$json = array_map(function(Tag $tag) {return $tag->json_encode();}, $this->data);
			return "[".implode(", ", $json)."]";
		}

		// Print a comma-separated list of the quoted names
		function print_names() : string {
			$names = array_map(function(Tag $tag) {return "\"".$tag->getName()."\"";}, $this->data);
			return implode(",", $names);
		}

		// Print a HTML <select> element for tags with the tags as options
		function print_select(string $onchange, string $default, string $name = "") : void {
			print "<select ".(($name != "") ? "name = '$name' " : "")."onchange=\"$onchange\" value=''>";
			print "<option selected value=''>&mdash;$default&mdash;</option>";

			foreach ($this->data as $tag)
				print "<option value='{$tag->getURLName()}'>{$tag->getName()}</option>";

			print "</select>";
		}

		// Print commands writing the tags into the element given by the JavaScript
		// variable named $taglist. Eventually with spaces between tags.
		function js(string $taglist, bool $spaces = false) : string {
			$res = "";
			foreach ($this->data as $tag) {
				$res .= $tag->js($taglist);
				if ($spaces)
					$res .= "$taglist.appendChild(document.createTextNode(' '));";
			}
			return $res;
		}

		// Add the tags to file $id, and remove all others
		function set_for_file(SQLDatabase $pb, int $id) : void {
			$pb->exec("DELETE FROM tag_list WHERE problem_id=$id AND "
				."EXISTS (SELECT id FROM tags WHERE tag_list.tag_id=tags.id AND "
				."(private_user ISNULL OR private_user={$_SESSION["user_id"]}))");
			$stmt = $pb->prepare("INSERT INTO tag_list (problem_id, tag_id) VALUES ($id, $1)");
			foreach ($this->data as $tag)
				$tag->exec_id($stmt);
		}

		// Condition for filtering all problems with this taglist
		function filter_condition() : string {
			$cond = array();
			foreach ($this->data as $tag)
				$cond[] = $tag->filter_condition();
			return implode(" AND ", $cond);
		}

		// Print tag statistic
		function print_statistic() : void {
			if (!count($this->data))
				return;

			print "<div class='stat'>";
			$rows = array_map(
				function(Tag $tag) {
					return "<span class='stat_tag'></span>&times;{$tag->problem_count()}";
				}, $this->data);

			print implode(" ", $rows);

			// print script
			$count = -1;
			$js = array_map(function(Tag $tag) use (&$count)
				{ ++$count; return $tag->js("stat_tags[$count]"); }, $this->data);
			print "</div><script> var stat_tags = document.getElementsByClassName('stat_tag');";
			print implode("", $js);
			print "</script>";
		}
	}

	// Print the tag form
	function tag_form(SQLDatabase $pb, string $form, TagList $taglist) : void {
		$tags = new TagList;
		$tags->get($pb, array("name", "private_user"));
		$tags->print_select("tagList.add(this.value); this.value='';", "Tag hinzufügen");
		print "<input type='hidden' name='tags'/>";
		print "<span id='taglist'></span>";
		print "<script>var tagList = new TagList('$form', {$taglist->json_encode()});</script>";
	}
?>
