<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

$errors = new ErrorCollector();

if (isset($_POST["deleteSubmit"])) {
	$result = $app->delete();
	if ($result) {
		Auth::redirect("./?applications");
	} else {
		$errors->addError("An error occurred trying to delete the application.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; <a href="./?applications&id=<?php echo $app->getPID(); ?>"><?php echo $app; ?></a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete <?php echo $app->getFirstName(); ?>'s application?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?applications&id=<?php echo $app->getPID(); ?>" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";
die();