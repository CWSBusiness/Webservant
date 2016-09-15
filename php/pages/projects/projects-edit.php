<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./?projects&id=" . $_GET["id"]);
}

try {
	$project = Project::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects");
}

$data = array(
	"name" => $project->getName(),
	"description" => $project->getDescription(),
	"url" => $project->getURL(),
	"clients" => $project->getTechnicalContacts()
);

$errors = new ErrorCollector();

if (isset($_POST["projectsEditSubmit"])) {

	$continue = true;

	$data["name"] = $_POST["projectName"];
	try {
		$project->setName($data["name"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid project name.", ErrorCollector::WARNING);
	}

	$data["url"] = $_POST["projectURL"];
	try {
		$project->setURL($data["url"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid project URL.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	try {
		$project->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid project description.", ErrorCollector::WARNING);
	}

	$data["clients"] = array();
	foreach (Client::getClientsByCompanyID($project->getCompanyID()) as $client) {
		if (isset($_POST["client_" . $client->getPID()])) {
			$data["clients"][$client->getPID()] = $client;
		}
	}
	$project->setTechnicalContacts($data["clients"]);

	if ($continue) {
		$success = $project->save();
		if ($success) {
			Auth::redirect("./?projects&id=" . $project->getPID());
		} else {
			$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Edit</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Project Overview</h2>

		<table>
			<tr>
				<th><label for="projectName">Project Name</label></th>
				<td><input type="text" id="projectName" name="projectName" value="<?php echo $data["name"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="projectURL">Project URL</label></th>
				<td><input type="url" id="projectURL" name="projectURL" value="<?php echo $data["url"]; ?>" placeholder="http://"></td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td><textarea id="description" name="description"><?php echo $data["description"]; ?></textarea></td>
			</tr>
		</table>

	</section>

	<?php if ($project->getCompanyID() > 0) { ?>
	<section>
		<h2 class="section-heading">Contact List</h2>
		<table>
		<?php foreach (Client::getClientsByCompanyID($project->getCompanyID()) as $client) { ?>
			<tr>
				<td>
					<input type="checkbox" name="client_<?php echo $client->getPID(); ?>" id="client_<?php echo $client->getPID(); ?>" value="<?php echo $client->getPID(); ?>" <?php echo (array_key_exists($client->getPID(), $data["clients"])) ? "checked" : ""; ?>>
					<label for="client_<?php echo $client->getPID(); ?>"><?php echo $client; ?></label>
				</td>
			</tr>
		<?php }	?>
		</table>
	</section>
	<?php } ?>

	<a href="./?projects&id=<?php echo $project->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Project</a>
	<input type="hidden" name="projectsEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php
include_once "php/footer.php";
die();