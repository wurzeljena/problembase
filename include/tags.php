<?php
	// (Extra) condition to select only tags the current user is allowed to see
	function tag_restr($rights) {
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

	// Is the current user allowed to access the tag $id?
	function tag_access($pb, $id, $rights) {
		if ($rights == ACCESS_WRITE)
			return $_SESSION['editor'];
		else
			return $pb->querySingle("SELECT COUNT(id) FROM tags WHERE id=$id".tag_restr($rights), true);
	}

	// Print commands writing certain tags into the element given by the
	// JavaScript variable named $taglist. $tags==ALL_TAGS means: write all tags.
	function tags($pb, $tags, $taglist) {
		if (!$tags)
			return;

		// get tag data from db
		$tags = $pb->query("SELECT * FROM tags WHERE id IN (".$tags.")".tag_restr(ACCESS_READ));
		while($tag_info = $tags->fetchAssoc()) {
			$tag_info['color'] = "#".substr("00000".dechex($tag_info['color']),-6);
			print "$taglist.appendChild(writeTag(".json_encode($tag_info)."));";
		}
	}

	// Print the tag form
	function tag_form($pb, $form, $taglist)
	{
		$tags = $pb->query("SELECT id, name FROM tags WHERE 1".tag_restr(ACCESS_READ)); ?>
		<select id="tag_select" onchange="tagList.add(parseInt(this.value)); this.value=0;">
		<option selected value="0">&mdash;Tag hinzuf&uuml;gen&mdash;</option>
<?php	while($tag = $tags->fetchArray())
			print '<option value="'.$tag[0].'">'.$tag[1].'</option>'; ?>
		</select>
		<input type="hidden" name="tags"/>
		<span id="taglist"></span>
		<script>var tagList = new TagList("<?=$form?>", [<?=$taglist?>]);</script>
 <?php }

	// Get the tags for a problem
	function get_tags($pb, $problem_id)
	{
		return $pb->querySingle("SELECT group_concat(tag_id) FROM tag_list WHERE problem_id=$problem_id", false);
	}

	function tag_selector($pb, $tags, $problem_id) {
		// create empty div for tags
		print "<div class='tag_selector'><i class='icon-tags'></i></div>";

		// initial script to print and mark the right ones
		print "<script> var tagSelector = document.getElementsByClassName('tag_selector')[0];";
		$tags = $pb->query("SELECT *, (id IN ($tags)) AS active, 1 AS enabled, $problem_id as problem FROM tags WHERE 1".tag_restr(ACCESS_READ));
		// display only tags the user is allowed to add/remove
		while($tag_info = $tags->fetchAssoc()) {
			$tag_info['color'] = "#".substr("00000".dechex($tag_info['color']),-6);
			print "tagSelector.appendChild(writeTag(".json_encode($tag_info)."));";
			print "tagSelector.appendChild(document.createTextNode(' '));";
		}
		print "</script>";
	}

	// Answer to Ajax queries for tags
	if (isset($_GET['taginfo'])) {
		include '../lib/master.php';
		$pb = load(LOAD_DB);
		$res = $pb->query("SELECT name, description, color, hidden FROM tags WHERE id='".$_GET['id']."'".tag_restr(ACCESS_READ))
			->fetchAssoc();
		if ($res) {
			$res['color'] = "#".substr("00000".dechex($res['color']),-6);
			header("Content-Type: application/json");
			print json_encode($res);
		}
		else
			http_error(404, "Tag nicht gefunden");
		$pb->close();
	}

	// Write tag from tag form
	if (isset($_POST['id']) && isset($_POST['name'])) {
		include '../lib/master.php';
		$pb = load(LOAD_DB);
		$right = ($_POST['id'] == "") ? ACCESS_WRITE : ACCESS_MODIFY;
		if (!tag_access($pb, (int)$_POST['id'], $right)) {
			http_error(403);
			exit();
		}

		foreach(array("id", "name", "description", "color") as $key)
			$$key = $pb->escape($_POST[$key]);

		if (isset($_POST['delete'])) {
			$pb->exec("PRAGMA foreign_keys=on");
			$pb->exec("DELETE FROM tags WHERE id=$id");
		}
		else {
			$color = hexdec(substr($color, -6));
			$hidden = isset($_POST['hidden']) ? 1 : 0;

			if ($id == "")
				$pb->exec("INSERT INTO tags (name, description, color, hidden) VALUES ('$name', '$description', $color, $hidden)");
			else
				$pb->exec("UPDATE tags SET name='$name', description='$description', color=$color, hidden=$hidden WHERE id=$id");
		}
		$pb->close();

		header("Location: {$_SERVER['HTTP_REFERER']}");
	}
?>
