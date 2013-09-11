<?php
	include '../lib/master.php';
	$pb = load(LOAD_DB);

	if (isset($_POST["logout"])) {
		$_SESSION['user_id'] = -1;
		$_SESSION['root'] = $_SESSION['editor'] = 0;
	}
	else {
		// look up user ID and password hash
		$user = $pb->querySingle("SELECT id, name, encr_pw, root, editor, wait_till "
			."FROM users WHERE email='{$pb->escape($_POST["email"])}'", true);

		$now = new DateTime("now");
		$wait_till = new DateTime($user['wait_till']);

		// check if we don't have to wait and for correct password
		if ($now >= $wait_till && ($user['encr_pw'] == "" || $user['encr_pw'] == crypt($_POST["password"], $user['encr_pw']))) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['name'];
			$_SESSION['email'] = $_POST["email"];
			$_SESSION['root'] = $user['root'];
			$_SESSION['editor'] = $user['editor'];

			$pb->exec("UPDATE users SET wait_till=null WHERE email='{$pb->escape($_POST["email"])}'");
			unset($_SESSION['wait']);
		}
		else if ($now >= $wait_till) {
			$wait_till = date_add($now, new DateInterval('PT10S'));
			$pb->exec("UPDATE users SET wait_till='{$wait_till->format('H:i:s')}' WHERE email='{$pb->escape($_POST["email"])}'");
			$_SESSION['wait'] = $wait_till->format(DATE_ATOM);
		}
	}

	$pb->close();

	// redirect to referer
	header('Location: '.$_SERVER['HTTP_REFERER']);
?>
