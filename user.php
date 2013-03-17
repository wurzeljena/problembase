<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="fancy.js"></script>
	<script type="text/javascript" src="ajax.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
		$pb = new SQLite3('sqlite/problembase.sqlite');
		if (!isset($user_id))
			die("Nur f&uuml;r angemeldete Nutzer sichtbar!");
		$root = $_SESSION['root'];
	?>

	<div class="content">
		<div class="caption" id="users" style="margin-top:1.5em;">Benutzer</div>
		<table class="users">
			<tr><th>Name</th><th>E-Mail</th><th>root</th><th>editor</th><th>Kommentare</th></tr>
		<?php
		$users = $pb->query("SELECT users.*, COUNT(problem_id) as numcomm FROM users LEFT JOIN comments ON comments.user_id=users.id GROUP BY id ORDER BY name");
			while ($user = $users->fetchArray(SQLITE3_ASSOC)) { ?>
				<tr <?php print 'id='.$user['id']; ?>>
					<td><?php print $user['name'] ?></td>
					<?php
					if ($user['id']==$user_id)
						print '<td><a class="button" href="javascript:User.Trig();">Bearbeiten</a></td>	';
					else
						print '<td style="font-family:monospace;">'.$user['email'].'</td>';
					?>
					<td><input type="checkbox" <?php
						print 'id="root'.$user['id'].'" ';
						if ($user['root'])
							print "checked ";
						if ($root && $user['id']!=$user_id)
							print "onclick='setright(".$user['id'].", \"root\")'";
						else
							print "disabled";
						?>></td>
					<td><input type="checkbox" <?php
						print 'id="editor'.$user['id'].'" ';
						if ($user['editor'])
							print "checked ";
						if ($root)
							print "onclick='setright(".$user['id'].", \"editor\")'";
						else
							print "disabled";
						?>></td>
					<td><?php print $user['numcomm'] ?></td>
				</tr>

				<?php if ($user['id']==$user_id) { ?>
				<tr class="own" id="forms" style="visibility:hidden; position:absolute;"><td colspan=5>
				<form id="edit" action="edit_user.php" method="POST">
					<input type="hidden" name="id" value="<?php print $user_id; ?>">
					<input type="text" name="name" placeholder="Name" value="<?php print $user['name'] ?>">
					<input type="email" name="email" style="width:200px;" placeholder="E-Mail" value="<?php print $user['email'] ?>">
					<input type="submit" value="&Auml;ndern">
				</form>
				<form id="pw" action="edit_user.php" method="POST" onsubmit="return validate_password()">
					<input type="hidden" name="id" value="<?php print $user_id; ?>">
					<input type="password" style="width:100px;" name="old_pw" placeholder="Altes Passwort">
					<input type="password" style="width:100px;" name="new_pw" placeholder="Neues Passwort">
					<input type="password" style="width:100px;" name="new_pw_check" placeholder="Best&auml;tigen">
					<input type="submit" value="Passwort &auml;ndern">
				</form>
				</td></tr>
				<?php } ?>
			<?php } ?>

			<?php if ($root) { ?>
				<tr><form id="newuser" action="edit_user.php" method="POST">
					<td><input type="text" name="newname" style="width:100px;" placeholder="Name" required></td>
					<td><input type="email" name="email" style="width:180px;" placeholder="E-Mail" required></td>
					<td><input type="checkbox" name="root"></td>
					<td><input type="checkbox" name="editor"></td>
					<td><input type="submit" value="Erstellen"></td>
				</form></tr>
			<?php } ?>
		</table>
	</div>

	<?php $pb->close(); ?>
	
	<script type="text/javascript">
		var User = new Trigger("forms");
	</script>
</body>
</html>
