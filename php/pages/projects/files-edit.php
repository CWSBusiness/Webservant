<?php

$project = Project::withID($_GET["id"]);
$files   = $project->getAssetsDirectoryList();
$dir     = $files->getDirectory();
$file    = urldecode($_GET["file"]);

$errors  = new ErrorCollector();

if (isset($_POST['fileEditSubmit'])) {

	$continue = true;

	$currentPath = $dir . DIRECTORY_SEPARATOR . $file;
	$newPath     = $dir . DIRECTORY_SEPARATOR . $_POST["filename"];

	if ($currentPath == $newPath) {
		Auth::redirect("./?projects&id=" . $project->getPID());
	}
	if (!is_writable($dir) || !is_writable($currentPath)) {
		$continue = false;
		$errors->addError("The files directory is not writable.", ErrorCollector::DANGER);
	} else if (!Validate::plainText($_POST["filename"])) {
		$continue = false;
		$errors->addError("Please enter a valid file name.", ErrorCollector::WARNING);
	} else if (file_exists($newPath)) {
		$continue = false;
		$errors->addError("The filename you chose is taken.", ErrorCollector::WARNING);
		$file = $_POST["filename"];
	}

	if ($continue) {

		$success = rename($currentPath, $newPath);
		if ($success !== false) {
			Auth::redirect("./?projects&id=" . $project->getPID());
		} else {
			$errors->addError("The file could not be renamed.", ErrorCollector::DANGER);
		}

	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Edit File</h2>

	<form action="" method="post">

		<?php echo $errors; ?>

		<section>
			<h2 class="section-heading">Rename</h2>

			<label for="filename" class="label-hide">New Name</label>
			<input type="text" name="filename" id="filename" placeholder="<?php echo $file; ?>" value="<?php echo $file; ?>">

		</section>

		<input type="hidden" name="fileEditSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

	</form>

<?php include_once "php/footer.php";