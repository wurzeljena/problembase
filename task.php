<?php
	session_start();
	include 'tags.php';
	include 'proposers.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank - Aufgabe</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="user-scalable=no,width=device-width">
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="stylesheet" type="text/css" href="Font-Awesome/css/Font-Awesome.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
	$id = (int)$_REQUEST['id'];
	$pb = new SQLite3('sqlite/problembase.sqlite');
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);
	?>
	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
		<div class="task">
			<?php
				if (isset($user_id))
					print "<a class='button inner' href='problem.php?id=$id'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			?>
			<div class="info">
			<div class="tags">
				<?php tags($pb, get_tags($pb, $id)); ?>
			</div>
			<?php printproposers($pb, "problem", $id); ?>
			</div>
			<div class="text" id="prob"><?php print htmlspecialchars($problem['problem']); ?></div>
			<div class="published">
			<?php
				$pub = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$id, true);
				if (count($pub)) {
					$volume = $pub['month']."/".str_pad($pub['year']%100, 2, "0", STR_PAD_LEFT);
					$letter = $pub['letter'];
					$number = $pub['number'];
					print "Publiziert als $$letter\,$number$ im Heft $volume.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button danger' style='float:right' href='javascript:Publ.Show();'><i class='icon-globe'></i> <span>&Auml;ndern</span></a>";
				}
				else {
					$date = getdate();	++$date['mon'];
					if ($date['mon'] > 12) {
						$date['mon'] -= 12;
						++$date['year'];
					}
					$volume = $date['mon']."/".str_pad($date['year']%100, 2, "0", STR_PAD_LEFT);
					$number = $letter = "";
					print "Noch nicht ver&ouml;ffentlicht.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button' style='float:right' href='javascript:Publ.Show();'><i class='icon-globe'></i> <span>Ver&ouml;ffentlichen</span></a>";
				}
			?>
			<form id="publish" style="display:none;" action="publish.php" method="POST">
				<input type="hidden" name="id" value="<?php print $id; ?>">
				<input type="submit" style="float:right;" value="Speichern">
				<div style="display:inline;white-space:nowrap;">
				<label for="volume">Ausgabe:</label>
				<input type="text" id="volume" name="volume" placeholder="MM/JJ" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}"
					style="width:40px;" value="<?php print $volume; ?>">
				</div>
				<div style="display:inline;white-space:nowrap;">
				<label for="letter">Nummer:</label>
				<input type="text" id="letter" name="letter" placeholder="xxx"
					style="width:50px;" value="<?php print $letter; ?>">
				<input type="text" name="number" placeholder="NN" pattern="[1-9]|[0-5][0-9]|60"
					style="width:20px;" value="<?php print $number; ?>">
				</div>
			</form>
			</div>
		</div>

		<?php
		$solutions = $pb->query("SELECT solutions.id, files.content AS solution, solutions.remarks, solutions.month, solutions.year FROM solutions "
			."LEFT JOIN files ON solutions.file_id=files.rowid "
			."WHERE problem_id=$id");
		while($solution = $solutions->fetchArray(SQLITE3_ASSOC)) {
			print '<div class="solution">';
			if (isset($user_id))
				print "<a class='button inner' href='solution.php?id={$solution['id']}'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			print '<div class="info">';
			printproposers($pb, "solution", $solution['id']);
			print '</div>';

			print '<div class="text" id="soln">';
			print htmlspecialchars($solution['solution']);
			print '</div></div>';
		};

		// show buttons between solutions and comments
		if (isset($user_id))
			print "<a class='button' href='solution.php?problem_id=$id'><i class='icon-book'></i> L&ouml;sung hinzuf&uuml;gen</a>";
		if (isset($user_id) && !$pb->querySingle("SELECT * FROM comments WHERE user_id=$user_id AND problem_id=$id", false))
			print "<a class='button' style='float:right;' href='eval.php?id=$id'><i class='icon-comments'></i> Kommentar schreiben</a>";

		// if not logged in, separate comments from solutions with a lightweight header
		if (!isset($user_id))
			print "<h3 id='comments'><i class='icon-comment-alt'></i> Kommentare</h3>";

		$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE problem_id=$id");
		while($comment=$comments->fetchArray(SQLITE3_ASSOC)) {
			if (isset($user_id) && $comment['user_id']==$user_id)
				print '<div class="comment own">';
			else
				print '<div class="comment">';
			if (isset($user_id) && $comment['user_id']==$user_id)
				print "<a class='button inner' href='eval.php?id=$id'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			print "<div class='author'>{$comment['name']}";
			if (isset($user_id))
				print " <a href='mailto:{$comment['email']}'><i class='icon-envelope'></i></a>";
			print '</div><div class="text">'.htmlspecialchars($comment['comment']).'</div>';

			print '<div class="eval">';
			$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
			$critcols = array('beauty', 'difficulty', 'knowledge_required');
			for ($crit=0; $crit<3; ++$crit) {
				print '<span class="evalspan">';
				print '<span class="eval">'.$critnames[$crit].'</span> ';
				for ($star=1; $star<=5; ++$star)
					print ($star<=$comment[$critcols[$crit]]) ?
						'<img class="star" src="img/mandstar.png" alt="*"> ' :
						'<img class="star" src="img/mand.png" alt="o"> ';
				print '</span> ';
			}
			print '</div></div>';
		};
		?>

		<?php $pb->close(); ?>
	</div>
	</div>

	<script type="text/javascript">
		var Publ = new PopupTrigger("publish");
	</script>
</body>
</html>
