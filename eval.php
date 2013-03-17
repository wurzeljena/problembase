<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank - Aufgabe bewerten</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
		$id = (int)$_REQUEST['id'];
		if (!isset($user_id))
			die('Fehler: Nur Benutzer d&uuml;rfen kommentieren!');
		$pb = new SQLite3('sqlite/problembase.sqlite');
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);
		$comment = $pb->querySingle("SELECT * FROM comments WHERE user_id=$user_id AND problem_id=$id", true);
		$pb->close();
	?>

	<div class="content">
	<h2 class="eval">Aufgabe bewerten</h2>
	<form class="eval" id="eval" title="Bewertungsformular" action="submit_eval.php" method="POST">
		<div class="problem" id="prob"><?php print htmlspecialchars($problem['problem']); ?></div>
		<input type="hidden" name="id" value="<?php print $id?>"/>
		<label class="eval" for="beautystars">Eleganz</label>
		<span id="beautystars" onmouseout="Beauty.reset();">
			<img class="star" id="beauty1"
				onmouseover="Beauty.show(1);" onclick="Beauty.set(1);"/>
			<img class="star" id="beauty2"
				onmouseover="Beauty.show(2);" onclick="Beauty.set(2);"/>
			<img class="star" id="beauty3"
				onmouseover="Beauty.show(3);" onclick="Beauty.set(3);"/>
			<img class="star" id="beauty4"
				onmouseover="Beauty.show(4);" onclick="Beauty.set(4);"/>
			<img class="star" id="beauty5"
				onmouseover="Beauty.show(5);" onclick="Beauty.set(5);"/>
			</span>
			<input type="hidden" name="beauty" id="beauty" value="<?php print empty($comment) ? -1 : $comment['beauty']?>"/>
		<label class="eval" for="diffstars">Schwierigkeit</label>
			<span id="diffstars" onmouseout="Diff.reset();">
			<img class="star" id="diff1"
				onmouseover="Diff.show(1);" onclick="Diff.set(1);"/>
			<img class="star" id="diff2"
				onmouseover="Diff.show(2);" onclick="Diff.set(2);"/>
			<img class="star" id="diff3"
				onmouseover="Diff.show(3);" onclick="Diff.set(3);"/>
			<img class="star" id="diff4"
				onmouseover="Diff.show(4);" onclick="Diff.set(4);"/>
			<img class="star" id="diff5"
				onmouseover="Diff.show(5);" onclick="Diff.set(5);"/>
			</span>
			<input type="hidden" name="diff" id="diff" value="<?php print empty($comment) ? -1 : $comment['difficulty']?>"/>
		<label class="eval" for="knowstars">Wissen</label>
			<span id="knowstars" onmouseout="Know.reset();">
			<img class="star" id="know1"
				onmouseover="Know.show(1);" onclick="Know.set(1);"/>
			<img class="star" id="know2"
				onmouseover="Know.show(2);" onclick="Know.set(2);"/>
			<img class="star" id="know3"
				onmouseover="Know.show(3);" onclick="Know.set(3);"/>
			<img class="star" id="know4"
				onmouseover="Know.show(4);" onclick="Know.set(4);"/>
			<img class="star" id="know5"
				onmouseover="Know.show(5);" onclick="Know.set(5);"/>
			</span>
			<input type="hidden" name="know" id="know" value="<?php print empty($comment) ? -1 : $comment['knowledge_required']?>"/>
		<textarea name="comment" rows="10" cols="80" placeholder="Kommentar" style="height:100px;"><?php
			if (!empty($comment)) print $comment['comment']?></textarea> <br/>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="Speichern" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('Kommentar wirklich l&ouml;schen?')) postDelete('eval');"/>
		<?php } ?>
	</form>
	</div>

	<script type="text/javascript">
		var Beauty = new Stars("beauty");
		var Diff = new Stars("diff");
		var Know = new Stars("know");
	</script>
</body>
</html>
