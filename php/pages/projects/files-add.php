<?php

$project = Project::withID($_GET["id"]);
$files   = $project->getAssetsDirectoryList();
$dir     = $files->getDirectory();

$errors  = new ErrorCollector();

if (isset($_POST['fileAddSubmit'])) {

	$continue = true;

	if ($_FILES["fileField"]["error"][0] == UPLOAD_ERR_NO_FILE) {
		$continue = false;
		$errors->addError("Please select at least one file to upload.", ErrorCollector::WARNING);
	}

	if ($continue) {

		if (is_writable($dir)) {
			for ($i = 0; $i < count($_FILES["fileField"]["name"]); $i++) {
				if ($_FILES["fileField"]["size"][$i] > max_file_upload_in_bytes()) {
					$errors->addError("The file \"" . $_FILES["fileField"]["name"][$i] . "\" exceeds the maximum filesize of " . Format::bytes(max_file_upload_in_bytes()) . ".", ErrorCollector::WARNING);
				} else if ($_FILES["fileField"]["error"][$i] != UPLOAD_ERR_OK) {
					$errors->addError("The file \"" . $_FILES["fileField"]["name"][$i] . "\" could not be uploaded.", ErrorCollector::WARNING);
				} else {
					$uploadPath = $dir . DIRECTORY_SEPARATOR . $_FILES["fileField"]["name"][$i];

					if (file_exists($uploadPath)) {
						$j = 1;
						$pathParts = explode(".", $_FILES["fileField"]["name"][$i]);
						if (count($pathParts) == 1) {
							$newPath = $dir . DIRECTORY_SEPARATOR . $_FILES["fileField"]["name"][$i] . " (" . $j . ")";
						} else {
							$extension           = end($pathParts);
							$filenameNoExtension = basename($_FILES["fileField"]["name"][$i], "." . $extension);
							$newPath             = $dir . DIRECTORY_SEPARATOR . $filenameNoExtension . " (" . $j . ")" . "." . $extension;
						}
						while (file_exists($newPath)) {
							$newPath = $dir . DIRECTORY_SEPARATOR . $filenameNoExtension . " (" . $j . ")" . "." . $extension;
							$j++;
						}
						$uploadPath = $newPath;
					}

					$success = move_uploaded_file($_FILES["fileField"]["tmp_name"][$i], $uploadPath);
					if ($success === false) {
						$errors->addError("The file \"" . $_FILES["fileField"]["name"][$i] . "\" could not be uploaded.", ErrorCollector::WARNING);
					}
				}
			}
			if (count($errors) == 0) {
				Auth::redirect("./?projects&id=" . $project->getPID());
			}
		} else {
			$errors->addError("There was a problem writing to the files directory.", ErrorCollector::DANGER);
		}

	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Upload File</h2>

<form action="" method="post" enctype="multipart/form-data">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Select File</h2>

		<input type="file" name="fileField[]" id="fileField" multiple>
		<span id="fileLabel" class="file-input-label"></span>
	</section>

	<input type="hidden" name="fileAddSubmit">
	<button type="submit"><i class="fa fa-upload"></i>Upload File</button>

</form>

<script>
	$(document).ready(function () {
		$('#fileLabel').html("No files selected");
		$('#fileField').change(function() {
			var numFiles = $(this)[0].files.length;
			var label = "";
			if (numFiles == 0) {
				label = "No files selected";
			} else if (numFiles == 1) {
//				label = "1 file selected";
				label = $(this).val();
				var lastIndex = label.lastIndexOf("\\");
				if (lastIndex >= 0) {
					label = label.substring(lastIndex + 1);
				}
			} else {
				label = numFiles + " files selected";
			}
			$('#fileLabel').html(label);
		});
	});
</script>

<?php include_once "php/footer.php";