<?php

$doc = Doc::withID($_GET["id"]);

$errors = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {
	$result = $doc->delete();
	if ($result) {
		Auth::redirect("./?docs");
	} else {
		$errors->addError("An error occurred trying to delete the doc.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; Delete</h2>

	<form action="" method="post">

		<?php echo $errors; ?>

		<section>
			<h2 class="section-heading">Please Confirm</h2>
			<p>Are you sure you want to delete &ldquo;<?php echo $doc; ?>&rdquo;?</p>
			<p>This action can't be undone.</p>
		</section>

		<input type="hidden" name="deleteSubmit">
		<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
		<a href="./?docs&id=<?php echo $doc->getPID(); ?>" class="delete-link">cancel</a>

	</form>

<?php
include_once "php/footer.php";