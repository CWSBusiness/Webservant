<?php

$project = Project::withID($_GET['id']);

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

$data = array(
	"name" => "",
	"bounty" => "",
	"maxAssignees" => "",
	"description" => ""
);

$errors = new ErrorCollector();

if (isset($_POST["taskAddSubmit"])) {

	$continue = true;

	$data["name"] = $_POST["taskName"];
	if (!Validate::plainText($data["name"])) {
		$continue = false;
		$errors->addError("Please enter a valid name for the task.", ErrorCollector::WARNING);
	}

	$data["bounty"] = $_POST["bounty"];
	try {
		$data["bounty"] = new Money($data["bounty"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid bounty.", ErrorCollector::WARNING);
	}

	$data["maxAssignees"] = $_POST["maxAssignees"];
	if (ctype_digit($data["maxAssignees"])) {
		if ($data["maxAssignees"] < 1) {
			$continue = false;
			$errors->addError("You must allow at least one assignee.", ErrorCollector::WARNING);
		}
	} else {
		$continue = false;
		$errors->addError("Please enter a valid number of assignees.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	if (!Validate::plainText($data["description"], true)) {
		$continue = false;
		$errors->addError("Please validate the description.", ErrorCollector::WARNING);
	}

	if ($continue) {
		try {
			$task = new Task($project->getPID(), $data["name"], $data["description"], $data["bounty"], $data["maxAssignees"]);
			$milestone->addTask($task);
			$project->save();
			Auth::redirect("./?projects&id=" . $project->getPID() . "&milestone=" . $milestone->getNameForParameter());
		} catch (InvalidArgumentException $e) {
			$errors->addError("An error occurred creating the task.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Add Task</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Task Details</h2>
		<table>
			<tr>
				<th><label for="taskName">Name</label></th>
				<td><input type="text" name="taskName" id="taskName" value="<?php echo $data["name"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="bounty">Bounty</label></th>
				<td><input type="text" name="bounty" id="bounty" placeholder="$" value="<?php echo $data["bounty"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="maxAssignees">Max Assignees</label></th>
				<td><input type="text" name="maxAssignees" id="maxAssignees" placeholder="1" value="<?php echo $data["maxAssignees"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td><textarea name="description" id="description"><?php echo $data["description"]; ?></textarea></td>
			</tr>
		</table>
	</section>

	<input type="hidden" name="taskAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";
die();