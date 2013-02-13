<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Aufgabe bewerten</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<div class="content">
	<h2 class="eval">Aufgabe bewerten</h2>

	<?php
		$id = (int)$_REQUEST['id'];
		if (!isset($user_id))
			die('Fehler: Nur Benutzer dürfen kommentieren!');
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		$result = $pb->query("SELECT * FROM problems WHERE id=".$id);
		$problem = $result->fetchArray(SQLITE3_ASSOC);
		$result = $pb->query("SELECT * FROM comments WHERE user_id=$user_id AND problem_id=".$id);
		$comment = $result->fetchArray(SQLITE3_ASSOC);
		$pb->close();
	?>
	<form class="eval" title="Bewertungsformular" action="submit_eval.php" method="POST">
		<div class="problem" id="prob"><?php print $problem['problem']?></div>
		<input type="hidden" name="id" value="<?php print $id?>"/>
		<span class="eval">Eleganz</span>
		<span onmouseout="Beauty.reset();">
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
			<input type="hidden" name="beauty" id="beauty" value="<?php print $comment['beauty']?>"/>
		<span class="eval">Schwierigkeit</span>
			<span onmouseout="Diff.reset();">
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
			<input type="hidden" name="diff" id="diff" value="<?php print $comment['difficulty']?>"/>
		<span class="eval">Wissen</span>
			<span onmouseout="Know.reset();">
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
			<input type="hidden" name="know" id="know" value="<?php print $comment['knowledge_required']?>"/>
		<textarea name="comment" rows="10" cols="80" placeholder="Kommentar" style="height:100px;"><?php print $comment['comment']?></textarea> <br/>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="Speichern" style="float:right;"/>
		<input type="button" value="Verwerfen" onclick="history.back();" style="float:right;"/>
	</form>
	</div>

	<script type="text/javascript">
		text = document.getElementById("prob");
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, prob]);
		var Beauty = new Stars("beauty");
		var Diff = new Stars("diff");
		var Know = new Stars("know");
	</script>
</body>
</html>
