<?php
	session_start();

	// if user isn't authenticated, throw a 403 error
	if (!isset($_SESSION['user_id'])) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}

	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
	$users = $pb->query("SELECT users.*, COUNT(problem_id) as numcomm FROM users LEFT JOIN comments ON comments.user_id=users.id GROUP BY id ORDER BY name");
	$root = $_SESSION['root'];

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/head.php';
	printhead("Benutzerliste");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
		<table class="users">
			<thead><tr><th>Name</th><th>E-Mail</th><th>root</th><th>editor</th><th>Kommentare</th></tr></thead>
		<tbody>
		<?php
			while ($user = $users->fetchArray(SQLITE3_ASSOC)) { ?>
				<tr <?php print 'id='.$user['id']; ?>>
					<td><?php print $user['name'] ?></td>
					<?php
					if ($user['id']==$user_id)
						print '<td><a class="button" href="javascript:User.Trig();"><i class="icon-pencil"></i> Bearbeiten</a></td>	';
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
				<tr class="own" id="forms" style="visibility:hidden; position:absolute;"><td colspan="5">
				<form id="edit" action="<?=$_SERVER["PBROOT"]?>/users/<?=$user_id?>/edit" method="POST">
					<input type="text" name="name" placeholder="Name" value="<?php print $user['name'] ?>">
					<input type="email" name="email" style="width:200px;" placeholder="E-Mail" value="<?php print $user['email'] ?>">
					<input type="submit" value="&Auml;ndern">
				</form>
				<form id="pw" action="<?=$_SERVER["PBROOT"]?>/users/<?=$user_id?>/changepw" method="POST" onsubmit="return validate_password()">
					<input type="password" style="width:100px;" name="old_pw" placeholder="Altes Passwort">
					<input type="password" style="width:100px;" name="new_pw" placeholder="Neues Passwort">
					<input type="password" style="width:100px;" name="new_pw_check" placeholder="Best&auml;tigen">
					<input type="submit" value="Passwort &auml;ndern">
				</form>
				</td></tr>
				<?php } ?>
			<?php } ?>
			</tbody>

			<?php if ($root) { ?>
			<tfoot>
				<tr><form id="newuser" action="<?=$_SERVER["PBROOT"]?>/users/new" method="POST">
					<td><input type="text" name="newname" style="width:100px;" placeholder="Name" required></td>
					<td><input type="email" name="email" style="width:180px;" placeholder="E-Mail" required></td>
					<td><input type="checkbox" name="root"></td>
					<td><input type="checkbox" name="editor"></td>
					<td><input type="submit" value="Erstellen"></td>
				</form></tr>
			</tfoot>
			<?php } ?>
		</table>
	</div>
	</div>

	<?php $pb->close(); ?>
	
	<script type="text/javascript">
		var User = new Trigger("forms");
	</script>
</body>
</html>