<?php

$doc = Doc::withID($_GET["id"]);

$data = array(
	"title" => $doc->getTitle(),
	"contents" => $doc->getContents()
);

$errors = new ErrorCollector();

if (isset($_POST["docEditSubmit"])) {

	$continue = true;
	$sameValues = true;

	if ($data["title"] != $_POST["docTitle"]) {
		$data["title"] = $_POST["docTitle"];
		$sameValues = false;
	}
	try {
		$doc->setTitle($data["title"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid title.", ErrorCollector::WARNING);
	}

	if ($data["contents"] != $_POST["docContents"]) {
		$data["contents"] = $_POST["docContents"];
		$sameValues = false;
	}
	try {
		$doc->setContents($data["contents"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("The document contains invalid text.", ErrorCollector::WARNING);
	}

	if ($sameValues) {
		$errors->addError("There are no changes to save.", ErrorCollector::INFO);
	} else if ($continue) {
		$result = $doc->save();
		if ($result) {
			Auth::redirect("./?docs&id=" . $doc->getPID());
		} else {
			$errors->addError("The document could not be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; Edit</h2>

	<form action="" method="post">

		<?php echo $errors; ?>

		<section>
			<label for="docTitle" class="label-hide">Title</label>
			<input id="docTitle" name="docTitle" class="title" type="text" placeholder="Document Title" value="<?php echo $data["title"]; ?>" autofocus required>
			<label for="docContents" class="label-hide">Contents</label>
			<textarea id="docContents" name="docContents"><?php echo $data["contents"]; ?></textarea>
		</section>

		<a href="./?docs&id=<?php echo $doc->getPID(); ?>&delete" class="top-right-button delete-link"><i class="fa fa-trash-o"></i>Delete Doc</a>
		<input type="hidden" name="docEditSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

	</form>

<?php include_once "php/footer.php";
die();