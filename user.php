<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		if (!isset($user_id))
			die("Nur für angemeldete Nutzer sichtbar!");
		if (isset($_REQUEST['id']))
			$id = $_REQUEST['id'];
		else
			$id = $user_id;
		$user = $pb->querySingle("SELECT * FROM users WHERE id=$id", true);
		$root = $_SESSION['root'];
	?>

	<div class="content">
		<div class="user_info">
		<span style="font-size:large; font-weight:bold;"><?php print $user['name']; ?></span>
		<form id="rights" style="float:right;" action="edit_user.php" method="POST">
			Rechte:
			<input type="hidden" name="rights_id" value="<?php print $id; ?>">
			<input type="checkbox" name="root" <?php if ($user['root']) print "checked "; if (!$root) print "disabled"; ?>> root
			<input type="checkbox" name="editor" <?php if ($user['editor']) print "checked "; if (!$root) print "disabled"; ?>> editor
			<input type="submit" value="Ändern" <?php if (!$root) print 'disabled'; ?>>
		</form>

		<?php if ($id==$user_id) { ?>
		<a class="button" href="javascript:User.Trig();">Bearbeiten</a>
		<div id="forms" style="visibility:hidden; position:absolute;">
		<form id="edit" action="edit_user.php" method="POST">
			<input type="hidden" name="id" value="<?php print $id; ?>">
			<input type="text" name="name" placeholder="Name" value="<?php print $user['name'] ?>">
			<input type="text" name="email" style="width:200px;" placeholder="E-Mail" value="<?php print $user['email'] ?>">
			<input type="submit" value="Ändern">
		</form>
		<form id="pw" action="edit_user.php" method="POST" onsubmit="return validate_password()">
			<input type="hidden" name="id" value="<?php print $id; ?>">
			<input type="password" style="width:100px;" name="old_pw" placeholder="Altes Passwort">
			<input type="password" style="width:100px;" name="new_pw" placeholder="Neues Passwort">
			<input type="password" style="width:100px;" name="new_pw_check" placeholder="Bestätigen">
			<input type="submit" value="Passwort ändern">
		</form>
		</div>
		<?php } else {
			print '(<a href="mailto:'.$user['email'].'">Mail</a>)';
		}
		?>
		</div>

		<div class="caption" id="users" style="margin-top:1.5em;">Benutzer</div>
		<table class="users">
			<tr><th>Name</th><th>E-Mail</th><th>root</th><th>editor</th><th>Kommentare</th></tr>
		<?php
		$users = $pb->query("SELECT users.*, COUNT(problem_id) as numcomm FROM users LEFT JOIN comments ON comments.user_id=users.id GROUP BY comments.user_id ORDER BY name");
			while ($user = $users->fetchArray(SQLITE3_ASSOC)) { ?>
				<tr>
					<td><a href="user.php?id=<?php print $user['id'] ?>"><?php print $user['name'] ?></a></td>
					<td style="font-family:monospace;"><?php print $user['email'] ?></td>
					<td><input type="checkbox" <?php if ($user['root']) print "checked "; ?> disabled></td>
					<td><input type="checkbox" <?php if ($user['editor']) print "checked "; ?> disabled></td>
					<td><?php print $user['numcomm'] ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>

	<?php $pb->close(); ?>
	
	<script type="text/javascript">
		var User = new Trigger("forms");
	</script>
</body>
</html>
