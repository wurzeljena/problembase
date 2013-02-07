<?php
	function int2clrstr($num)
	{
		$str = "00000".dechex($num);
		return "#".substr($str, -6);
	}

	function int2color($num) { return array($num % 0x100, ($num % 0x10000)>>8, $num>>16); }
	function color2int($clr) { return $clr[0] + ($clr[1]<<8) + ($clr[2]<<16); }

	function tags($pb, $tags) {
	foreach ($tags as $tag) {
		// get tag data from db
		$tag_info = $pb->querySingle("SELECT * FROM tags WHERE id=".$tag, true);

		// compute 2nd color for gradient
		$color = int2color($tag_info['color']);
		foreach ($color as &$comp)
			$comp += ($comp>=32) ? -32 : 32;
		$gradclr = color2int($color);

		// decide on text color
		$white = ($color[0]*$color[0] + $color[1]*$color[1] + $color[2]*$color[2] < 2*0x100*0x100);

		// write tag
		echo '<span class="tag" ';
		echo 'style="background:'.int2clrstr($tag_info['color']).';';
		if ($white)
			echo 'color:White;text-shadow:1px 1px 0px Black;';
		else
			echo 'color:Black;text-shadow:1px 1px 0px Gray;';
		echo 'background:linear-gradient(to bottom, '.int2clrstr($tag_info['color']).','.int2clrstr($gradclr).');" ';
		echo 'title="'.$tag_info['description'].'" ';
		echo '>'.$tag_info['name'].'</span>';
	} }
?>

