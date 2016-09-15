<?php

$errors = new ErrorCollector();

if (isset($_POST['settingsSubmit'])) {

	if (User::current()->isEmployee()) {

		$continue = true;

		try {
			Employee::current()->setFirstName($_POST['firstName']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setLastName($_POST['lastName']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setBirthday($_POST['birthday']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid birthday.", ErrorCollector::WARNING);
		}

		if ($_POST['phone'] == "") {
			Employee::current()->setPhone("");
		} else {
			try {
				Employee::current()->setPhone(Format::phone($_POST['phone']));
			} catch (InvalidArgumentException $e) {
				$continue = false;
				$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
			}
		}

		try {
			Employee::current()->setNetID($_POST["netID"]);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid netID.", ErrorCollector::WARNING);
		}

		if ($_POST['alternateEmail'] == "") {
			Employee::current()->setEmail("");
			User::current()->setEmail(Employee::current()->getNetID() . "@queensu.ca");
		} else {
			try {
				Employee::current()->setEmail($_POST['alternateEmail']);
				User::current()->setEmail($_POST['alternateEmail']);
			} catch (InvalidArgumentException $e) {
				$continue = false;
				$errors->addError("Please enter a valid email address.", ErrorCollector::WARNING);
			}
		}

		try {
			Employee::current()->setFaculty($_POST['faculty']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid faculty name.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setMajor($_POST['major']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid major name.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setWebsiteURL($_POST['website']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid website URL or clear the field.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setGitHubURL($_POST['github']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid GitHub URL or clear the field.", ErrorCollector::WARNING);
		}

		try {
			Employee::current()->setLinkedInURL($_POST['linkedin']);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid LinkedIn URL or clear the field.", ErrorCollector::WARNING);
		}

		if ($continue) {
			$success = Employee::current()->save();
			if ($success) {
				$success = User::current()->save();
			}
			if ($success) {
				$errors->addError("Your changes have been saved.", ErrorCollector::SUCCESS);
			} else {
				$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
			}
		}

	} else {

		if (Validate::email($_POST['email'])) {
			User::current()->setEmail($_POST['email']);
			$success = User::current()->save();
			if ($success) {
				$errors->addError("Your changes have been saved.", ErrorCollector::SUCCESS);
			} else {
				$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
			}
		} else {
			$errors->addError("Please enter a valid email address.", ErrorCollector::WARNING);
		}

	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-gears"></i> Settings</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>

	<?php if (User::current()->isEmployee()) { ?>

		<h2 class="section-heading">Employee Profile</h2>
		<table>
			<tr>
				<th><label for="startDate">Start Date</label></th>
				<td><input name="startDate" id="startDate" type="text" value="<?php echo Format::date(Employee::current()->getStartDate()); ?>" disabled></td>
			</tr>
			<tr>
				<th><label for="firstName">First Name</label></th>
				<td><input name="firstName" id="firstName" placeholder="<?php echo Employee::current()->getFirstName(); ?>" type="text" value="<?php echo Employee::current()->getFirstName(); ?>" autocorrect="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="lastName">Last Name</label></th>
				<td><input name="lastName" id="lastName" placeholder="<?php echo Employee::current()->getLastName(); ?>" type="text" value="<?php echo Employee::current()->getLastName(); ?>" autocorrect="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="birthday">Birthday</label></th>
				<td><input name="birthday" id="birthday" placeholder="MM/DD/YYYY" type="text" value="<?php echo Format::date(Employee::current()->getBirthday()); ?>"></td>
			</tr>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td><input name="phone" id="phone" type="tel" value="<?php echo Employee::current()->getPhone(); ?>"></td>
			</tr>
			<tr>
				<th><label for="netID">netID</label></th>
				<td><input name="netID" id="netID" type="text" value="<?php echo Employee::current()->getNetID(); ?>" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<?php
				$tempEmail = Employee::current()->getEmail();
				$placeholder = Employee::current()->getNetID() . "@queensu.ca";
				if ($tempEmail == $placeholder) {
					$tempEmail = "";
				}
				?>
				<th><label for="alternateEmail" style="margin: 0; padding: 0;">Alternate Email<br><span style="font-weight: 300; font-size: 0.8em;">(leave blank to use your netID email)</span></label></th>
				<td><input name="alternateEmail" id="alternateEmail" type="email" value="<?php echo $tempEmail; ?>" placeholder="<?php echo $placeholder; ?>"></td>
			</tr>
			<tr>
				<th><label for="faculty">Faculty</label></th>
				<td><input name="faculty" id="faculty" placeholder="<?php echo Employee::current()->getFaculty(); ?>" type="text" value="<?php echo Employee::current()->getFaculty(); ?>"></td>
			</tr>
			<tr>
				<th><label for="major">Major</label></th>
				<td><input name="major" id="major" placeholder="<?php echo Employee::current()->getMajor(); ?>" type="text" value="<?php echo Employee::current()->getMajor(); ?>"></td>
			</tr>
			<tr>
				<th><label for="website">Website <span style="font-weight: 300; font-size: 0.8em;">(optional)</span></label></th>
				<td><input type="url" id="website" name="website" placeholder="http://..." value="<?php echo Employee::current()->getWebsiteURL(); ?>"></td>
			</tr>
			<tr>
				<th><label for="github">GitHub <span style="font-weight: 300; font-size: 0.8em;">(optional)</span></label></th>
				<td><input type="url" id="github" name="github" placeholder="https://github.com/..." value="<?php echo Employee::current()->getGitHubURL(); ?>"></td>
			</tr>
			<tr>
				<th><label for="linkedin">LinkedIn <span style="font-weight: 300; font-size: 0.8em;">(optional)</span></label></th>
				<td><input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." value="<?php echo Employee::current()->getLinkedInURL(); ?>"></td>
			</tr>
			<tr>
				<th><span class="label">Profile Picture</span></th>
				<td>
					<?php if (Employee::current()->getProfilePic()) { ?>
						<div class="profile-pic" style="background-image: url('<?php echo Employee::current()->getProfilePic(); ?>'); float: left;"></div>
						<a href="./?settings&profilepic"><i class="fa fa-picture-o"></i>Change Pic</a>
						<div style="clear: both;"></div>
					<?php } else { ?>
						<a href="./?settings&profilepic"><i class="fa fa-picture-o"></i>Add Pic</a>
					<?php } ?>
				</td>
			</tr>
		</table>

	<?php } else { ?>

		<h2 class="section-heading">Account Email</h2>
		<table>
			<tr>
				<th><label for="email">Email Address</label></th>
				<td><input name="email" id="email" placeholder="email address" type="text" value="<?php echo User::current()->getEmail(); ?>"></td>
			</tr>
		</table>

	<?php } ?>

	</section>

	<input type="hidden" name="settingsSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>
	
<section>
	<h2 class="section-heading">Username/Password</h2>
	<table>
		<tr>
			<th><label for="username">Username</label></th>
			<td><input name="username" id="username" type="text" value="<?php echo User::current()->getUsername(); ?>" disabled></td>
			<td><a href="./?settings&username" title="Change Username"><i class="fa fa-pencil"></i></a></td>
		</tr>
		<tr>
			<th><label for="pass">Password</label></th>
			<td><input name="pass" id="pass" type="password" value="<?php for ($i = 0; $i < 22; $i++) { echo "&bull;"; } ?>" disabled></td>
			<td><a href="./?settings&changepass" title="Change Password"><i class="fa fa-pencil"></i></a></td>
		</tr>
	</table>
</section>

<?php include_once "php/footer.php";
die();