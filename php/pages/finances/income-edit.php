<?php

try {
	$income = FinanceRecord::withID($_GET["income"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$data = array(
	"date" => $income->getTransactionDate(),
	"description" => $income->getDescription(),
	"payer" => $income->getPayer(),
	"value" => $income->getTransactionValue()->__toString(),
	"comments" => $income->getComments(),
);

$errors = new ErrorCollector();

if (isset($_POST["incomeEditSubmit"])) {

	$continue = true;

	$data["date"] = $_POST["date"];
	try {
		$income->setTransactionDate(Format::date($data["date"], Format::MYSQL_DATE_FORMAT));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction date.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	try {
		$income->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["payer"] = $_POST["payer"];
	try {
		$income->setPayer($data["payer"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid payer.", ErrorCollector::WARNING);
	}

	$data["value"] = $_POST["value"];
	try {
		$value = new Money($data["value"]);
		$income->setTransactionValue($value);
		$income->setAmountPaid($value);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	$data["comments"] = $_POST["comments"];
	try {
		$income->setComments($data["comments"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $income->save();
		if ($success) {
			Auth::redirect("./?finances&income=" . $income->getPID());
		} else {
			$errors->addError("Unable to save changes.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; <a href="./?finances&income=<?php echo $income->getPID(); ?>">Manual Income Record</a> &gt; Edit</h2>

	<form action="" method="post">

		<?php echo $errors; ?>

		<section>
			<h2 class="section-heading">Transaction Details</h2>

			<table>
				<tr>
					<th><label for="date">Date of Transaction</label></th>
					<td><input name="date" id="date" type="text" value="<?php echo Format::date($data["date"]); ?>"></td>
				</tr>
				<tr>
					<th><label for="description">Description</label></th>
					<td><input name="description" id="description" type="text" value="<?php echo $data["description"]; ?>"></td>
				</tr>
				<tr>
					<th><label for="payer">Payer</label></th>
					<td><input name="payer" id="payer" type="text" value="<?php echo $data["payer"]; ?>"></td>
				</tr>
				<tr>
					<th><label for="value">Transaction Amount</label></th>
					<td><input name="value" id="value" type="text" value="<?php echo $data["value"]; ?>" placeholder="$"></td>
				</tr>
				<tr>
					<th><label for="comments">Comments</label></th>
					<td><textarea name="comments" id="comments"><?php echo $data["comments"]; ?></textarea></td>
				</tr>
			</table>

		</section>

		<a href="./?finances&income=<?php echo $income->getPID(); ?>&delete" class="top-right-button delete-link"><i class="fa fa-trash-o"></i>Delete Transaction</a>
		<input type="hidden" name="incomeEditSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

	</form>

<?php include_once "php/footer.php";
die();
