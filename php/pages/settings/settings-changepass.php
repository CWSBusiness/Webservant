<?php

$errors = new ErrorCollector();

if (isset($_POST['resetPassSubmit'])) {

	if ($_POST['currentPass'] == "") {
		$errors->addError("Enter your existing password to make changes.", ErrorCollector::WARNING);
	} else if (!Auth::verify($_POST['currentPass'], User::current()->getPassword())) {
		$errors->addError("Your existing password was incorrect.", ErrorCollector::WARNING);
	} else {
		if (strlen($_POST["pass"]) < PASSWORD_MIN_LENGTH) {
			$errors->addError("Passwords must be at least " . PASSWORD_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
		} else if (!Validate::password($_POST['pass'])) {
			$errors->addError("You must enter a new password.", ErrorCollector::WARNING);
		} else {
			if (!Auth::passMatch($_POST['pass'], $_POST['passConfirm'])) {
				$errors->addError("The new passwords you entered do not match.", ErrorCollector::WARNING);
			} else {
				User::current()->setPassword($_POST['pass']);
				Auth::redirect("./?settings");
			}
		}
	}
	
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-gears"></i> <a href="./?settings">Settings</a> &gt; Change Password</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Old Password</h2>

		<table>
			<tr>
				<th><label for="currentPass">Current Password</label></th>
				<td><input name="currentPass" id="currentPass" placeholder="Current password" type="password"></td>
			</tr>
		</table>

	</section>

	<section>
		<h2 class="section-heading">New Password</h2>

		<table>
			<tr>
				<th><label for="pass">New Password</label></th>
				<td><input name="pass" id="pass" placeholder="New password" type="password"></td>
			</tr>
			<tr>
				<th><label for="passConfirm">Confirm Password</label></th>
				<td><input name="passConfirm" id="passConfirm" placeholder="Confirm password" type="password"></td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="resetPassSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Change</button>

</form>

<?php include_once "php/footer.php";