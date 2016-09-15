<?php

$data = array(
	"title" => "",
	"contents" => ""
);

$errors = new ErrorCollector();

if (isset($_POST["docAddSubmit"])) {

	$continue = true;

	$data["title"] = $_POST["docTitle"];
	if (!Validate::plainText($data["title"])) {
		$continue = false;
		$errors->addError("Please enter a valid title.", ErrorCollector::WARNING);
	}

	$data["contents"] = $_POST["docContents"];
	if (!Validate::HTML($data["contents"], true)) {
		$continue = false;
		$errors->addError("The document contains invalid text.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$doc = new Doc($data["title"], $data["contents"]);
		$result = $doc->save();
		if ($result) {
			Auth::redirect("./?docs&id=" . $doc->getPID());
		} else {
			$errors->addError("The document could not be created.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; Add New</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<label for="docTitle" class="label-hide">Title</label>
		<input id="docTitle" name="docTitle" class="title" type="text" placeholder="Document Title" value="<?php echo $data["title"]; ?>" autofocus required>
		<label for="docContents" class="label-hide">Contents</label>
		<textarea id="docContents" name="docContents" placeholder="Type in Markdown, HTML, or a combination of the two."><?php echo $data["contents"]; ?></textarea>
	</section>

	<input type="hidden" name="docAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";
die();