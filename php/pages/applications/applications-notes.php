<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

$notes = $app->getNotes();

$errors = new ErrorCollector();

if (isset($_POST["applicationNotesSubmit"])) {

	$continue = true;

	$notes = $_POST["appNotes"];
	try {
		$app->setNotes($notes);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your input.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $app->save();
		if ($success) {
			Auth::redirect("./?applications&id=" . $app->getPID());
		} else {
			$errors->addError("Your changes were unable to be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; <a href="./?applications&id=<?php echo $app->getPID(); ?>"><?php echo $app; ?></a> &gt; Notes</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Notes</h2>
		<label for="appNotes" class="label-hide">Notes</label>
		<textarea id="appNotes" name="appNotes"><?php echo $notes; ?></textarea>
	</section>

	<input type="hidden" name="applicationNotesSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();