<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_TAGS);

	if (!$_SESSION['editor'])
		http_error(403);

	$tags = new TagList;
	$tags->get($pb, array("name"));
	$pb->close();

	printhead("Tag-Editor");

	if (isset($_GET['standalone'])) {
		print "<body>";
		printheader();
		print "<div class='center'><div class='content'>";
	}
	else
		print "<body class='iframe'>"
?>
	<form id="tageditor" action="<?=WEBROOT?>/tags" method="POST">
		<div>
			<?php $tags->print_select("loadTag();", "Neuer Tag", "old_name"); ?>
			<input type="checkbox" name="delete"/>
			<input type="button" id="delete_tag" value="L&ouml;schen" disabled
				onclick="if (confirm('Tag wirklich l&ouml;schen?')) postDelete('tageditor');"/>
		</div>
		<div>
			<input type="text" name="name" style="width:120px;" placeholder="Name" pattern="[ 0-9A-Za-z]*" required onkeyup="tagPreview();" title="Nur Buchstaben, Ziffern und Lehrzeichen">
			<input type="color" name="color" style="width:60px;" placeholder="Farbe" pattern="#[0-9A-Fa-f]{6}$" required onkeyup="tagPreview();" title="#hhhhhh"> <br/>
			<input type="text" name="description" style="width:200px;" placeholder="Beschreibung" onkeyup="tagPreview();"> <br/>
			<input type="checkbox" name="hidden" id="hidden" style="margin:10px 5px;"><label class="info" for="hidden">redaktionell</label>
			<input type="submit" id="submit_tag" style="margin:1em;" value="Hinzuf&uuml;gen"> <br/>
			<span class="info" style="font-variant: small-caps; margin: 5px;">(Vorschau)</span><span id="result_tag"></span>
		</div>
	</form>
	<?php if (isset($_GET['standalone'])) print "</div></div>"; ?>
</body>
</html>
