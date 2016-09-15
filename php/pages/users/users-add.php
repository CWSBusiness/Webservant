<?php

$data = array(
	"username" => "",
	"email" => "",
	"admin" => 0,
	"employeeID" => 0,
	"sendEmail" => false,
);

$errors = new ErrorCollector();

if (isset($_POST['addUserSubmit'])) {
	
	$continue = true;
	
	if (isset($_POST['admin'])) {
		if ($_POST['admin'] == 1) {
			$data["admin"] = 1;
		} else if ($_POST['admin'] == 2) {
			$data["admin"] = 2;
		}
	}

	$data["username"] = $_POST['username'];

	if ($data["username"] == "") {
		$continue = false;
		$errors->addError("You must enter a username.", ErrorCollector::WARNING);
	} else if (strlen($data["username"]) < USERNAME_MIN_LENGTH) {
		$continue = false;
		$errors->addError("Usernames must be at least " . USERNAME_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
	} else if (!Validate::username($data["username"])) {
		$continue = false;
		$errors->addError("Please enter a valid  username.", ErrorCollector::WARNING);
	} else if (!User::usernameAvailable($data["username"])) {
		$continue = false;
		$errors->addError("The username you entered is already taken.", ErrorCollector::WARNING);
	}

	$data["email"] = $_POST['email'];
	if (!Validate::email($data['email'])) {
		$continue = false;
		$errors->addError("You must enter a valid email address.", ErrorCollector::WARNING);
	}

	if ($_POST['employeeID'] > 0) {
		$data["employeeID"] = $_POST['employeeID'];
	}

	if (isset($_POST["sendEmail"])) {
		$data["sendEmail"] = true;
	}

	if ($continue) {
		if (strlen($_POST['pass']) < PASSWORD_MIN_LENGTH) {
			$continue = false;
			$errors->addError("Passwords must be at least " . PASSWORD_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
		} else if (Validate::password($_POST['pass'])) {
			if (!Auth::passMatch($_POST['pass'], $_POST['passConfirm'])) {
				$continue = false;
				$errors->addError("The passwords you entered do not match.", ErrorCollector::WARNING);
			}
		} else {
			$continue = false;
			$errors->addError("You must enter a valid password.", ErrorCollector::WARNING);
		}
	}
	
	if ($continue) {
		$user = User::create($data["username"], $data["email"], $_POST["pass"], $data["admin"]);
		$user->setEmployeeID($data["employeeID"]);
		if ($data["employeeID"] > 0) {
			$employee = Employee::withID($data["employeeID"]);
			$employee->setEmail($data["email"]);
			$employee->save();
		}
		$result = $user->save();
		if ($result) {

			if ($data["sendEmail"]) {

				$subject = "WebServant Login Details";

				$message = '<html><body>';
				$message .= '<p>Hello there! </p>';
				$message .= '<p>Your WebServant account has been created and is ready for you to access. </p>';
				$message .= '<p>You can log in to your account at <a href="' . DOMAIN . '">' . DOMAIN . '</a> with the following credentials: </p>';
				$message .= '<p>Username: ' . $data["username"] . ' <br>Password: ' . $_POST["pass"] . ' </p>';
				$message .= '<p>It is strongly encouraged that you change your password once you log in, for security reasons. </p>';
				$message .= '<p>Cheers, <br>WebServant </p>';
				$message .= '</body></html>';

				Mail::sendHTML($data["email"], $subject, $message);

			}

			Auth::redirect("./?users");
		} else {
			$errors->addError("An error occurred creating the user.", ErrorCollector::DANGER);
		}
	}
		
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user"></i> <a href="./?users">Users</a> &gt; Add User</h2>

<form action="" method="post">
	
	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">New User</h2>

		<table>
			<tr>
				<th><label for="username">Username</label></th>
				<td><input type="text" name="username" id="username" value="<?php echo $data["username"]; ?>" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="email">Email Address</label></th>
				<td><input type="email" name="email" id="email" value="<?php echo $data["email"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="pass">Password</label></th>
				<td><input name="pass" id="pass" type="password"></td>
			</tr>
			<tr>
				<th><label for="passConfirm">Confirm password</label></th>
				<td><input name="passConfirm" id="passConfirm" type="password"></td>
			</tr>
			<tr>
				<th><label for="employeeID">Employee</label></th>
				<td>
					<select name="employeeID" id="employeeID">
						<option value="0"<?php if ($data["employeeID"] == 0) { echo " selected"; } ?>>Non-Employee Account</option>
						<option value="0" disabled>Current Employees</option>
						<?php foreach (Employee::getCurrent() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>"<?php if ($data["employeeID"] == $employee->getPID()) { echo " selected"; } ?>><?php echo $employee->getLastName() . ", " . $employee->getFirstName(); ?></option>
						<?php } ?>
						<option value="0" disabled>Past Employees</option>
						<?php foreach (Employee::getPast() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>"<?php if ($data["employeeID"] == $employee->getPID()) { echo " selected"; } ?>><?php echo $employee->getLastName() . ", " . $employee->getFirstName(); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><span class="label">Admin</span></th>
				<td>
					<?php
					$disabled = "";
					$labelStyle = "";
					?>
					<input type="radio" name="admin" id="admin0" value="0" style="display: inline-block;"<?php echo $disabled; if ($data["admin"] == 0) { echo " checked=\"checked\""; } ?>><label for="admin0" style="<?php echo $labelStyle ?>">Regular user</label>
					<input type="radio" name="admin" id="admin1" value="1" style="display: inline-block;"<?php echo $disabled; if ($data["admin"] == 1) { echo " checked=\"checked\""; } ?>><label for="admin1" style="<?php echo $labelStyle ?>">Admin</label>
					<?php if (!User::current()->isSuperAdmin()) {
						$disabled = " disabled";
						$labelStyle = " opacity: 0.5; pointer-events: none;";
					} ?>
					<input type="radio" name="admin" id="admin2" value="2" style="display: inline-block;"<?php echo $disabled; if ($data["admin"] == 2) { echo " checked=\"checked\""; } ?>><label for="admin2" style="<?php echo $labelStyle ?>">Superadmin</label>
				</td>
			</tr>
			<tr>
				<th><span class="label">Setup Email</span></th>
				<td>
					<input type="checkbox" name="sendEmail" id="sendEmail" value="1" <?php echo ($data["sendEmail"]) ? "checked" : "" ?>>
					<label for="sendEmail">Send the new user an email with login instructions</label>
				</td>
			</tr>
		</table>

	</section>
		
	<input type="hidden" name="addUserSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create User</button>
		
</form>

<?php include_once "php/footer.php";