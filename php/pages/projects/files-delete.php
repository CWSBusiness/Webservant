<?php

$project = Project::withID($_GET["id"]);
$files   = $project->getAssetsDirectoryList();
$dir     = $files->getDirectory();
$file    = urldecode($_GET["file"]);

$errors  = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {

	$continue = true;

	$path = $dir . DIRECTORY_SEPARATOR . $file;

	if (!is_file($path) || !is_writable($path) || !is_writable($dir)) {
		$continue = false;
		$errors->addError("The file could not be deleted.", ErrorCollector::DANGER);
	}

	if ($continue) {

		$success = unlink($path);
		if ($success !== false) {
			Auth::redirect("./?projects&id=" . $project->getPID());
		} else {
			$errors->addError("The file could not be deleted.", ErrorCollector::DANGER);
		}

	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Delete File</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete the file &ldquo;<?php echo $file; ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?projects" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";