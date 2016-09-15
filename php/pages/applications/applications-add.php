<?php

$row = array(
	"first_name" => "",
	"last_name" => "",
	"birthday" => "",
	"phone" => "",
	"netID" => "",
	"faculty" => "",
	"major" => "",
	"year" => "",
	"alternate_email" => "",
	"website" => "",
	"github" => "",
	"cover_letter_link" => "",
	"notes" => "",
	"positions_applied_for" => array(),
	"questions" => array(),
	"date" => new DateTime(),
	"status" => 0
);

foreach (JobApp::currentQuestions() as $number => $question) {
	$row["questions"][$number] = array();
	$row["questions"][$number]["question"] = $question;
	$row["questions"][$number]["answer"] = "";
}

$resume0selected = true;
$resume1selected = false;
$resume2selected = false;

$errors = new ErrorCollector();

if (isset($_POST['applicationAddSubmit'])) {

	$continue = true;

	$row["first_name"] = $_POST["first_name"];
	if (!Validate::name($_POST["first_name"])) {
		$errors->addError("Please enter your first name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["last_name"] = $_POST["last_name"];
	if (!Validate::name($_POST["last_name"])) {
		$errors->addError("Please enter your last name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["birthday"] = $_POST["birthday"];
	if (!Validate::date($_POST['birthday'])) {
		$errors->addError("Please enter your date of birth.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["netID"] = $_POST["netID"];
	if (!Validate::netID($_POST["netID"])) {
		$errors->addError("Please enter a valid netID.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["faculty"] = $_POST["faculty"];
	if (!Validate::plainText($_POST["faculty"])) {
		$errors->addError("Please enter your faculty name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["major"] = $_POST["major"];
	if (!Validate::plainText($_POST["major"])) {
		$errors->addError("Please enter your major name.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["year"] = preg_replace("/[^0-9]*/i", "", $_POST["year"]);
	if (!Validate::number($row["year"])) {
		$errors->addError("Please enter a valid year.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["phone"] = $_POST["phone"];
	if (!Validate::phone($row["phone"])) {
		$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
		$continue = false;
	} else {
		$row["phone"] = Format::phone($row["phone"]);
	}

	$row["alternate_email"] = $_POST["alternate_email"];
	if ($_POST["alternate_email"] != "" && !Validate::email($_POST["alternate_email"])) {
		$errors->addError("Please enter a valid alternate email address.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["website"] = $_POST["website"];
	if ($_POST["website"] != "" && !Validate::url($_POST["website"])) {
		$errors->addError("Please enter a valid website URL.", ErrorCollector::WARNING);
		$continue = false;
	}

	$row["github"] = $_POST["github"];
	if ($_POST["github"] != "" && !Validate::url($_POST["github"])) {
		$errors->addError("Please enter a valid GitHub URL.", ErrorCollector::WARNING);
		$continue = false;
	}

	if ($_POST["resume"] == 1) {
		$resume0selected = false;
		$resume1selected = true;
	} else if ($_POST["resume"] == 2) {
		$resume0selected = false;
		$resume2selected = true;
	}

	foreach (Employee::positions() as $code => $position) {
		if (isset($_POST["positions_" . $code])) {
			array_push($row["positions_applied_for"], $code);
		}
	}
	if (empty($row["positions_applied_for"])) {
		$errors->addError("You must choose at least one position.", ErrorCollector::WARNING);
		$continue = false;
	}

	foreach ($row["questions"] as $number => $question) {
		$field = "question_" . $number;
		$row["questions"][$number]["answer"] = $_POST[$field];
		if (!Validate::plainText($_POST[$field])) {
			$errors->addError("Please provide an answer for application question " . $number . ".", ErrorCollector::WARNING);
			$continue = false;
		}
	}

	$temp = DateTime::createFromFormat(Format::DATE_FORMAT, $row["birthday"]);
	if ($temp === false) {
		$errors->addError("Please enter a valid birthday.", ErrorCollector::WARNING);
		$continue = false;
	} else {
		$row["birthday"] = clone $temp;
	}
	unset($temp);


	if ($continue) {

		$app = new JobApp($row["first_name"], $row["last_name"], $row["birthday"], $row["phone"], $row["netID"], $row["alternate_email"], $row["faculty"], $row["major"], $row["year"], $row["positions_applied_for"], $row["status"], $row["questions"], $row["website"], $row["github"], new DateTime());
		$attempt = $app->save();
		if ($attempt) {
			try {
				if ($resume1selected) {
					$app->setResumeWithFile($_FILES["resumeFile"]);
					$app->save();
				} else if ($resume2selected) {
					$app->setResumeWithURL($_POST["resumeLink"]);
					$app->save();
				}
				Auth::redirect("./?applications&id=" . $attempt);
			} catch (Exception $e) {
				$app->delete();
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		} else {
			$errors->addError("The application was unable to be created.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; New Application</h2>

<form enctype="multipart/form-data" id="applicationsAdd" action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Personal Information</h2>
		<table>
			<tr>
				<th><label for="first_name">Full Name</label></th>
				<td>
					<label for="first_name" class="label-hide">First Name</label>
					<input type="text" id="first_name" name="first_name" placeholder="First" value="<?php echo $row["first_name"]; ?>" autocorrect="off" spellcheck="false">
				</td>
				<td>
					<label for="last_name" class="label-hide">Last Name</label>
					<input type="text" id="last_name" name="last_name" placeholder="Last" value="<?php echo $row["last_name"]; ?>" autocorrect="off" spellcheck="false">
				</td>
			</tr>
			<tr>
				<th><label for="birthday">Birthday</label></th>
				<td colspan="2"><input type="text" id="birthday" name="birthday" placeholder="MM/DD/YYYY" value="<?php echo (Validate::date($row["birthday"])) ? Format::date($row["birthday"]) : $row["birthday"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="netID">NetID</label></th>
				<td colspan="2"><input type="text" id="netID" name="netID" placeholder="eg. 11bb37" value="<?Php echo $row["netID"]; ?>" autocorrect="off" autocapitalize="off" spellcheck="false"></td>
			</tr>
			<tr>
				<th><label for="faculty">Faculty / Major</label></th>
				<td><input type="text" id="faculty" name="faculty" placeholder="eg. Computing" value="<?php echo $row["faculty"]; ?>"></td>
				<td><input type="text" id="major" name="major"placeholder="eg. Software Design" value="<?php echo $row["major"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="year">Year of Study</label></th>
				<td colspan="2"><input type="text" id="year" name="year" placeholder="eg. 2nd" value="<?php echo (Validate::number($row["year"])) ? Format::ordinal($row["year"]) : $row["year"]; ?>"></td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Contact Information</h2>
		<table>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td colspan="3"><input type="tel" id="phone" name="phone" placeholder="(xxx) xxx-xxxx" value="<?php echo $row["phone"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="alternate_email">Alternate Email Address</label></th>
				<td colspan="3"><input type="email" id="alternate_email" name="alternate_email" placeholder="leave blank to use netID@queensu.ca" value="<?php echo $row["alternate_email"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="website">Website</label></th>
				<td colspan="3"><input type="url" id="website" name="website" placeholder="http://" value="<?php echo $row["website"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="github">GitHub</label></th>
				<td colspan="3"><input type="url" id="github" name="github" placeholder="http://" value="<?php echo $row["github"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="resumeLink">Resumé</label></th>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume0" value="0" <?php echo ($resume0selected) ? "checked" : ""; ?>>
					<label for="resume0">No Resumé</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume1" value="1" <?php echo ($resume1selected) ? "checked" : ""; ?>>
					<label for="resume1">Upload PDF</label>
					<br>
					<input type="file" id="resumeFile" name="resumeFile">
					<span id="resumeFileLabel" class="file-input-label"></span>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="resume" id="resume2" value="2" <?php echo ($resume2selected) ? "checked" : ""; ?>>
					<label for="resume2">Provide a Link (preferred)</label>
					<input type="url" id="resumeLink" name="resumeLink" placeholder="http://">
				</td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Position Choices</h2>
		<table>
			<?php foreach (Employee::positions() as $code => $position) { ?>
				<tr>
					<td>
						<input type="checkbox" name="positions_<?php echo $code; ?>" id="positions_<?php echo $code; ?>" value="<?php echo $code; ?>" <?php echo ($position["available"]) ? "" : "disabled"; ?> <?php echo (in_array($code, $row["positions_applied_for"])) ? "checked" : ""; ?>>
						<label for="positions_<?php echo $code; ?>" style="display: inline-block; <?php echo ($position["available"]) ?: "opacity: 0.5; text-decoration: line-through;" ?>"><?php echo $position["title"]; ?></label>
					</td>
				</tr>
			<?php } ?>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Application Questions</h2>
		<ol>
			<?php foreach ($row["questions"] as $number => $question) { ?>
				<li>
					<label for="question_<?php echo $number ?>"><?php echo $question["question"]; ?></label>
					<textarea id="question_<?php echo $number ?>" name="question_<?php echo $number ?>"><?php echo $question["answer"]; ?></textarea>
				</li>
			<?php } ?>
		</ol>
	</section>
		
	<input type="hidden" name="applicationAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>
		
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