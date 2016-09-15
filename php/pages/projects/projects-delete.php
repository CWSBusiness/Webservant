<?php

try {
	$project = Project::withID($_GET['id']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects");
}

$errors = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {
	$result = $project->delete();
	if ($result) {
		Auth::redirect("./?projects");
	} else {
		$errors->addError("An error occurred trying to delete the project.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete &ldquo;<?php echo $project->getName(); ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>" class="delete-link">cancel</a>
</form>

<?php
include_once "php/footer.php";
die();