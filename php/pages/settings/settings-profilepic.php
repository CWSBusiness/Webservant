<?php

if (!User::current()->isEmployee()) {
	Auth::redirect("./?settings");
}

$errors = new ErrorCollector();

if (isset($_POST['settingsProfilePicClear'])) {

	try {
		$result = Employee::current()->setProfilePic(false);
		if ($result) {
			Auth::redirect("./?settings");
		} else {
			$errors->addError("An error occurred trying to delete your profile pic.", ErrorCollector::DANGER);
		}
	} catch (RuntimeException $e) {
		$errors->addError($e->getMessage(), ErrorCollector::DANGER);
	}

} else if (isset($_POST['settingsProfilePicSubmit'])) {

	if (isset($_FILES['profilePic']) && $_FILES['profilePic']['size'] > 0) {

		try {
			$result = Employee::current()->setProfilePic($_FILES['profilePic']);
			if ($result) {
				Auth::redirect("./?settings");
			} else {
				$errors->addError("An error occurred trying to upload your profile pic.", ErrorCollector::DANGER);
			}
		} catch (InvalidArgumentException $e) {
			$errors->addError($e->getMessage(), ErrorCollector::WARNING);
		} catch (RuntimeException $e) {
			$errors->addError($e->getMessage(), ErrorCollector::DANGER);
		}

	} else {
		$errors->addError("Please select a file to upload.", ErrorCollector::WARNING);
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-gears"></i> <a href="./?settings">Settings</a> &gt; Profile Pic</h2>

<?php if (Employee::current()->getProfilePic()) { ?>
	<div class="profile-pic profile-pic-huge" style="background-image: url('<?php echo Employee::current()->getProfilePic(); ?>');"></div>
<?php } ?>

<form enctype="multipart/form-data" action="" method="post">

<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Upload a New Picture</h2>
		<label for="profilePic" class="label-hide">Profile Pic</label>
		<input type="file" id="profilePic" name="profilePic">
		<span id="profilePicFileLabel" class="file-input-label"></span>
		<p>Allowed formats: <?php echo Employee::profilePicTypesText(); ?></p>
		<p>Max filesize: <?php echo Employee::profilePicMaxSizeText(); ?></p>

		<input type="hidden" name="settingsProfilePicSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i><?php echo (Employee::current()->getProfilePic()) ? "Change Pic" : "Add Pic" ?></button>
	</section>

</form>

<?php if (Employee::current()->getProfilePic()) { ?>
	<form action="" method="post">
		<section>
			<h2 class="section-heading">Remove Picture</h2>
			<input type="hidden" name="settingsProfilePicClear">
			<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Remove Pic</button>
		</section>
	</form>
<?php } ?>

<script>
	$(document).ready(function () {
		$('#profilePicFileLabel').html("No file selected");
		$('#profilePic').change(function() {
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$('#profilePicFileLabel').html(filename);
		});
	});
</script>

<?php include_once "php/footer.php";
die();