<?php

$project = Project::withID($_GET['id']);

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

try {
	$task = $milestone->getTask($_GET['edittask']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
}

$data = array(
	"name" => $task->getName(),
	"bounty" => $task->getBounty(),
	"maxAssignees" => $task->getMaxAssignees(),
	"description" => $task->getDescription()
);

$errors = new ErrorCollector();

if (isset($_POST["taskEditSubmit"])) {

	$continue = true;

	$data["name"] = $_POST["taskName"];
	try {
		$task->setName($data["name"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid name for the task.", ErrorCollector::WARNING);
	}

	$data["bounty"] = $_POST["bounty"];
	try {
		$data["bounty"] = new Money($data["bounty"]);
		$task->setBounty($data["bounty"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid bounty.", ErrorCollector::WARNING);
	}

	$data["maxAssignees"] = $_POST["maxAssignees"];
	$numAssignees = count($task->getAssignees());
	if ($data["maxAssignees"] < 1) {
		$continue = false;
		$errors->addError("Please enter a valid number of assignees.", ErrorCollector::WARNING);
	} else if ($data["maxAssignees"] < $numAssignees) {
		$continue = false;
		$errors->addError("There are currently " . $numAssignees . " people assigned to the project. Please manually remove employees from the task or choose a higher number of max assignees.", ErrorCollector::WARNING);
	} else {
		try {
			$task->setMaxAssignees($data["maxAssignees"]);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError($e->getMessage(), ErrorCollector::WARNING);
		}
	}

	$data["description"] = $_POST["description"];
	try {
		$task->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate the description.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $project->save();
		if ($success) {
			Auth::redirect("./?projects&id=" . $project->getPID() . "&milestone=" . $milestone->getNameForParameter());
		} else {
			$errors->addError("An error occurred saving the task.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Edit Task</h2>

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
				<td><input type="text" name="bounty" id="bounty" value="<?php echo $data["bounty"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="maxAssignees">Max Assignees</label></th>
				<td><input type="text" name="maxAssignees" id="maxAssignees" value="<?php echo $data["maxAssignees"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td><textarea name="description" id="description"><?php echo $data["description"]; ?></textarea></td>
			</tr>
		</table>
	</section>

	<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&deletetask=<?php echo $_GET["edittask"]; ?>" class="top-right-button delete-link"><i class="fa fa-trash-o"></i>Delete Task</a>
	<input type="hidden" name="taskEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();