<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

try {
	$user = User::withID($_GET['edit']);
} catch (InvalidArgumentException $e) {
	Auth::redirect("./?users");
}

$errors = new ErrorCollector();

if (isset($_POST["userEditSubmit"])) {

	if ($_POST["employeeID"] > 0) {
		$user->setEmployeeID($_POST["employeeID"]);
	} else {
		$user->setEmployeeID(0);
	}

	if ($user->isEmployee()) {
		$employee = Employee::withID($user->getEmployeeID());
	}

	$continue = true;

	if (isset($_POST["admin"]) && ctype_digit($_POST["admin"])) {
		$user->setAdminStatus($_POST["admin"]);
	}

	if (strlen($_POST["username"]) < USERNAME_MIN_LENGTH) {
		$continue = false;
		$errors->addError("Usernames must be at least " . USERNAME_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
	}
	if (Validate::username($_POST['username'])) {
		if (trim($_POST['username']) != $user->getUsername()) {
			if (User::usernameAvailable($_POST["username"])) {
				$user->setUsername($_POST["username"]);
			} else {
				$continue = false;
				$errors->addError("This username is already taken.", ErrorCollector::WARNING);
			}
		}
	} else {
		$continue = false;
		$errors->addError("Please enter a valid username.", ErrorCollector::WARNING);
	}

	try {
		$user->setEmail($_POST["email"]);
		if ($user->isEmployee()) {
			$employee->setEmail($_POST["email"]);
		}
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid email address.", ErrorCollector::WARNING);
	}

	if ($continue && $_POST['pass'] != "") {
		if (strlen($_POST['pass']) < PASSWORD_MIN_LENGTH) {
			$continue = false;
			$errors->addError("Passwords must be at least " . PASSWORD_MIN_LENGTH . " characters long.", ErrorCollector::WARNING);
		} else if (Validate::password($_POST['pass'])) {
			if (!Auth::passMatch($_POST['pass'], $_POST['passConfirm'])) {
				$continue = false;
				$errors->addError("The passwords you entered do not match.", ErrorCollector::WARNING);
			} else {
				$user->setPassword($_POST['pass']);
			}
		} else {
			$continue = false;
			$errors->addError("You must enter a valid password.", ErrorCollector::WARNING);
		}
	}

	if ($continue) {
		$user->save();
		if ($user->isEmployee()) {
			$employee->save();
		}
		Auth::redirect("./?users");
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user"></i> <a href="./?users">Users</a> &gt; Edit User</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Editing <?php echo $user->getUsername() ?></h2>

		<table>
			<tr>
				<th><label for="username">Username</label></th>
				<td><input name="username" id="username" type="text" placeholder="<?php echo $user->getUsername(); ?>" value="<?php echo $user->getUsername(); ?>" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="email">Email Address</label></th>
				<td><input name="email" id="email" placeholder="email address" type="text" value="<?php echo $user->getEmail(); ?>"></td>
			</tr>
			<tr>
				<th><label for="pass">Change Password</label></th>
				<td><input name="pass" id="pass" placeholder="leaving this blank will not change the password" type="password"></td>
			</tr>
			<tr>
				<th><label for="passConfirm">Confirm Password Change</label></th>
				<td><input name="passConfirm" id="passConfirm" type="password" placeholder="Confirm password"></td>
			</tr>
			<tr>
				<th><label for="employeeID">Employee</label></th>
				<td>
					<select name="employeeID" id="employeeID">
						<option value="0" <?php echo ($user->getEmployeeID() == 0) ? "selected" : ""; ?>>Non-Employee Account</option>
						<option value="0" disabled>Current Employees</option>
						<?php foreach (Employee::getCurrent() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>" <?php echo ($user->getEmployeeID() == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
						<?php } ?>
						<option value="0" disabled>Past Employees</option>
						<?php foreach (Employee::getPast() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>" <?php echo ($user->getEmployeeID() == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
				<tr>
					<th><span class="label">Admin</span></th>
					<td>
							<?php if (User::current()->getPID() == $user->getPID()) {
								$disabled = " disabled";
								$labelStyle = " opacity: 0.5; cursor: not-allowed;";
								$disabledText = "You cannot change your own admin status.";
							} else if (User::current()->getAdminStatus() >= $user->getAdminStatus()) {
								$disabled = "";
								$labelStyle = "";
								$disabledText = "";
							} else {
								$disabled = " disabled";
								$labelStyle = " opacity: 0.5; cursor: not-allowed;";
								$disabledText = "You cannot change the admin status of accounts with higher access than you.";
							} ?>
							<input type="radio" name="admin" id="admin0" value="0" style="display: inline-block;"<?php echo $disabled; if ($user->getAdminStatus() == 0) { echo " checked"; } ?>><label for="admin0" style="<?php echo $labelStyle ?>" title="<?php echo $disabledText; ?>">Regular user</label>
							<input type="radio" name="admin" id="admin1" value="1" style="display: inline-block;"<?php echo $disabled; if ($user->getAdminStatus() == 1) { echo " checked"; } ?>><label for="admin1" style="<?php echo $labelStyle ?>" title="<?php echo $disabledText; ?>">Admin</label>
							<?php if ($disabled == "" && !User::current()->isSuperAdmin()) {
								$disabled = " disabled";
								$labelStyle = " opacity: 0.5; cursor: not-allowed;";
								$disabledText = "Only superadmins can set this status on accounts.";
							} ?>
							<input type="radio" name="admin" id="admin2" value="2" style="width: auto;"<?php echo $disabled; if ($user->isSuperAdmin()) { echo " checked"; } ?>><label for="admin2" style="<?php echo $labelStyle ?>" title="<?php echo $disabledText; ?>">Superadmin</label>
					</td>
				</tr>
		</table>

	</section>

	<a href="./?users&delete=<?php echo $user->getPID(); ?>" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete User</a>
	<input type="hidden" name="userEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";