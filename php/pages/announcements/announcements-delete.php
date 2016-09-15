<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./?announcements");
}

try {
	$announcement = Announcement::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./");
}

$errors = new ErrorCollector();

if (isset($_POST["deleteSubmit"])) {

	$success = $announcement->delete();
	if ($success) {
		Auth::redirect("./?announcements");
	} else {
		$errors->addError("An error occurred trying to delete the announcement.", ErrorCollector::DANGER);
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-bullhorn"></i> <a href="./?announcements">Announcements</a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete the announcement &ldquo;<?php echo $announcement; ?>&rdquo;?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?announcements" class="delete-link">cancel</a>

</form>

<?php
include_once "php/footer.php";