<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_EVALUATIONS);

	// Get user
	$id = (int)$_GET["id"];
	$user = $pb->querySingle("SELECT users.* FROM users WHERE users.id=$id", true);
	$own = ($_SESSION['user_id'] == $id);

	printhead("Benutzer {$user["name"]}");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
		<h3>Übersicht zu <?=$user["name"]?></h3>

		<?php if ($_SESSION['editor'] || $own) : ?>
		<div>
			E-Mail: <a class="email"  href="mailto:<?=$user["email"]?>"><?=$user["email"]?></span>
		<?php if ($own): ?>
			<a class="button" style="float:right;" href="javascript:password_form.Show();"><i class="icon-key"></i> Passwort ändern</a>
			<a class="button" style="float:right;" href="javascript:edit_form.Show();"><i class="icon-pencil"></i> Bearbeiten</a>
		</div>

		<form id="edit" style="display:none;"
			action="<?=WEBROOT?>/users/<?=$_SESSION['user_id']?>/edit" method="POST">
			<input type="text" name="name" placeholder="Name" value="<?=$user['name']?>">
			<input type="email" name="email" style="width:200px;" placeholder="E-Mail" value="<?=$user['email']?>">
			<input type="submit" value="Ändern">
		</form>
		<form id="pw" style="display:none;"
			action="<?=WEBROOT?>/users/<?=$_SESSION['user_id']?>/changepw" method="POST" onsubmit="return validate_password()">
			<input type="password" style="width:100px;" name="old_pw" placeholder="Altes Passwort">
			<input type="password" style="width:100px;" name="new_pw" placeholder="Neues Passwort">
			<input type="password" style="width:100px;" name="new_pw_check" placeholder="Bestätigen">
			<input type="submit" value="Passwort ändern">
		</form>

		<script type="text/javascript">
			var edit_form = new PopupTrigger("edit");
			var password_form = new PopupTrigger("pw");
		</script>

		<h3>Kommentare</h3>
		<?php else :
				print "</div>";
			endif;
		endif;

		// Liste der Kommentare
		$evals = new EvalList;
		$evals->get_for_user($pb, $id);
		$evals->print_html(true);
		?>
	</div>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
