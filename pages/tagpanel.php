<?php
	session_start();

	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/head.php';
	printhead("Tag-Editor");

	if (isset($_GET['standalone'])) {
		print "<body>";
		printheader();
		print "<div class='center'><div class='content'>";
	}
	else
		print "<body class='iframe'>"
?>
	<form id="tageditor" action="<?=$_SERVER["PBROOT"]?>/tags/edit" method="POST">
		<div>
			<select name="id" onchange="loadTag();">
				<option value="" selected>&mdash;Neuer Tag&mdash;</option>
				<?php
					$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
					$tags = $pb->query("SELECT id, name FROM tags");
					while(list($id, $tag) = $tags->fetchArray(SQLITE3_NUM))
						print "<option value='$id'>$tag</option>";
					$pb->close();
				?>
			</select>
			<input type="checkbox" name="delete"/>
			<input type="button" id="delete_tag" value="L&ouml;schen" disabled
				onclick="if (confirm('Tag wirklich l&ouml;schen?')) postDelete('tageditor');"/>
		</div>
		<div>
			<input type="text" name="name" style="width:120px;" placeholder="Name" required onkeyup="tagPreview();">
			<input type="color" name="color" style="width:60px;" placeholder="Farbe" pattern="#[a-fA-F0-9]{6}$" required onkeyup="tagPreview();" title="#hhhhhh"> <br/>
			<input type="text" name="description" style="width:200px;" placeholder="Beschreibung" onkeyup="tagPreview();"> <br/>
			<input type="checkbox" name="hidden" id="hidden" style="margin:10px 5px;"><label class="info" for="hidden">nicht &ouml;ffentlich</label>
			<input type="submit" id="submit_tag" style="margin:1em;" value="Hinzuf&uuml;gen"> <br/>
			<span class="info" style="font-variant: small-caps; margin: 5px;">(Vorschau)</span><span id="result_tag"></span>
		</div>
	</form>
	<?php if (isset($_GET['standalone'])) print "</div></div>"; ?>
</body>
</html>