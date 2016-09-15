<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

$employee = array(
	"pid" => 0,
	"first_name" => "",
	"last_name" => "",
	"position" => "",
	"birthday" => "",
	"phone" => "",
	"netID" => "",
	"alternate_email" => "",
	"faculty" => "",
	"major" => "",
	"start_date" => new DateTime(),
	"current_employee" => true,
	"end_date" => "",
	"website" => "",
	"github" => "",
	"linkedin" => ""
);

$errors = new ErrorCollector();

if (isset($_POST['employeeAddSubmit'])) {
	$continue = true;

	$employee["first_name"] = $_POST['firstName'];
	if (!Validate::name($employee["first_name"])) {
		$continue = false;
		$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
	}

	$employee["last_name"] = $_POST["lastName"];
	if (!Validate::name($employee["last_name"])) {
		$continue = false;
		$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
	}

	$employee["position"] = $_POST["position"];
	if (!Validate::plainText($employee['position'])) {
		$continue = false;
		$errors->addError("Please enter a valid position title.", ErrorCollector::WARNING);
	}

	$employee["birthday"] = $_POST["birthday"];
	if (Validate::date($employee['birthday'])) {
		$employee["birthday"] = Format::date($employee["birthday"]);
	} else {
		$continue = false;
		$errors->addError("Please enter a valid birthday.", ErrorCollector::WARNING);
	}

	$employee["phone"] = $_POST["phone"];
	if ($employee["phone"] != "") {
		if (Validate::phone($employee["phone"])) {
			$employee["phone"] = Format::phone($employee["phone"]);
		} else {
			$continue = false;
			$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
		}
	}

	$employee["netID"] = $_POST["netID"];
	if (!Validate::netID($employee['netID'])) {
		$continue = false;
		$errors->addError("Please enter a valid netID.", ErrorCollector::WARNING);
	}

	if (isset($_POST["alternateEmail"])) {
		$employee["alternate_email"] = $_POST["alternateEmail"];
		if ($_POST['alternateEmail'] != "") {
			if (!Validate::email($employee['alternate_email'])) {
				$continue = false;
				$errors->addError("Please enter a valid alternate email.", ErrorCollector::WARNING);
			}
		}
	}

	$employee["faculty"] = $_POST["faculty"];
	if (!Validate::plainText($employee['faculty'])) {
		$continue = false;
		$errors->addError("Please enter a valid faculty name.", ErrorCollector::WARNING);
	}

	$employee["major"] = $_POST["major"];
	if (!Validate::plainText($employee['major'])) {
		$continue = false;
		$errors->addError("Please enter a valid major name.", ErrorCollector::WARNING);
	}

	if ($_POST["startDate"] != "") {
		$employee["start_date"] = $_POST["startDate"];
		if (!Validate::date($employee["start_date"])) {
			$continue = false;
			$errors->addError("Please enter a valid start date.", ErrorCollector::WARNING);
		}
	}

	$employee["website"] = urldecode($_POST["website"]);
	if ($employee["website"] != "" && !Validate::url($employee["website"])) {
		$continue = false;
		$errors->addError("Please enter a valid website URL.", ErrorCollector::WARNING);
	}

	$employee["github"] = urldecode($_POST["github"]);
	if ($employee["github"] != "" && !Validate::url($employee["github"])) {
		$continue = false;
		$errors->addError("Please enter a valid GitHub URL.", ErrorCollector::WARNING);
	}

	$employee["linkedin"] = urldecode($_POST["linkedin"]);
	if ($employee["linkedin"] != "" && !Validate::url($employee["linkedin"])) {
		$continue = false;
		$errors->addError("Please enter a valid LinkedIn URL.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$employee["birthday"] = Format::date($employee["birthday"], Format::MYSQL_DATE_FORMAT);
		$employee["start_date"] = Format::date($employee["start_date"], Format::MYSQL_DATE_FORMAT);

		$entry = new Employee($employee["first_name"], $employee["last_name"], $employee["position"], $employee["birthday"], $employee["netID"], $employee["phone"], $employee["alternate_email"], $employee["faculty"], $employee["major"], $employee["website"], $employee["github"], $employee["linkedin"]);

		$success = $entry->save();
		if ($success) {
			Auth::redirect("./?employees&id=" . $success);
		} else {
			$employee["birthday"] = Format::date($employee["birthday"], Format::DATE_FORMAT);
			$employee["start_date"] = Format::date($employee["start_date"], Format::DATE_FORMAT);
			$errors->addError("The employee could not be created.", ErrorCollector::DANGER);
		}
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; Add</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">New Employee</h2>

		<table>
			<tr>
				<th><label for="firstName">Full Name</label></th>
				<td>
					<label for="firstName" class="label-hide">First Name</label>
					<input name="firstName" id="firstName" type="text" value="<?php echo $employee["first_name"]; ?>" placeholder="First" autocorrect="off" spellcheck="false">
				</td>
				<td>
					<label for="lastName" class="label-hide">Last Name</label>
					<input name="lastName" id="lastName" type="text" value="<?php echo $employee["last_name"]; ?>" placeholder="Last" autocorrect="off" spellcheck="false">
				</td>
			<tr>
				<th><label for="position">Position</label></th>
				<td colspan="2"><input name="position" id="position" type="text" value="<?php echo $employee["position"]; ?>" placeholder="eg. Developer"></td>
			</tr>
			<tr>
				<th><label for="birthday">Birthday</label></th>
				<td colspan="2"><input name="birthday" id="birthday" type="text" value="<?php echo ($employee["birthday"] instanceof DateTime) ? Format::date($employee["birthday"]) : $employee["birthday"] ; ?>" placeholder="MM/DD/YYYY"></td>
			</tr>
			<tr>
				<th><label for="netID">netID</label></th>
				<td colspan="2"><input name="netID" id="netID" type="text" value="<?php echo $employee["netID"]; ?>" placeholder="eg. 11bb37" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="faculty">Faculty / Major</label></th>
				<td>
					<label for="faculty" class="label-hide">Faculty</label>
					<input name="faculty" id="faculty" placeholder="eg. Computing" type="text" value="<?php echo $employee["faculty"]; ?>">
				</td>
				<td>
					<label for="major" class="label-hide">Major</label>
					<input name="major" id="major" placeholder="eg. Software Design" type="text" value="<?php echo $employee["major"]; ?>">
				</td>
			</tr>
			<tr>
				<th><label for="startDate">Start Date</label></th>
				<td colspan="2"><input name="startDate" id="startDate" type="text" value="<?php echo ($employee["start_date"] instanceof DateTime) ? Format::date($employee["start_date"]) : $employee["birthday"]; ?>" placeholder="<?php echo Format::date(new DateTime()); ?> (today)"></td>
			</tr>
		</table>

	</section>

	<section>
		<h2 class="section-heading">Contact Info</h2>

		<table>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td><input name="phone" id="phone" type="tel" value="<?php echo $employee["phone"]; ?>" placeholder="(xxx) xxx-xxxx"></td>
			</tr>
			<tr>
				<th><label for="alternateEmail">Alternate Email</label></th>
				<td><input name="alternateEmail" id="alternateEmail" type="email" value="<?php echo $employee["alternate_email"]; ?>" placeholder="leave blank to use netID@queensu.ca"></td>
			</tr>
			<tr>
				<th><label for="website">Website</label></th>
				<td><input type="url" id="website" name="website" placeholder="http://..." value="<?php echo $employee["website"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="github">GitHub</label></th>
				<td><input type="url" id="github" name="github" placeholder="https://github.com/..." value="<?php echo $employee["github"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="linkedin">LinkedIn</label></th>
				<td><input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." value="<?php echo $employee["linkedin"]; ?>"></td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="employeeAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";
die();