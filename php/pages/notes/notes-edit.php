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

$data = array(
	"title" => $note->getTitle(),
	"contents" => $note->getContents()
);

if (isset($_POST['noteEditSubmit'])) {

	$continue = true;
	$sameValues = true;

	if ($data["title"] != $_POST["noteTitle"]) {
		$data["title"] = $_POST["noteTitle"];
		$sameValues = false;
	}
	try {
		$note->setTitle($data["title"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid note title.", ErrorCollector::WARNING);
	}

	if ($data["contents"] != $_POST["noteContents"]) {
		$data["contents"] = $_POST["noteContents"];
		$sameValues = false;
	}
	try {
		$note->setContents($data["contents"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your note contents.", ErrorCollector::WARNING);
	}

	if ($sameValues) {
		$errors->addError("There are no changes to save.", ErrorCollector::INFO);
	} else if ($continue) {
		$success = $note->save(); // update the note on the server
		if ($success) {
			$errors->addError("Changes saved.", ErrorCollector::SUCCESS);
		} else {
			$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
		}
	}
}
		
include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-pencil-square-o"></i> <a href="./?notes">Notes</a> &gt; Edit</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<label for="noteTitle" class="label-hide">Title</label>
		<input id="noteTitle" name="noteTitle" class="title" type="text" placeholder="<?php echo $data["title"]; ?>" value="<?php echo $data["title"]; ?>">
		<label for="noteContents" class="label-hide">Contents</label>
		<textarea id="noteContents" name="noteContents"><?php echo $data["contents"]; ?></textarea>
	</section>

	<a href="./?notes&id=<?php echo $note->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Note</a>
	<input type="hidden" name="noteEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php
include_once "php/footer.php";