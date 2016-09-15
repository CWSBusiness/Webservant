<?php

try {
	$note = Note::withID($_GET['id']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?notes");
}

if (User::current()->getPID() !== $note->getAuthorID()) {
	Auth::redirect("./?notes");
}

$errors = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {
	$result = $note->delete();
	if ($result) {
		Auth::redirect("./?notes");
	} else {
		$errors->addError("An error occurred trying to delete the note.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-pencil-square-o"></i> <a href="./?notes">Notes</a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete &ldquo;<?php echo $note; ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?notes" class="delete-link">cancel</a>

</form>

<?php
include_once "php/footer.php";