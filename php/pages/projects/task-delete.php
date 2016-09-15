<?php

$project = Project::withID($_GET['id']);
$parsedown = new Parsedown();

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

$taskID = $_GET["deletetask"];

try {
	$task = $milestone->getTask($taskID);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
}

$errors = new ErrorCollector();

if (isset($_POST["deleteSubmit"])) {
	$milestone->deleteTask($taskID);
	$success = $project->save();
	if ($success) {
		Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
	} else {
		$errors->addError("An error occurred deleting the task.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Delete Task</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete the task &ldquo;<?php echo $task; ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edittask=<?php echo $taskID; ?>" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";
die();