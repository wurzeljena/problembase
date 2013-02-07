<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Aufgabe</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="preview.js"></script>
</head>
<body>
	<div class="head"><div style="width:50em;">
		<div class="logo">&#x221A;<span style="text-decoration:overline">WURZEL</span></div>
		<div class="login">Login</div>
		<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
	</div></div>

	<?php
		$id = (int)$_REQUEST['id'];
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		$problem = $pb->querySingle("SELECT * FROM problems WHERE id=".$id, true);
		$proposer = $pb->querySingle("SELECT * FROM proposers WHERE id=".$problem['proposer_id'], true);
		$comments = $pb->query("SELECT * FROM comments, users WHERE comments.user_id=users.id AND problem_id=".$id);
	?>
	<div class="content">
		<div class="task">
			<div class="info">
			<?php
				print htmlspecialchars($proposer['name']).", ".htmlspecialchars($proposer['location']);
				if ($proposer['country'] != "") print " (".htmlspecialchars($proposer['country']).")";
			?>
			<div class="tags">
				<span class="tag tag_test">Test</span>
			</div></div>
			<div class="text" id="prob"><?php print $problem['problem']?></div>
		</div>

		<h3 class="caption" style="margin-top:1.5em;">Kommentare</h3>
		<table class="comments">
			<?php
			while($comment=$comments->fetchArray(SQLITE3_ASSOC)) {
				if ($comment['user_id']==1)
					echo '<tr class="own">';
				else
					echo '<tr>';
				echo '<td class="author">'.$comment['name'].'</td>';
				echo '<td class="comment">';
				if ($comment['user_id']==1)
					echo '<a class="button" style="float:right;" href="eval.php?id='.$comment['problem_id'].'">Bearbeiten</a>';
				echo '{Bewertungsbereich} <br/>';
				echo $comment['comment'];
				echo '</td></tr>';
			};
			?>
		</table>

		<!-- Musterlösungen (wie Aufgabenliste in index.htm)-->

		<?php $pb->close(); ?>
	</div>

	<script type="text/javascript">
		text = document.getElementById("prob");
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, prob]);
	</script>
</body>
</html>
