<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

$errors = new ErrorCollector();

$data = array(
	"firstName" => $app->getFirstName(),
	"lastName" => $app->getLastName(),
	"birthday" => $app->getBirthday(),
	"netID" => $app->getNetID(),
	"faculty" => $app->getFaculty(),
	"major" => $app->getMajor(),
	"year" => $app->getYear(),
	"phone" => $app->getPhone(),
	"email" => ($app->getEmail() == $app->getNetID() . "@queensu.ca") ? "" : $app->getEmail(),
	"website" => $app->getWebsiteURL(),
	"github" => $app->getGitHubURL(),
	"positionsApplied" => $app->getPositionsAppliedFor(),
	"questions" => $app->getQuestions()
);

if (isset($_POST['applicationEditSubmit'])) {

	$continue = true;

	$data["firstName"] = $_POST["first_name"];
	try {
		$app->setFirstName($data["firstName"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["lastName"] = $_POST["last_name"];
	try {
		$app->setLastName($data["lastName"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["birthday"] = $_POST["birthday"];
	try {
		$app->setBirthday(Format::date($data["birthday"], Format::MYSQL_DATE_FORMAT));
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid birthday.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["netID"] = $_POST["netID"];
	try {
		$app->setNetID($data["netID"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid netID.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["faculty"] = $_POST["faculty"];
	try {
		$app->setFaculty($data["faculty"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter your faculty name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["major"] = $_POST["major"];
	try {
		$app->setMajor($data["major"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter your major name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["netID"] = preg_replace("/[^0-9]*/i", "", $_POST["year"]);
	try {
		$app->setYear($data["year"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid year.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["phone"] = $_POST["phone"];
	try {
		$app->setPhone($data["phone"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["email"] = $_POST["alternate_email"];
	try {
		$app->setEmail($data["email"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid alternate email address.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["website"] = $_POST["website"];
	try {
		$app->setWebsiteURL($data["website"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid website URL.", ErrorCollector::WARNING);
		$continue = false;
	}

	$data["github"] = $_POST["github"];
	try {
		$app->setGitHubURL($data["github"]);
	} catch (InvalidArgumentException $e) {
		$errors->addError("Please enter a valid GitHub URL.", ErrorCollector::WARNING);
		$continue = false;
	}

	if ($_POST["resume"] == 0) {
		$app->setResumeWithURL("");
	} else if ($_POST["resume"] == 1) {
		if (isset($_FILES["resumeFile"]["size"]) && $_FILES["resumeFile"]["size"] == 0) {
			if (!$app->resumeIsFile()) {
				$continue = false;
				$errors->addError("No file was selected to upload.", ErrorCollector::WARNING);
			}
		} else if (!$app->resumeIsFile() || isset($_FILES["resumeFile"])) {
			try {
				$app->setResumeWithFile($_FILES["resumeFile"]);
			} catch (Exception $e) {
				$continue = false;
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		}
	} else if ($_POST["resume"] == 2) {
		try {
			$app->setResumeWithURL($_POST["resumeLink"]);
		} catch (Exception $e) {
			$errors->addError("Please enter a valid resumé URL.", ErrorCollector::WARNING);
			$continue = false;
		}
	}

	$positionsAppliedFor = array();
	foreach (Employee::positions() as $code => $position) {
		if (isset($_POST["positions_" . $code])) {
			array_push($positionsAppliedFor, $code);
		}
	}
	$data["positionsApplied"] = $positionsAppliedFor;
	$app->setPositionsAppliedFor($data["positionsApplied"]);
	if (empty($app->getPositionsAppliedFor())) {
		$errors->addError("You must choose at least one position.", ErrorCollector::WARNING);
		$continue = false;
	}

	$questions = $app->getQuestions();
	foreach ($questions as $number => $question) {
		$field = "question_" . $number;
		$questions[$number]["answer"] = $_POST[$field];
		if (!Validate::plainText($_POST[$field])) {
			$errors->addError("Please provide an answer for application question " . $number . ".", ErrorCollector::WARNING);
			$continue = false;
		}
	}
	$data["questions"] = $questions;
	$app->setQuestions($data["questions"]);

	if ($continue) {
		$attempt = $app->save();
		if ($attempt) {
			Auth::redirect("./?applications&id=" . $app->getPID());
		} else {
			$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; <a href="./?applications&id=<?php echo $app->getPID(); ?>"><?php echo $app; ?></a> &gt; Edit</h2>

<form enctype="multipart/form-data" id="applicationsEdit" action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Personal Information</h2>
		<table>
			<tr>
				<th><label for="first_name">Full Name</label></th>
				<td>
					<label for="first_name" class="label-hide">First Name</label>
					<input type="text" id="first_name" name="first_name" placeholder="First" value="<?php echo $data["firstName"]; ?>" autocorrect="off" spellcheck="false">
				</td>
				<td>
					<label for="last_name" class="label-hide">Last Name</label>
					<input type="text" id="last_name" name="last_name" placeholder="Last" value="<?php echo $data["lastName"]; ?>" autocorrect="off" spellcheck="false">
				</td>
			</tr>
			<tr>
				<th><label for="birthday">Birthday</label></th>
				<td colspan="2"><input type="text" id="birthday" name="birthday" placeholder="MM/DD/YYYY" value="<?php echo Format::date($data["birthday"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="netID">NetID</label></th>
				<td colspan="2"><input type="text" id="netID" name="netID" placeholder="netID" value="<?Php echo $data["netID"]; ?>" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="faculty">Faculty / Major</label></th>
				<td><input type="text" id="faculty" name="faculty" placeholder="Faculty" value="<?php echo $data["faculty"]; ?>"></td>
				<td><input type="text" id="major" name="major"placeholder="Major" value="<?php echo $data["major"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="year">Year of Study</label></th>
				<td colspan="2"><input type="text" id="year" name="year" placeholder="eg. 2 or 2nd" value="<?php echo (Validate::number($data["year"])) ? Format::ordinal($data["year"]) : $data["year"]; ?>"></td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Contact Information</h2>
		<table>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td colspan="3"><input type="tel" id="phone" name="phone" placeholder="(xxx) xxx-xxxx" value="<?php echo Format::phone($data["phone"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="alternate_email">Alternate Email Address</label></th>
				<td colspan="3"><input type="email" id="alternate_email" name="alternate_email" placeholder="<?php echo $data["netID"]; ?>@queensu.ca" value="<?php echo ($data["email"] == $data["netID"] . "@queensu.ca") ? "" : $data["email"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="website">Website</label></th>
				<td colspan="3"><input type="url" id="website" name="website" placeholder="http://" value="<?php echo $data["website"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="github">GitHub</label></th>
				<td colspan="3"><input type="url" id="github" name="github" placeholder="http://" value="<?php echo $data["github"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="resumeLink">Resumé</label></th>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume0" value="0" <?php echo (!$app->resumeIsURL() && !$app->resumeIsFile()) ? "checked" : ""; ?>>
					<label for="resume0">No Resumé</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume1" value="1" <?php echo ($app->resumeIsFile()) ? "checked" : ""; ?>>
					<?php if ($app->resumeIsFile()) { ?>
					<label for="resume1">Uploaded PDF</label>
					<p><?php echo $app->getResumeFile(); ?></p>
					<label for="resume1">Replace File:</label>
					<?php } else { ?>
					<label for="resume1">Upload PDF</label>
					<?php } ?>
					<br>
					<input type="file" id="resumeFile" name="resumeFile">
					<span id="resumeFileLabel" class="file-input-label"></span>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume2" value="2" <?php echo ($app->resumeIsURL()) ? "checked" : ""; ?>>
					<label for="resume2">Provide a Link (preferred)</label>
					<input type="url" id="resumeLink" name="resumeLink" placeholder="http://" value="<?php echo ($app->resumeIsURL()) ? $app->getResumeURL() : ""; ?>">
				</td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Position Choices</h2>
		<table>
			<?php $positions = Employee::positions();
			foreach ($positions as $code => $position) { ?>
				<tr>
					<td>
						<input type="checkbox" name="positions_<?php echo $code; ?>" id="positions_<?php echo $code; ?>" value="<?php echo $code; ?>" <?php echo ($position["available"]) ?: "disabled"; ?> style="width: auto;" <?php echo (array_key_exists($code, $data["positionsApplied"])) ? "checked" : ""; ?>>
						<label for="positions_<?php echo $code; ?>" style="display: inline-block; <?php echo ($position["available"]) ?: "opacity: 0.5; text-decoration: line-through;" ?>"><?php echo $position["title"]; ?></label>
					</td>
				</tr>
			<?php } ?>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Application Questions</h2>
		<ol>
			<?php foreach ($data["questions"] as $number => $question) { ?>
				<li>
					<label for="question_<?php echo $number ?>"><?php echo $question["question"]; ?></label>
					<textarea id="question_<?php echo $number ?>" name="question_<?php echo $number ?>"><?php echo $question["answer"]; ?></textarea>
				</li>
			<?php } ?>
		</ol>
	</section>

	<input type="hidden" name="applicationEditSubmit">
	<a href="./?applications&id=<?php echo $app->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Application</a>
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<script>
	$(document).ready(function () {
		$('#resumeFileLabel').html("No file selected");
		$('#resumeFile').change(function() {
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$('#resumeFileLabel').html(filename);
		});
	});
</script>

<?php include_once "php/footer.php";
die();