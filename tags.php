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
		if ($form == '')
			print "<span class='tag' ";
		else
			print "<a class='tag' href='javascript:deleteTag(\"$form\", $id)' ";
		echo "style='background:".int2clrstr($tagcolor).";";
		if ($white)
			print "color:White;text-shadow:1px 1px 0px Black;";
		else
			print "color:Black;text-shadow:1px 1px 0px White;";
		print "background:linear-gradient(to bottom, ".int2clrstr($tagcolor).",".int2clrstr($gradclr).");' ";
		print "title='$desc'>$name";
		if ($form == '')
			print "</span>";
		else
			print "</a>";
	}

	function tags($pb, $tags, $form = "") {
		// get tag data from db
		$tags = $pb->query("SELECT * FROM tags WHERE id in (".$tags.")");

		while($tag_info = $tags->fetchArray(SQLITE3_ASSOC))
			print_tag($tag_info['id'], $tag_info['name'], $tag_info['description'], $tag_info['color'], $form);
	}

	function tags_datalist($pb)
	{
		$tags = $pb->query("SELECT name FROM tags");

		print '<datalist id="tag_datalist">';
		while($tag = $tags->fetchArray(SQLITE3_NUM)) {
			print '<option value="'.$tag[0].'">';
		}
		print '</datalist>';
	}

	function get_tags($pb, $problem_id)
	{
		return $pb->querySingle("SELECT group_concat(tag_id) FROM tag_list WHERE problem_id=".$problem_id, false);
	}

	// answer to Ajax queries from taglists
	if (isset($_REQUEST['taglist'])) {
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		if (isset($_REQUEST['newtag'])) {
			$newtag = $pb->querySingle("SELECT id FROM tags WHERE name='".$_REQUEST['newtag']."'", false);
			if ($_REQUEST['taglist'] == "")
				$taglist = $newtag;
			else
				$taglist = $_REQUEST['taglist'].",".$newtag;
		}
		else
			$taglist = $_REQUEST['taglist'];
		print '<input type="hidden" name="tags" value="'.$taglist.'"/>';
		tags($pb, $taglist, $_REQUEST['form']);
		$pb->close();
	}

	// answer to Ajax queries from tag form
	if (isset($_REQUEST['taginfo'])) {
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		list($name, $desc, $color) = $pb->query("SELECT name, description, color FROM tags WHERE id='".$_REQUEST['id']."'")
			->fetchArray(SQLITE3_NUM);
		$color = "#".substr("00000".dechex($color),-6);
		print "{name : '$name', desc : '$desc', color : '$color'}";
		$pb->close();
	}

	if (isset($_REQUEST['drawtag'])) {
		$color = hexdec(substr($_REQUEST['color'], -6));
		print_tag(0, $_REQUEST['name'], $_REQUEST['desc'], $color);
	}

	// write tag from tag form
	if (isset($_REQUEST['id']) && isset($_REQUEST['name'])) {
		session_start();
		if (!isset($_SESSION['user_id']))
			die("Nur f&uuml;r angemelde Benutzer m&ouml;glich!");

		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		foreach ($_REQUEST as $key=>$value)
			$$key = $pb->escapeString($value);
		$color = hexdec(substr($color, -6));
		if ($id == "")
			$pb->exec("INSERT INTO tags (name, description, color) VALUES ('$name', '$description', $color)");
		else
			$pb->exec("UPDATE tags SET name='$name', description='$description', color=$color WHERE id=$id");
		$pb->close();
		header('Location: '.$referer);
	}
?>
