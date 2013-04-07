<?php
	function int2clrstr($num)
	{
		$str = "00000".dechex($num);
		return "#".substr($str, -6);
	}

	function int2color($num) { return array($num % 0x100, ($num % 0x10000)>>8, $num>>16); }
	function color2int($clr) { return $clr[0] + ($clr[1]<<8) + ($clr[2]<<16); }

	function print_tag($id, $name, $desc, $tagcolor, $form = "") {
		// compute 2nd color for gradient
		$color = int2color($tagcolor);
		foreach ($color as &$comp)
			$comp += ($comp>=32) ? -32 : 32;
		$gradclr = color2int($color);

		// decide on text color
		$white = (0.07*$color[0] + 0.71*$color[1] + 0.21*$color[2] < 0.5*0x100);

		// write tag
		print "<span class='tag' ";
		echo "style='background:".int2clrstr($tagcolor).";";
		if ($white)
			print "color:White;text-shadow:1px 1px 0px Black;";
		else
			print "color:Black;text-shadow:1px 1px 0px White;";
		print "background:linear-gradient(to bottom, ".int2clrstr($tagcolor).",".int2clrstr($gradclr).");' ";
		print "title='$desc'>$name";
		if ($form != "")
			print "<a href='javascript:removeTag(\"$form\",$id)'><img class='close' src='{$_SERVER["PBROOT"]}/img/close.png' alt='x'></a>";
		print "</span>";
	}

	function tags($pb, $tags, $form = "") {
		// get tag data from db
		$restr = isset($_SESSION['user_id']) ? "" : " AND hidden=0";
		$tags = $pb->query("SELECT * FROM tags WHERE id in (".$tags.")".$restr);

		while($tag_info = $tags->fetchArray(SQLITE3_ASSOC))
			print_tag($tag_info['id'], $tag_info['name'], $tag_info['description'], $tag_info['color'], $form);
	}

	function tag_form($pb, $form, $taglist)
	{
		$restr = isset($_SESSION['user_id']) ? "" : " WHERE hidden=0";
		$tags = $pb->query("SELECT id, name FROM tags".$restr); ?>
		<select name="tag" id="tag_select" onchange="tagList.add(parseInt(this.value)); this.value=0;">
		<option selected value="0">&mdash;Tag hinzuf&uuml;gen&mdash;</option>
<?php	while($tag = $tags->fetchArray(SQLITE3_NUM))
			print '<option value="'.$tag[0].'">'.$tag[1].'</option>'; ?>
		</select>
		<input type="hidden" name="tags"/>
		<span id="taglist" style="margin:3px;"></span>
		<script>var tagList = new TagList("<?=$form?>", [<?=$taglist?>]);</script>
 <?php }

	function get_tags($pb, $problem_id)
	{
		return $pb->querySingle("SELECT group_concat(tag_id) FROM tag_list WHERE problem_id=$problem_id", false);
	}

	// answer to Ajax queries for tags
	if (isset($_REQUEST['taginfo'])) {
		session_start();
		$pb = new SQLite3('sqlite/problembase.sqlite');
		$res = $pb->query("SELECT name, description, color, hidden FROM tags WHERE id='".$_REQUEST['id']."'")
			->fetchArray(SQLITE3_ASSOC);
		$res['color'] = "#".substr("00000".dechex($res['color']),-6);
		header("Content-Type: application/json");
		print json_encode($res);
		$pb->close();
	}

	// write tag from tag form
	if (isset($_REQUEST['id']) && (isset($_REQUEST['name']) || isset($_REQUEST['delete']))) {
		session_start();
		if (!isset($_SESSION['user_id'])) {
			include 'error403.php';
			exit();
		}

		$pb = new SQLite3('sqlite/problembase.sqlite');
		foreach ($_REQUEST as $key=>$value)
			$$key = $pb->escapeString($value);

		if (isset($_REQUEST['delete'])) {
			$pb->exec("PRAGMA foreign_keys=on");
			$pb->exec("DELETE FROM tags WHERE id=$id");
		}
		else {
			$color = hexdec(substr($color, -6));
			$hidden = isset($_REQUEST['hidden']) ? 1 : 0;

			if ($id == "")
				$pb->exec("INSERT INTO tags (name, description, color, hidden) VALUES ('$name', '$description', $color, $hidden)");
			else
				$pb->exec("UPDATE tags SET name='$name', description='$description', color=$color, hidden=$hidden WHERE id=$id");
		}
		$pb->close();

		header("Location: {$_SERVER["PBROOT"]}/tagpanel.php");
	}
?>
