<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

try {
	$employee = Employee::withID($_GET['id']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?employees");
}

$notes = $employee->getNotes();

$errors = new ErrorCollector();

if (isset($_POST["employeeNotesSubmit"])) {

	$continue = true;

	$notes = $_POST["notes"];
	try {
		$employee->setNotes($notes);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your input.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $employee->save();
		if ($success) {
			Auth::redirect("./?employees&id=" . $employee->getPID());
		} else {
			$errors->addError("Your changes were unable to be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; <a href="./?employees&id=<?php echo $employee->getPID(); ?>"><?php echo $employee; ?></a> &gt; Notes</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Notes</h2>
		<label for="notes" class="label-hide">Notes</label>
		<textarea id="notes" name="notes"><?php echo $notes; ?></textarea>
	</section>

	<input type="hidden" name="employeeNotesSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();