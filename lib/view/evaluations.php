<?php
	class Evaluation {
		private $data;      // Named array containing the data

		// Construct from array
		function __construct($data) {
			$this->data = $data;
		}

		// Print as HTML
		function print_html() {
			print "<div class='comment".($this->data['user_id']==$_SESSION['user_id'] ? " own" : "")
				.($this->data['editorial'] ? " editorial" : "")."'>";
			if ($this->data['user_id'] == $_SESSION['user_id'])
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}/evaluate'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			print "<div class='author'>{$this->data['name']}";
			if ($_SESSION['editor'])
				print " <a href='mailto:{$this->data['email']}'><i class='icon-envelope-alt'></i></a>";
			print '</div><div class="text">'.htmlspecialchars($this->data['comment']).'</div>';

			print '<div class="eval">';
			$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
			$critcols = array('beauty', 'difficulty', 'knowledge_required');
			for ($crit=0; $crit<3; ++$crit) {
				print '<span class="evalspan">';
				print '<span class="eval">'.$critnames[$crit].'</span> ';
				for ($star=1; $star<=5; ++$star)
					print ($star <= $this->data[$critcols[$crit]]) ?
						"<img class='star' src='".WEBROOT."/img/mandstar.png' alt='*'> " :
						"<img class='star' src='".WEBROOT."/img/mand.png' alt='o'> ";
				print '</span> ';
			}
			print '</div></div>';
		}
		
	}
	
	class EvalList {
		private $data = array();    // Array of Evaluation objects

		// Evaluations for a problem
		function get(SQLDatabase $pb, $problem_id) {
			$cond = $_SESSION['editor'] ? "" : " AND (editorial=0 OR user_id={$_SESSION["user_id"]})";
			$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE problem_id=$problem_id".$cond);
			while ($comment = $comments->fetchAssoc())
				$this->data[] = new Evaluation($comment);
		}

		// Print them all
		function print_html() {
			foreach($this->data as $comment)
				$comment->print_html();
		}
	}
?>