<?php

// Procedural code controlling login/logout behaviour (run at the start of each page load)

if (isset($_GET['logout'])) {
	Auth::logout();
}

if (!Auth::authenticate()) {

	$errors = new ErrorCollector();
	$idle_message = "";
	
	$error_message = "";
	$user_field = "";
	$focus_pass = false;

	if (isset($_POST['loginSubmit'])) {
		if (!isset($_POST['usernameAlt']) && !Validate::username($_POST['username'])) {
			$errors->addError("Please enter a valid username.", ErrorCollector::WARNING);
		} else {
			if (isset($_POST['username'])) {
				$user_field = $_POST['username'];
			} else {
				$user_field = $_POST['usernameAlt'];
			}
			$focus_pass = true;
			if (!Validate::password($_POST['password'])) {
				$errors->addError("Please enter your password.", ErrorCollector::WARNING);
				$pass_field = "";
			} else {
				try {
					$attempt = Auth::login($user_field, $_POST['password']);
					if (!$attempt) {
						$errors->addError("The username/password combination you entered is invalid.", ErrorCollector::WARNING);
					}
				} catch (InvalidArgumentException $e) {
					$errors->addError("The username/password combination you entered is invalid.", ErrorCollector::WARNING);
				}
			}
		}
	}
	
	if (Auth::isIdle()) {
		$idle_message = "<p>" . Auth::idleMessage() . "</p>";
	}

	$result = Auth::authenticate();

	if (!$result) {

		include_once "php/header.php"; ?>

		<form name="login-form" class="login-form" action="" method="post">
			<h2>Log In</h2>
			<?php echo $idle_message; ?>
			<?php echo $errors; ?>
			<label for="username">Username</label>
			<?php
			$userAtts = "";
			$userValue = "";

			if (Auth::isIdle()) {
				$userValue = User::current()->getUsername();
				$userAtts .= " disabled";
			} else if (isset($_GET["username"])) {
				$userValue = $_GET["username"];
				$focus_pass = true;
			} else {
				$userValue = $user_field;
				if (!$focus_pass) {
					$userAtts .= " autofocus";
				}
			}

			?>
			<?php if (Auth::isIdle()) {
				echo '<input type="hidden" name="usernameAlt" value="' . User::current()->getUsername() . '">';
			} ?>
			<input type="text" name="username" id="username" placeholder="username" value="<?php echo $userValue; ?>" <?php echo $userAtts; ?> autocorrect="off" autocapitalize="off" spellcheck="false">
			<input type="password" name="password" id="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;" <?php if (Auth::isIdle() || $focus_pass) { echo "autofocus"; } ?>>

			<?php if (Auth::isIdle()) { ?>
				<br>
				<a href="./?logout" class="top-right-button">Log Out/Switch User</a>
			<?php } else { ?>
				<a href="./?forgotpass" class="top-right-button">Forgot?</a>
			<?php } ?>

			<input type="hidden" name="loginSubmit">
			<button type="submit">Log In</button>
		</form>

		<?php include_once "php/footer.php";
		die();

	}
	
}