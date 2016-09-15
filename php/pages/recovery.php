<?php

if (isset($_GET['id']) && !empty($_GET['id']) && ctype_digit($_GET['id'])) {

	$user = User::withID($_GET['id']);

	if (isset($_GET['key'])) {
		// this part of the page runs if the email link is clicked (it requires the reset key to be valid)
		if ($user->getResetKey() != $_GET['key']) {
			Auth::redirect("./");
		}
		$errors = new ErrorCollector();

		if (isset($_POST['recoveryFinalSubmit'])) {
			if (strlen($_POST['pass']) < PASSWORD_MIN_LENGTH) {
				$errors->addError("Passwords must be at least " . PASSWORD_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
			} else if (Validate::password($_POST['pass'])) {
				if (Auth::passMatch($_POST['pass'], $_POST['passConfirm'])) {
					$user->setPassword($_POST['pass']);
					Auth::redirect("./");
				} else {
					$errors->addError("The passwords you entered do not match.", ErrorCollector::WARNING);
				}
			} else {
				$errors->addError("Please enter a valid password.", ErrorCollector::WARNING);
			}
		}

		include_once "php/header.php"; ?>

		<?php echo $errors; ?>

		<section>
			<h2>Reset Password</h2>
			<form action="" method="post">
				<table>
					<tr>
						<th><label for="username">Username</label></th>
						<td><input name="username" id="username" type="text" value="<?php echo $user->getUsername(); ?>" disabled></td>
					</tr>
					<tr>
						<th><label for="pass">New password</label></th>
						<td><input name="pass" id="pass" placeholder="New password" type="password"></td>
					</tr>
					<tr>
						<th><label for="passConfirm">Confirm password</label></th>
						<td><input name="passConfirm" id="passConfirm" placeholder="Confirm password" type="password"></td>
					</tr>
				</table>
				<input type="submit" id="recoveryFinalSubmit" name="recoveryFinalSubmit" value="Reset Password">
			</form>
		</section>

		<?php include_once "php/footer.php";
		die();

	} else {
		// this part runs if they entered their username

		$mail = false;

		$reset_url = DOMAIN . "/?forgotpass&id=" . $user->getPID() . "&key=" . $user->getResetKey();

		$to      = $user->getEmail();
		$subject = "Password Recovery";

		$message = '<html><body>';
		$message .= '<p>Hello ' . $user->getUsername() . ', </p>';
		$message .= '<p>It seems you\'ve requested a password reset. To reset your WebServant password, simply click the link below or paste it into your browser: </p>';
		$message .= '<p><a href="' . $reset_url . '">' . $reset_url . '</a> </p>';
		$message .= '<p>If you received this email in error, or you\'ve already remembered your password, simply delete this email. </p>';
		$message .= '<p>Cheers, <br>WebServant </p>';
		$message .= '</body></html>';

		$mail = Mail::sendHTML($to, $subject, $message);

		include_once "php/header.php";

		if ($mail) { ?>

			<h2>Username/Password Recovery</h2>
			<p>You have been sent an email with instructions for resetting your password.</p>
			<p>You may close this page.</p>

		<?php } else { ?>

			<h2>Username/Password Recovery</h2>
			<p>We tried emailing you with instructions, but it failed.</p>
			<p>Please <a href="mailto:<?php echo ADMIN_EMAIL; ?>">contact the administrator</a> for help resetting your password.</p>

		<?php }
		include_once "php/footer.php";
		die();
	}

}

$errors = new ErrorCollector();

if (isset($_POST['recoverySubmit'])) {
	// this part runs if they entered their email address

	if (Validate::email($_POST['email'])) {
		try {
			$user = User::withEmail($_POST['email']);
			include_once "php/header.php"; ?>
			<h2>Username/Password Recovery</h2>
			<p>Your username is <strong><?php echo $user->getUsername(); ?></strong>.</p>
			<p>If this is enough to get you going, try <a href="./?username=<?php echo $user->getUsername(); ?>">logging in</a> again.</p>
			<p>Or would you like to keep going and <a href="./?forgotpass&id=<?php echo $user->getPID(); ?>">recover your password</a> as well?</p>
			<?php include_once "php/footer.php";
			die();
		} catch (Exception $e) {
			$errors->addError("The email address you entered does not exist in our records.", ErrorCollector::WARNING);
		}
	} else if (Validate::username($_POST['email'])) {
		try {
			$user = User::withUsername($_POST['email']);
			Auth::redirect("./?forgotpass&id=" . $user->getPID());
		} catch (OutOfBoundsException $e) {
			$errors->addError("The username you entered does not exist in our records.", ErrorCollector::WARNING);
		}
	} else {
		$errors->addError("Please enter a valid username.", ErrorCollector::WARNING);
	}
}

include_once "php/header.php"; ?>

<h2>Username/Password Recovery</h2>

<p>Enter your username or email address to get started.</p><br>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<label for="email" class="label-hide">Username or Email Address</label>
		<input id="text" name="email" type="text" placeholder="username or email address" value="<?php echo (isset($_POST["email"])) ? $_POST["email"] : ""; ?>" autocorrect="off" autocapitalize="off" spellcheck="false">
	</section>

	<input type="hidden" name="recoverySubmit">
	<button type="submit"><i class="fa fa-arrow-circle-right"></i>Look Up</button>
</form>

<?php include_once "php/footer.php";
die();
