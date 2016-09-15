<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

try {
	$employee = Employee::withID($_GET['id']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?employees");
}

$data = array(
	"firstName" => $employee->getFirstName(),
	"lastName" => $employee->getLastName(),
	"position" => $employee->getPosition(),
	"startDate" => $employee->getStartDate(),
	"endDate" => $employee->getEndDate(),
	"phone" => $employee->getPhone(),
	"alternateEmail" => ($employee->getEmail() == $employee->getNetID() . "@queensu.ca") ? "" : $employee->getEmail(),
	"website" => $employee->getWebsiteURL(),
	"github" => $employee->getGitHubURL(),
	"linkedin" => $employee->getLinkedInURL(),
	"birthday" => $employee->getBirthday(),
	"netID" => $employee->getNetID(),
	"faculty" => $employee->getFaculty(),
	"major" => $employee->getMajor(),
);

$errors = new ErrorCollector();

if (isset($_POST['employeeEditSubmit'])) {

	$continue = true;

	$data["position"] = $_POST["position"];
	try {
		$employee->setPosition($data["position"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid position title.", ErrorCollector::WARNING);
	}

	$data["startDate"] = $_POST["startDate"];
	try {
		$employee->setStartDate($data["startDate"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid start date.", ErrorCollector::WARNING);
	}

	$data["endDate"] = $_POST["endDate"];
	try {
		$employee->setEndDate($data["endDate"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The end date you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["phone"] = $_POST["phone"];
	try {
		$employee->setPhone($data["phone"]);
	} Catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The phone number you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["alternateEmail"] = $_POST['alternateEmail'];
	try {
		$employee->setEmail($data['alternateEmail']);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The alternate email address you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["website"] = $_POST["website"];
	try {
		$employee->setWebsiteURL(urldecode($data['website']));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The website URL you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["github"] = $_POST["github"];
	try {
		$employee->setGitHubURL(urldecode($data['github']));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The GitHub URL you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["linkedin"] = $_POST["linkedin"];
	try {
		$employee->setLinkedInURL(urldecode($data['linkedin']));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The LinkedIn URL you entered is invalid.", ErrorCollector::WARNING);
	}

	$data["firstName"] = $_POST["firstName"];
	try {
		$employee->setFirstName($data["firstName"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
	}

	$data["lastName"] = $_POST["lastName"];
	try {
		$employee->setLastName($data["lastName"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
	}

	try {
		$employee->setBirthday($_POST["birthday"]);
		$data["birthday"] = $_POST["birthday"];
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid birthday.", ErrorCollector::WARNING);
	}

	$data["netID"] = $_POST["netID"];
	try {
		$employee->setNetID($data['netID']);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid netID.", ErrorCollector::WARNING);
	}

	$data["faculty"] = $_POST["faculty"];
	try {
		$employee->setFaculty($data['faculty']);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid faculty name.", ErrorCollector::WARNING);
	}

	$data["major"] = $_POST["major"];
	try {
		$employee->setMajor($data['major']);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid major name.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $employee->save();
		if ($success) {
			Auth::redirect("./?employees&id=" . $employee->getPID());
		} else {
			$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
		}
	}


}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; <a href="./?employees&id=<?php echo $employee->getPID(); ?>"><?php echo $employee; ?></a> &gt; Edit</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Employment Details</h2>

		<table>
			<tr>
				<th><label for="position">Position</label></th>
				<td><input name="position" id="position" type="text" value="<?php echo $data["position"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="startDate">Start Date</label></th>
				<td><input name="startDate" id="startDate" type="text" value="<?php echo Format::date($data["startDate"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="endDate">End Date</label></th>
				<td><input name="endDate" id="endDate" type="text" value="<?php echo ($employee->isCurrentEmployee()) ? "" : Format::date($data["endDate"]); ?>" placeholder="Leave blank for current employee"></td>
			</tr>
		</table>

	</section>

	<section>
		<h2 class="section-heading">Contact Info</h2>

		<table>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td><input name="phone" id="phone" type="tel" value="<?php echo Format::phone($data["phone"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="alternateEmail">Alternate Email</label></th>
				<td><input name="alternateEmail" id="alternateEmail" type="email" value="<?php echo ($data["alternateEmail"] == $data["netID"] . "@queensu.ca") ? "" : $data["alternateEmail"]; ?>" placeholder="<?php echo $data["netID"] . "@queensu.ca" ?>"></td>
			</tr>
			<tr>
				<th><label for="website">Website</label></th>
				<td><input type="url" id="website" name="website" placeholder="http://..." value="<?php echo $data["website"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="github">GitHub</label></th>
				<td><input type="url" id="github" name="github" placeholder="https://github.com/..." value="<?php echo $data["github"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="linkedin">LinkedIn</label></th>
				<td><input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." value="<?php echo $data["linkedin"]; ?>"></td>
			</tr>
		</table>

	</section>

	<section>
		<h2 class="section-heading">Profile</h2>

		<table>

			<tr>
				<th><label for="firstName">Full Name</label></th>
				<td>
					<label for="firstName" class="label-hide">First Name</label>
					<input name="firstName" id="firstName" type="text" value="<?php echo $data["firstName"]; ?>">
				</td>
				<td>
					<label for="lastName" class="label-hide">Last Name</label>
					<input name="lastName" id="lastName" type="text" value="<?php echo $data["lastName"]; ?>">
				</td>
			</tr>
			<tr>
				<th><label for="birthday">Birthday</label></th>
				<td colspan="2"><input name="birthday" id="birthday" type="text" value="<?php echo Format::date($data["birthday"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="netID">netID</label></th>
				<td colspan="2"><input name="netID" id="netID" type="text" value="<?php echo $data["netID"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="faculty">Faculty / Major</label></th>
				<td>
					<label for="faculty" class="label-hide">Faculty</label>
					<input name="faculty" id="faculty" type="text" value="<?php echo $data["faculty"]; ?>">
				</td>
				<td>
					<label for="major" class="label-hide">Major</label>
					<input name="major" id="major" type="text" value="<?php echo $data["major"]; ?>">
				</td>
			</tr>
		</table>

	</section>

	<a href="./?employees&id=<?php echo $employee->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Employee</a>
	<input type="hidden" name="employeeEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();