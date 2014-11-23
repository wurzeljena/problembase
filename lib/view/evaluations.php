<?php
	class Evaluation {
		private $data;      // Named array containing the data

		// Construct from array
		function __construct($data) {
			$this->data = $data;
		}

		// Print as HTML
		function print_html($link) {
			print "<article class='comment".($this->data['user_id']==$_SESSION['user_id'] ? " own" : "")
				.($this->data['editorial'] ? " editorial" : "")."'>";
			if ($this->data['user_id'] == $_SESSION['user_id'])
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}/evaluate'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			if ($link)
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}'><i class='icon-hand-right'></i> <span>Zur Aufgabe</span></a>\n";
			print "<address><a class='username' "
				."href='".WEBROOT."/users/{$this->data['user_id']}'>{$this->data['name']}</a>";
			if ($_SESSION['editor'])
				print " <a class='envelope' href='mailto:{$this->data['email']}'><i class='icon-envelope-alt'></i></a>";
			print '</address><div class="text">'.htmlspecialchars($this->data['comment']).'</div>';

			print "<div style='clear:right;'>\n";
			$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
			$critcols = array('beauty', 'difficulty', 'knowledge_required');
			for ($crit=0; $crit<3; ++$crit) {
				print '<div class="eval">';
				print "<span class='eval'>{$critnames[$crit]}</span>\n";
				for ($star=1; $star<=5; ++$star) {
					$class = ($star <= $this->data[$critcols[$crit]]) ? "checked" : "unchecked";
					print "\t<span class='$class'>★</span>\n";
				}
				print "</div>\n";
			}
			print "</div></article>";
		}
		
	}
	
	class EvalList {
		private $data = array();    // Array of Evaluation objects

		// Evaluations for a problem
		function get_for_problem(SQLDatabase $pb, $problem_id) {
			$cond = $_SESSION['editor'] ? "" : " AND (editorial=0 OR user_id={$_SESSION["user_id"]})";
			$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE problem_id=$problem_id".$cond);
			while ($comment = $comments->fetchAssoc())
				$this->data[] = new Evaluation($comment);
		}

		// Evaluations for one user
		function get_for_user(SQLDatabase $pb, $user_id) {
			$cond = $_SESSION['editor'] ? "" : " AND (editorial=0 OR user_id={$_SESSION["user_id"]})";
			$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE user_id=$user_id".$cond);
			while ($comment = $comments->fetchAssoc())
				$this->data[] = new Evaluation($comment);
		}

		// Print them all
		function print_html($link) {
			foreach($this->data as $comment)
				$comment->print_html($link);
		}
	}
?>
