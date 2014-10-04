<?php
	class Tag {
		// Named array containing the plain data
		// The color is stored in string format
		private $data = array();
		private static $name_stmt = null;

		// Construct from database data set
		function __construct(array $data = array()) {
			$this->data = $data;
			if (isset($this->data['color']))
				$this->data['color'] = "#".substr("00000".dechex($this->data['color']),-6);
		}

		// Overwrite certain parameters
		function set(array $new_data) {
			$this->data = array_merge($this->data, $new_data);
		}

		static function prepare_name_query(SQLDatabase $pb) {
			self::$name_stmt = $pb->prepare("SELECT * FROM tags WHERE name=$1".self::tag_restr(ACCESS_READ));
		}

		function from_name($name) {
			self::$name_stmt->bind(1, $name, SQLTYPE_TEXT);
			$tag = self::$name_stmt->exec()->fetchAssoc();
			if ($tag)
				$this->__construct($tag);
		}

		function getName() {return $this->data["name"];}
		function getURLName() {return str_replace(" ", "_", $this->data["name"]);}

		function json_encode() {
			return $this->data ? json_encode($this->data) : null;
		}

		// Print JS code to write tag into the DOM-variable named $taglist.
		function js($taglist) {
			return "$taglist.appendChild(writeTag(".json_encode($this->data)."));\n";
		}

		// Write tag to database
		function write(SQLDatabase $pb) {
			// Do we have the rights?
			if (!$_SESSION['editor'])
				return false;

			// Sanitize data
			$tag = array_map(function($par) use ($pb) {return $pb->escape($par);}, $this->data);
			$tag["color"] = (int)hexdec(substr($tag["color"], -6));

			if (!isset($tag["id"]))
				$pb->exec("INSERT INTO tags (name, description, color, hidden) "
					."VALUES ('{$tag["name"]}', '{$tag["description"]}', {$tag["color"]}, {$tag["hidden"]})");
			else
				$pb->exec("UPDATE tags SET name='{$tag["name"]}', description='{$tag["description"]}', "
					."color={$tag["color"]}, hidden={$tag["hidden"]} WHERE id={$tag["id"]}");
			return true;
		}
		function delete(SQLDatabase $pb) {
			if (!$_SESSION['editor'])
				return false;
			$pb->exec("PRAGMA foreign_keys=on");
			$pb->exec("DELETE FROM tags WHERE id={$this->data["id"]}");
			return true;
		}

		// Execute SQL statement with $1 = $id
		function exec_id(SQLStmt $stmt) {
			$stmt->bind(1, $this->data["id"], SQLTYPE_INTEGER);
			$stmt->exec();
		}

		// Set or unset for problem
		function set_for_file(SQLDatabase $pb, $id, $set) {
			if ($set)
				$pb->exec("INSERT INTO tag_list(problem_id, tag_id) VALUES ($id, {$this->data["id"]})");
			else
				$pb->exec("DELETE FROM tag_list WHERE problem_id=$id AND tag_id={$this->data["id"]}");
		}

		// (Extra) condition to select only tags the current user is allowed to see
		static function tag_restr($rights) {
			$cond = array();
			if (($rights & ACCESS_READ) && !$_SESSION['editor'])
				$cond[] = "hidden=0";
			if (($rights & ACCESS_MODIFY) && !$_SESSION['editor'])
				$cond[] = "0";		// = false

			// return collapsed conditions
			if (count($cond))
				return " AND ".implode(" AND ", $cond);
			else
				return "";
		}

		// SQL condition to find problems with this tag
		function filter_condition() {
			return "EXISTS (SELECT tag_id FROM tag_list WHERE problems.file_id=tag_list.problem_id "
				."and tag_list.tag_id={$this->data["id"]})";
		}
	}

	class TagList {
		// Array consisting of Tags
		private $data = array();

		// Construct from a comma-separated list of names
		function from_list(SQLDatabase $pb, $list) {
			Tag::prepare_name_query($pb);
			foreach (explode(",", $list) as $name) {
				$tag = new Tag();
				if ($name == "") continue;
				$tag->from_name($name);
				$this->data[] = $tag;
			}
		}

		function get(SQLDatabase $pb, array $fields) {
			$tags = $pb->query("SELECT ".implode(", ", $fields)." FROM tags WHERE 1".Tag::tag_restr(ACCESS_READ));
			while($tag = $tags->fetchAssoc())
				$this->data[] = new Tag($tag);
		}

		function from_file(SQLDatabase $pb, $id) {
			$tags = $pb->query("SELECT name, description, color, hidden FROM tag_list JOIN tags"
				." ON tag_list.tag_id=tags.id WHERE problem_id=$id".Tag::tag_restr(ACCESS_READ));
			while($tag = $tags->fetchAssoc())
				$this->data[] = new Tag($tag);
		}

		function json_encode() {
			$json = array_map(function(Tag $tag) {return $tag->json_encode();}, $this->data);
			return "[".implode(", ", $json)."]";
		}

		// Print a comma-separated list of the quoted names
		function print_names() {
			$names = array_map(function(Tag $tag) {return "\"".$tag->getName()."\"";}, $this->data);
			return implode(",", $names);
		}

		// Print a HTML <select> element for tags with the tags as options
		function print_select($onchange, $default, $name = "") {
			print "<select ".(($name != "") ? "name = '$name' " : "")."onchange=\"$onchange\" value=''>";
			print "<option selected value=''>&mdash;$default&mdash;</option>";

			foreach ($this->data as $tag)
				print "<option value='{$tag->getURLName()}'>{$tag->getName()}</option>";

			print "</select>";
		}

		// Print commands writing the tags into the element given by the JavaScript
		// variable named $taglist. Eventually with spaces between tags.
		function js($taglist, $spaces = false) {
			$res = "";
			foreach ($this->data as $tag) {
				$res .= $tag->js($taglist);
				if ($spaces)
					$res .= "$taglist.appendChild(document.createTextNode(' '));";
			}
			return $res;
		}

		// Add the tags to file $id, and remove all others
		function set_for_file(SQLDatabase $pb, $id) {
			$pb->exec("DELETE FROM tag_list WHERE problem_id=$id");
			$stmt = $pb->prepare("INSERT INTO tag_list (problem_id, tag_id) VALUES ($id, $1)");
			foreach ($this->data as $tag)
				$tag->exec_id($stmt);
		}

		// Condition for filtering all problems with this taglist
		function filter_condition() {
			$cond = array();
			foreach ($this->data as $tag)
				$cond[] = $tag->filter_condition();
			return implode(" AND ", $cond);
		}
	}

	// Print the tag form
	function tag_form(SQLDatabase $pb, $form, TagList $taglist) {
		$tags = new TagList;
		$tags->get($pb, array("name"));
		$tags->print_select("tagList.add(this.value); this.value='';", "Tag hinzuf&uuml;gen");
		print "<input type='hidden' name='tags'/>";
		print "<span id='taglist'></span>";
		print "<script>var tagList = new TagList('$form', {$taglist->json_encode()});</script>";
	}

	function tag_selector(SQLDatabase $pb, TagList $tags, $problem_id) {
		// create empty div for tags
		print "<div class='tag_selector'><i class='icon-tags'></i></div>";

		// initial script to print and mark the right ones
		print "<script> var tagSelector = document.getElementsByClassName('tag_selector')[0];";
		$all_tags = new TagList;
		$all_tags->get($pb, array("*", "(name IN ({$tags->print_names()})) AS active", "1 AS enabled", "$problem_id as problem"));
		print $all_tags->js("tagSelector", true);
		print "</script>";
	}

	// Answer to Ajax queries for tags
	if (isset($_GET['name'])) {
		include '../lib/master.php';
		$pb = load(LOAD_DB);

		$tag = new Tag;
		Tag::prepare_name_query($pb);
		$tag->from_name(str_replace("_", " ", $_GET['name']));
		$res = $tag->json_encode();
		if ($res) {
			header("Content-Type: application/json");
			print $res;
		}
		else
			http_error(404, "Tag nicht gefunden");

		$pb->close();
	}

	// Write tag from tag form
	if (isset($_POST['old_name'])) {
		include '../lib/master.php';
		$pb = load(LOAD_DB);

		$tag = new Tag;
		Tag::prepare_name_query($pb);
		if ($_POST["old_name"] != "")
			$tag->from_name(str_replace("_", " ", $_POST["old_name"]));

		if (isset($_POST['delete']))
			$success = $tag->delete($pb);
		else {
			// Get parameters from POST request and write them into $tag
			$names = array("name", "description", "color");
			$par = array_intersect_key($_POST, array_fill_keys($names, 0));
			$par["hidden"] = isset($_POST["hidden"]) ? 1 : 0;
			$tag->set($par);

			$success = $tag->write($pb);
		}
		$pb->close();

		if ($success)
			header("Location: {$_SERVER['HTTP_REFERER']}");
		else
			http_error(403);
	}
?>
