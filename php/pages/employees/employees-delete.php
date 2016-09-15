<?php
$employee = Employee::withID($_GET['id']);
$status_message = "";

$errors = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {
	$success = $employee->delete();
	if ($success) {
		Auth::redirect("./?employees");
	} else {
		$errors->addError("Deletion of the employee record failed.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; <a href="./?employees&id=<?php echo $employee->getPID(); ?>"><?php echo $employee; ?></a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete the employee record for <?php echo $employee; ?>?</p>
		<p>This action can't be undone and is VERY strongly discouraged.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?employees" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";
die();