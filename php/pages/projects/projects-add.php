<?php

$project = array(
	"name" => "",
	"description" => "",
	"companyID" => "",
	"url" => "",
);

$errors = new ErrorCollector();

if (isset($_POST["projectsAddSubmit"])) {

	$continue = true;

	$project["name"] = $_POST["projectName"];
	if (!Validate::plainText($project["name"])) {
		$continue = false;
		$errors->addError("Please enter a valid project name.", ErrorCollector::WARNING);
	}

	$project["url"] = $_POST["projectURL"];
	if ($project["url"] != "" && !Validate::url($project["url"])) {
		$continue = false;
		$errors->addError("Please enter a valid project URL.", ErrorCollector::WARNING);
	}

	if (isset($_POST["projectClient"])) {
		$project["companyID"] = $_POST["projectClient"];
		if ($project["companyID"] == "" || !ctype_digit($project["companyID"])) {
			$continue = false;
			$errors->addError("Please choose an existing client organization.", ErrorCollector::WARNING);
		}
	} else {
		$continue = false;
		$errors->addError("Please choose a client organization.", ErrorCollector::WARNING);
	}

	$project["description"] = $_POST["description"];
	if (!Validate::plainText($project["description"], true)) {
		$continue = false;
		$errors->addError("Please validate the project description.", ErrorCollector::WARNING);
	}

	if ($continue) {
		try {
			$newProject = new Project($project["name"], $project["companyID"], array(), array(), $project["url"], $project["description"]);
			$success = $newProject->save();
			if ($success) {
				Auth::redirect("./?projects&id=" . $newProject->getPID());
			} else {
				throw new InvalidArgumentException();
			}
		} catch (InvalidArgumentException $e) {
			$errors->addError("The project could not be created.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; Add New</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Project Overview</h2>

		<table>
			<tr>
				<th><label for="projectName">Project Name</label></th>
				<td><input type="text" id="projectName" name="projectName" placeholder="Project Name" value="<?php echo $project["name"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="projectURL">Project URL</label></th>
				<td><input type="url" id="projectURL" name="projectURL" placeholder="http://" value="<?php echo $project["url"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="projectClient">Client</label></th>
				<td>
					<select name="projectClient" id="projectClient">
						<?php if ($project["companyID"] == "") { ?>
						<option value="" disabled selected>Select Organization</option>
						<?php } ?>
						<option value="0" <?php echo (ctype_digit($project["companyID"]) && $project["companyID"] == 0) ? "selected" : ""; ?>>Internal Project (<?php echo COMPANY_NAME; ?>)</option>
						<option value="" disabled>Clients</option>
						<?php foreach (Client::getAllCompanies() as $clientID => $clientName) { ?>
							<option value="<?php echo $clientID; ?>" <?php echo ($project["companyID"] == $clientID) ? "selected" : ""; ?>><?php echo $clientName; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td><textarea id="description" name="description" placeholder="A brief overview of the project's details, goals, and specifications"><?php echo $project["description"]; ?></textarea></td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="projectsAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";

die();