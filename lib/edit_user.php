<?php
	session_start();
	if (!isset($_SESSION['user_id'])) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$user_id = $_SESSION['user_id'];
	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');

	// new user
	if (isset($_POST["newname"]) && !isset($_GET["id"]) && $_SESSION["root"]) {
		$pb->exec("INSERT INTO users (name, email, root, editor) VALUES "
			."('{$_POST["newname"]}', '{$_POST["email"]}', ".(int)isset($_POST["root"]).", ".(int)isset($_POST["editor"]).")");
		header("Location: {$_SERVER["PBROOT"]}/users/".$pb->lastInsertRowID());
		// maybe later: send message to user to login and set his password
	}

	// delete user
	if (isset($_GET["id"]) && isset($_GET["delete"]) && $_SESSION['root']) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$pb->exec("DELETE FROM users WHERE id={$_GET["id"]}");
		header("Location: {$_SERVER["PBROOT"]}/users/");
		break;
	}

	// change name/email or password - user has to be logged in
	if (isset($_POST["id"]) && $_POST["id"]==$user_id) {
		$id = $pb->escapeString($_GET["id"]);
		if (isset($_POST["name"]) && isset($_POST["email"])) {
			$pb->exec("UPDATE users SET name='{$_POST["name"]}', email='{$_POST["email"]}' WHERE id=$id");
			header("Location: {$_SERVER["PBROOT"]}/users/$id");
		}
		if (isset($_POST["old_pw"]) && isset($_POST["new_pw"])) {
			$encr_pw = $pb->querySingle("SELECT encr_pw FROM users WHERE id=$id", false);
			if ($encr_pw == "" || $encr_pw == crypt($_POST["old_pw"], $encr_pw)) {
				$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
				$pb->exec("UPDATE users SET encr_pw='".crypt($_POST["new_pw"], '$6$rounds=5000$'.$salt.'$')."' WHERE id=$id");
			}
			header("Location: {$_SERVER["PBROOT"]}/users/$id");
		}
	}

	// change rights - user has to be root
	if (isset($_POST["update"])) {
		$id = $pb->escapeString($_GET["id"]);
		if (isset($_POST["root"]) && $_SESSION['root'])
			$pb->exec("UPDATE users SET root={$_POST["root"]} WHERE id=$id");
		if (isset($_POST["editor"]) && $_SESSION['root'])
			$pb->exec("UPDATE users SET editor={$_POST["editor"]} WHERE id=$id");
	}
	
	$pb->close();
?>
