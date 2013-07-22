<?php
	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/master.php';
	$pb = load(LOAD_DB);

	if ($_SESSION['user_id'] == -1)
		http_error(403);

	// new user
	if (isset($_POST["newname"]) && !isset($_GET["id"]) && $_SESSION["root"]) {
		// generate password
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		$password = strtr(base64_encode(mcrypt_create_iv(6, MCRYPT_DEV_URANDOM)), '+', '.');
		$salted_pw = crypt($password, '$6$rounds=5000$'.$salt.'$');

		$pb->exec("INSERT INTO users (name, email, encr_pw, root, editor) VALUES "
			."('{$_POST["newname"]}', '{$_POST["email"]}', '$salted_pw', "
			.(int)isset($_POST["root"]).", ".(int)isset($_POST["editor"]).")");

		// send message to user with his password
		mail($_POST["email"], "Anmeldung zur Aufgabendatenbank",
			"Lieber {$_POST["newname"]},\n\n".
			"Sie wurden zur Aufgabendatenbank angemeldet. Sie können sich ab sofort\n".
			"mit ihrer E-Mailadresse '{$_POST["email"]}' und folgendem\n".
			"Passwort einloggen:\n\n".
			$password.
			"\n\nBitte ändern sie dieses Passwort sofort nach der ersten Anmeldung.\n\n".
			"Viel Spaß beim Stöbern durch Aufgaben und Lösungen wünscht Ihnen\n".
			"\tIhr Wurzel-Verein",
			"From: info@wurzel.org\nContent-type: text/plain; charset=iso-8859-1");

		header("Location: {$_SERVER["PBROOT"]}/users/".$pb->lastInsertRowID("users", "id"));
	}

	// delete user
	if (isset($_GET["id"]) && isset($_GET["delete"]) && $_SESSION['root']) {
		$pb->exec("DELETE FROM users WHERE id={$_GET["id"]}");
		header("Location: {$_SERVER["PBROOT"]}/users/");
	}

	// change name/email or password - user has to be logged in
	if (isset($_GET["id"]) && $_GET["id"]==$_SESSION['user_id']) {
		$id = $pb->escape($_GET["id"]);
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
		$id = $pb->escape($_GET["id"]);
		if (isset($_POST["root"]) && $_SESSION['root'])
			$pb->exec("UPDATE users SET root={$_POST["root"]} WHERE id=$id");
		if (isset($_POST["editor"]) && $_SESSION['root'])
			$pb->exec("UPDATE users SET editor={$_POST["editor"]} WHERE id=$id");
	}

	$pb->close();
?>
