<?php
$project = Project::withID($_GET["id"]);
$milestone = $project->getMilestone($_GET["milestone"]);

$errors = new ErrorCollector();

if (isset($_POST["deleteSubmit"])) {

	$project->deleteMilestone($milestone->getName());
	$result = $project->save();
	if ($result) {
		Auth::redirect("./?projects&id=" . $project->getPID() . "&milestones");
	} else {
		$errors->addError("The milestone could not be deleted.", ErrorCollector::DANGER);
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $_GET['id']; ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $_GET['id']; ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete the milestone &ldquo;<?php echo $milestone; ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?projects&id=<?php echo $_GET['id']; ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edit" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";
die();