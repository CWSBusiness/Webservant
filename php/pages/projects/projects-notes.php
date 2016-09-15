<?php
$project = Project::withID($_GET['id']);

$notes = $project->getNotes();

$errors = new ErrorCollector();

if (isset($_POST["noteEditSubmit"])) {

	$continue = true;

	$notes = $_POST["noteContents"];
	try {
		$project->setNotes($notes);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your input.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $project->save();
		if ($success) {
			Auth::redirect("./?projects&id=" . $project->getPID());
		} else {
			$errors->addError("Your changes were unable to be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Notes</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Notes</h2>
		<label for="noteContents" class="label-hide">Contents</label>
		<textarea id="noteContents" name="noteContents"><?php echo $notes; ?></textarea>
	</section>

	<input type="hidden" name="noteEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php
include_once "php/footer.php";
die();