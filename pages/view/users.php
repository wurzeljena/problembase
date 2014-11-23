<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD);

	// Let only editors and admins see the user list
	if (!($_SESSION["editor"] || $_SESSION["root"]))
		http_error(403);

	$users = $pb->query("SELECT users.*, COUNT(problem_id) as numcomm FROM users "
		."LEFT JOIN comments ON comments.user_id=users.id GROUP BY id ORDER BY name");
	$root = $_SESSION['root'];

	printhead("Benutzerliste");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<aside id="panel">
	<?php drawMenu("sidemenu"); ?>
	</aside>

	<div class="content">
		<table class="users">
			<thead><tr><th>Name</th><th>E-Mail</th><th>root</th><th>editor</th><th>Kommentare</th></tr></thead>
		<tbody>
		<?php
			while ($user = $users->fetchAssoc()) { ?>
				<tr id=<?=$user['id']?>>
					<td><a class="username" href="<?=WEBROOT?>/users/<?=$user["id"]?>"><?=$user['name']?></a></td>
					<td class="email"><?=$user['email']?></td>
					<td><input type="checkbox" <?php
						print 'id="root'.$user['id'].'" ';
						if ($user['root'])
							print "checked ";
						if ($root && $user['id']!=$_SESSION['user_id'])
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
					<td><?=$user['numcomm']?></td>
				</tr>
			<?php } ?>
			</tbody>

			<?php if ($root) { ?>
			<tfoot>
				<tr><form id="newuser" action="<?=WEBROOT?>/users/new" method="POST">
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
</body>
</html>
