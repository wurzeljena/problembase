<form id="tageditor" action="tags.php" method="POST">
	<div>
		<input type="hidden" name="referer" value="<?php print $_SERVER['REQUEST_URI']; ?>">
		<select name="id" onchange="loadTag();">
			<option value="" selected>—Neuer Tag—</option>
			<?php
				$tags = $pb->query("SELECT id, name FROM tags");
				while(list($id, $tag) = $tags->fetchArray(SQLITE3_NUM))
					print "<option value='$id'>$tag</option>";
			?>
		</select>
		<input type="button" value="Löschen" disabled onclick="">
	</div>
	<div>
		<input type="text" name="name" style="width:120px;" placeholder="Name" onchange="tagPreview();">
		<input type="color" name="color" style="width:60px;" placeholder="Farbe" onchange="tagPreview();" title="Format: #hhhhhh"> <br/>
		<input type="text" name="description" style="width:200px;" placeholder="Beschreibung" onchange="tagPreview();"> <br/>
		<input type="submit" id="submit_tag" value="Hinzufügen"> <span id="result_tag"></span>
	</div>
</form>