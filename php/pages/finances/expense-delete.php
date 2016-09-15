<?php

try {
	$expense = FinanceRecord::withID($_GET["expense"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$errors = new ErrorCollector();

if (isset($_POST['deleteSubmit'])) {
	$result = $expense->delete();
	if ($result) {
		Auth::redirect("./?finances");
	} else {
		$errors->addError("An error occurred trying to delete the record.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; <a href="./?finances&expense=<?php echo $expense->getPID(); ?>">Expense</a> &gt; Delete</h2>

	<form action="" method="post">

		<?php echo $errors; ?>

		<section>
			<h2 class="section-heading">Please Confirm</h2>
			<p>Are you sure you want to delete the record for &ldquo;<?php echo $expense->getDescription(); ?>&rdquo;?</p>
			<p>This action can't be undone.</p>
		</section>

		<input type="hidden" name="deleteSubmit">
		<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
		<a href="./?finances&expense=<?php echo $expense->getPID(); ?>" class="delete-link">cancel</a>

	</form>

<?php include_once "php/footer.php";
die();
