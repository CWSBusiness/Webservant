<?php

$data = array(
	"date" => new DateTime(),
	"description" => "",
	"payer" => "",
	"value" => "",
	"comments" => "",
);

$errors = new ErrorCollector();

if (isset($_POST["incomeAddSubmit"])) {

	$continue = true;

	$data["date"] = $_POST["date"];
	if (!Validate::date($data["date"])) {
		$continue = false;
		$errors->addError("Please enter a valid transaction date.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	if (!Validate::plainText($data["description"])) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["payer"] = $_POST["payer"];
	if (!Validate::plainText($data["payer"])) {
		$continue = false;
		$errors->addError("Please enter a valid payer.", ErrorCollector::WARNING);
	}

	try {
		$data["value"] = new Money($_POST["value"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	$data["comments"] = $_POST["comments"];
	if (!Validate::HTML($data["comments"], true)) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {

		$income = new FinanceRecord(FinanceRecord::INCOMING_PAYMENT, Format::date($data["date"], Format::MYSQL_DATE_FORMAT), $data["description"], $data["payer"], COMPANY_NAME, $data["value"], $data["value"], false, 0, $data["comments"]);

		$success = $income->save();
		if ($success) {
			Auth::redirect("./?finances&income=" . $income->getPID());
		} else {
			$errors->addError("Unable to create the record.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Add Manual Income Record</h2>

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

		<input type="hidden" name="incomeAddSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Add Record</button>

	</form>

<?php include_once "php/footer.php";
die();
