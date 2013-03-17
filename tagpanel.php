<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<script type="text/javascript" src="ajax.js"></script>
</head>
<body>
	<form id="tageditor" action="tags.php" method="POST">
		<div>
			<input type="hidden" name="referer" value="<?php print $_SERVER['REQUEST_URI']; ?>">
			<select name="id" onchange="loadTag();">
				<option value="" selected>&mdash;Neuer Tag&mdash;</option>
				<?php
					$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
					$tags = $pb->query("SELECT id, name FROM tags");
					while(list($id, $tag) = $tags->fetchArray(SQLITE3_NUM))
						print "<option value='$id'>$tag</option>";
					$pb->close();
				?>
			</select>
			<input type="button" id="delete_tag" value="L&ouml;schen" disabled onclick="deleteTag();">
		</div>
		<div>
			<input type="text" name="name" style="width:120px;" placeholder="Name" required onchange="tagPreview();">
			<input type="color" name="color" style="width:60px;" placeholder="Farbe" pattern="#?[a-fA-F0-9]{6}$" required onchange="tagPreview();" title="[#]hhhhhh"> <br/>
			<input type="text" name="description" style="width:200px;" placeholder="Beschreibung" onchange="tagPreview();"> <br/>
			<input type="checkbox" name="hidden" id="hidden" style="margin:10px 5px;"><label class="info" for="hidden">nicht &ouml;ffentlich</label>
			<input type="submit" id="submit_tag" style="float:right;" value="Hinzuf&uuml;gen"> <br/>
			<span class="info" style="font-variant: small-caps; margin: 5px;">(Vorschau)</span><span id="result_tag"></span>
		</div>
	</form>
</body>
</html>