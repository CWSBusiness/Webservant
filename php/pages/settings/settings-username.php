<?php

$username = "";

$errors = new ErrorCollector();

if (isset($_POST['changeUsernameSubmit'])) {

	$username = $_POST["username"];
	if (strlen($username) < USERNAME_MIN_LENGTH) {
		$errors->addError("Usernames must be at least " . USERNAME_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
	} else if (!Validate::username($username)) {
		$errors->addError("Please enter a valid username.", ErrorCollector::WARNING);
	} else {
		if (trim($username) == User::current()->getUsername()) {
			$errors->addError("Please enter a new username.", ErrorCollector::WARNING);
		} else {
			$username = trim($username);
			if (!User::usernameAvailable($username)) {
				$errors->addError("This username is already taken.", ErrorCollector::WARNING);
			} else {
				if ($_POST['pass'] == "") {
					$errors->addError("You must enter your password to make changes.", ErrorCollector::WARNING);
				} else {
					if (!Auth::verify($_POST['pass'], User::current()->getPassword())) {
						$errors->addError("The password you entered is incorrect.", ErrorCollector::WARNING);
					} else {
						User::current()->setUsername($username);
						User::current()->save();
						Auth::login($username, $_POST['pass']);
						Auth::redirect("./?settings");
					}
				}


			}
		}
	}

}


include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-gears"></i> <a href="./?settings">Settings</a> &gt; Change Username</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">New Username</h2>
		<table>
			<tr>
				<th><label for="username">New Username</label></th>
				<td>
					<input type="text" name="username" id="username" value="<?php echo $username; ?>" placeholder="<?php echo User::current()->getUsername(); ?>" autocorrect="off" autocapitalize="off" spellcheck="false">
				</td>
			</tr>
			<tr>
				<th><label for="pass">Enter Password to Confirm</label></th>
				<td><input type="password" name="pass" id="pass"></td>
			</tr>
		</table>
	</section>

	<input type="hidden" name="changeUsernameSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Change</button>

</form>

<?php include_once "php/footer.php";