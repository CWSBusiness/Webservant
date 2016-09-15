<?php

$data = array(
	"title" => "",
	"contents" => ""
);

$errors = new ErrorCollector();

if (isset($_POST['noteAddSubmit'])) {

	$continue = true;

	$data["title"] = $_POST["noteTitle"];
	if (!Validate::plainText($data["title"])) {
		$continue = false;
		$errors->addError("Please enter a valid note title.", ErrorCollector::WARNING);
	}

	$data["contents"] = $_POST["noteContents"];
	if (!Validate::HTML($_POST['noteContents'], true)) {
		$continue = false;
		$errors->addError("Please validate your note contents.", ErrorCollector::WARNING);
	}

	if ($continue) {
		try {
			$note = new Note(User::current()->getPID(), $data["title"], $data["contents"], new DateTime(), new DateTime());
			$result = $note->save();
			if ($result) {
				Auth::redirect("./?notes&id=" . $result . "&edit");
			} else {
				$errors->addError("An error occurred trying to save the note.", ErrorCollector::DANGER);
			}
		} catch (InvalidArgumentException $e) {
			$errors->addError("An error occurred trying to create the note.", ErrorCollector::DANGER);
		}
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-pencil-square-o"></i> <a href="./?notes">Notes</a> &gt; Add</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<label for="noteTitle" class="label-hide">Title</label>
		<input id="noteTitle" name="noteTitle" class="title" type="text" placeholder="New Note" value="<?php echo $data["title"]; ?>" autofocus required>
		<label for="noteContents" class="label-hide">Contents</label>
		<textarea id="noteContents" name="noteContents" placeholder="Dear diary..."><?php echo $data["contents"]; ?></textarea>
	</section>

	<input type="hidden" name="noteAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";