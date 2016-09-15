<?php

$project = Project::withID($_GET['id']);

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

try {
	$task = $milestone->getTask($_GET['assigntask']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
}

$currentAssignees = $task->getAssignees();

/** @var Employee[] $possibleAssignees */
$possibleAssignees = array();
foreach (Employee::getCurrent() as $employee) {
	$possibleAssignees[$employee->getPID()] = $employee;
}
foreach ($currentAssignees as $assignee) {
	if (!isset($possibleAssignees[$assignee->getPID()])) {
		$possibleAssignees[$assignee->getPID()] = $assignee;
	}
}

$errors = new ErrorCollector();

if (isset($_POST["taskAssignSubmit"])) {

	$assignees = array();
	foreach ($possibleAssignees as $employee) {
		if (isset($_POST[$employee->getPID()])) {
			$assignees[] = $employee->getPID();
		}
	}
	if (count($assignees) > $task->getMaxAssignees()) {
		$errors->addError("You may only assign up to " . $task->getMaxAssignees() . " employees. To assign more, first edit the task details.", ErrorCollector::WARNING);
	} else {
		$task->setAssignees($assignees);
		$result = $project->save();
		if ($result) {
			Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
		} else {
			$errors->addError("An error occurred saving the task.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Assign Task</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Task Assignees</h2>
		<table>
		<?php

		foreach ($possibleAssignees as $employee) { ?>
			<tr>
				<td><input name="<?php echo $employee->getPID(); ?>" id="<?php echo $employee->getPID(); ?>" type="checkbox" <?php echo (in_array($employee, $currentAssignees) || isset($_POST[$employee->getPID()])) ? "checked" : ""; ?>><label for="<?php echo $employee->getPID(); ?>"><?php echo $employee; ?></label></td>
			</tr>
		<?php } ?>
		</table>
	</section>

	<input type="hidden" name="taskAssignSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();