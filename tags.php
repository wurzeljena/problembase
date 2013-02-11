<?php
	function int2clrstr($num)
	{
		$str = "00000".dechex($num);
		return "#".substr($str, -6);
	}

	function int2color($num) { return array($num % 0x100, ($num % 0x10000)>>8, $num>>16); }
	function color2int($clr) { return $clr[0] + ($clr[1]<<8) + ($clr[2]<<16); }

	function tags($pb, $tags, $form = "") {
		// get tag data from db
		$tags = $pb->query("SELECT * FROM tags WHERE id in (".$tags.")");

		while($tag_info = $tags->fetchArray(SQLITE3_ASSOC)) {
			// compute 2nd color for gradient
			$color = int2color($tag_info['color']);
			foreach ($color as &$comp)
				$comp += ($comp>=32) ? -32 : 32;
			$gradclr = color2int($color);

			// decide on text color
			$white = ($color[0]*$color[0] + $color[1]*$color[1] + $color[2]*$color[2] < 2*0x100*0x100);

			// write tag
			if ($form == "")
				print '<span class="tag" ';
			else
				print '<a class="tag" href="javascript:deleteTag(\''.$form.'\', '.$tag_info['id'].')" ';
			echo 'style="background:'.int2clrstr($tag_info['color']).';';
			if ($white)
				print 'color:White;text-shadow:1px 1px 0px Black;';
			else
				print 'color:Black;text-shadow:1px 1px 0px Gray;';
			print 'background:linear-gradient(to bottom, '.int2clrstr($tag_info['color']).','.int2clrstr($gradclr).');" ';
			print 'title="'.$tag_info['description'].'">'.$tag_info['name'];
			if ($form == "")
				print '</span>';
			else
				print '</a>';
		}
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
		$tag_list = $pb->query("SELECT tag_id FROM tag_list WHERE problem_id=".$problem_id);
		$tags = array();
		while ($tag = $tag_list->fetchArray(SQLITE3_NUM))
			$tags[] = $tag[0];
		return $tags;
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
?>
