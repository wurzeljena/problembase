<?php
	function tags($pb, $tags, $taglist = "taglist") {
		if (!$tags)
			return;
		if ($taglist === "taglist"): ?>
		<script> (function () {
			var taglist = document.getElementsByClassName("tags")[0];
<?php	endif;

		// get tag data from db
		$restr = isset($_SESSION['user_id']) ? "" : " AND hidden=0";
		$tags = $pb->query("SELECT * FROM tags WHERE id IN (".$tags.")".$restr);
		while($tag_info = $tags->fetchAssoc()) {
			$tag_info['color'] = "#".substr("00000".dechex($tag_info['color']),-6);
			print "$taglist.appendChild(writeTag(".json_encode($tag_info)."));";
		}

		if ($taglist === "taglist"): ?>
		})();</script>
<?php	endif;
	}

	function tag_form($pb, $form, $taglist)
	{
		$restr = isset($_SESSION['user_id']) ? "" : " WHERE hidden=0";
		$tags = $pb->query("SELECT id, name FROM tags".$restr); ?>
		<select name="tag" id="tag_select" onchange="tagList.add(parseInt(this.value)); this.value=0;">
		<option selected value="0">&mdash;Tag hinzuf&uuml;gen&mdash;</option>
<?php	while($tag = $tags->fetchArray())
			print '<option value="'.$tag[0].'">'.$tag[1].'</option>'; ?>
		</select>
		<input type="hidden" name="tags"/>
		<span id="taglist"></span>
		<script>var tagList = new TagList("<?=$form?>", [<?=$taglist?>]);</script>
 <?php }

	function get_tags($pb, $problem_id)
	{
		return $pb->querySingle("SELECT group_concat(tag_id) FROM tag_list WHERE problem_id=$problem_id", false);
	}

	// answer to Ajax queries for tags
	if (isset($_GET['taginfo'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/database.php';

		$pb = Problembase();
		$res = $pb->query("SELECT name, description, color, hidden FROM tags WHERE id='".$_GET['id']."'")
			->fetchAssoc();
		$res['color'] = "#".substr("00000".dechex($res['color']),-6);
		header("Content-Type: application/json");
		print json_encode($res);
		$pb->close();
	}

	// write tag from tag form
	if (isset($_POST['id']) && isset($_POST['name'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/database.php';
		if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
			include 'error403.php';
			exit();
		}

		$pb = Problembase();
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
