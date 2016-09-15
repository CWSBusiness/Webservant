<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

$employee = Employee::withID($_GET['id']);

$errors = new ErrorCollector();

if (isset($_POST['settingsProfilePicClear'])) {

	try {
		$result = $employee->setProfilePic(false);
		if ($result) {
			Auth::redirect("./?employees&id=" . $employee->getPID());
		} else {
			$errors->addError("An error occurred trying to delete the profile pic.", ErrorCollector::DANGER);
		}
	} catch (RuntimeException $e) {
		$errors->addError($e->getMessage(), ErrorCollector::DANGER);
	}

} else if (isset($_POST['settingsProfilePicSubmit'])) {

	if (isset($_FILES['profilePic']) && $_FILES['profilePic']['size'] > 0) {

		try {
			$result = $employee->setProfilePic($_FILES['profilePic']);
			if ($result) {
				Auth::redirect("./?employees&id=" . $employee->getPID());
			} else {
				$errors->addError("An error occurred trying to upload the profile pic.", ErrorCollector::DANGER);
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

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; <a href="./?employees&id=<?php echo $employee->getPID(); ?>"><?php echo $employee; ?></a> &gt; Profile Pic</h2>

<?php if ($employee->getProfilePic()) { ?>
	<div class="profile-pic profile-pic-huge" style="background-image: url('<?php echo $employee->getProfilePic(); ?>');"></div>
<?php } ?>

<form enctype="multipart/form-data" id="settingsProfilePic" action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Upload a New Picture</h2>
		<label for="profilePic" class="label-hide">Profile Pic</label>
		<input type="file" id="profilePic" name="profilePic">
		<span id="profilePicFileLabel" class="file-input-label"></span>
		<p>Allowed formats: <?php echo Employee::profilePicTypesText(); ?></p>
		<p>Max filesize: <?php echo Employee::profilePicMaxSizeText(); ?></p>

		<input type="hidden" name="settingsProfilePicSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i><?php echo ($employee->getProfilePic()) ? "Change Pic" : "Add Pic" ?></button>
	</section>

</form>

<?php if ($employee->getProfilePic()) { ?>
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