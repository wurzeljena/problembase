<?php
	session_start();
	if (!isset($_SESSION['user_id']))
		die("&Auml;nderungen nur angemeldet m&ouml;glich!");
	$user_id = $_SESSION['user_id'];
	$pb = new SQLite3('sqlite/problembase.sqlite');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $pb->escapeString($value);

	// new user
	if (isset($newname) && !isset($id) && $_SESSION['root']) {
		$pb->exec("INSERT INTO users (name, email, root, editor) VALUES "
			."('$newname', '$email', ".(int)isset($root).", ".(int)isset($editor).")");
		header('Location: user.php');
		// maybe later: send message to user to login and set his password
	}

	// delete user
	if (isset($id) && isset($delete) && $_SESSION['root']) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$pb->exec("DELETE FROM users WHERE id=$id");
		header('Location: user.php');
		break;
	}

	// change name/email or password - user has to be logged in
	if (isset($name) && isset($email) && isset($id) && $id==$user_id) {
		$pb->exec("UPDATE users SET name='$name', email='$email' WHERE id=$id");
		header('Location: user.php');
	}
	if (isset($old_pw) && isset($new_pw) && isset($id) && $id==$user_id) {
		$encr_pw = $pb->querySingle("SELECT encr_pw FROM users WHERE id=$id", false);
		if ($encr_pw == "" || $encr_pw == hash("sha256", $old_pw))
			$pb->exec("UPDATE users SET encr_pw='".hash("sha256", $new_pw)."' WHERE id=$id");
		header('Location: user.php');
	}
	
	// change rights - user has to be root
	if (isset($update) && isset($root) && $_SESSION['root'])
		$pb->exec("UPDATE users SET root=".$root." WHERE id=$id");
	if (isset($update) && isset($editor) && $_SESSION['root'])
		$pb->exec("UPDATE users SET editor=".$editor." WHERE id=$id");

	$pb->close();
?>
