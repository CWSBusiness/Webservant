<?php

try {
	$pay = FinanceRecord::withID($_GET["pay"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$data = array(
	"date" => $pay->getTransactionDate(),
	"description" => $pay->getDescription(),
	"payee" => $pay->getPayee()->getPID(),
	"value" => $pay->getTransactionValue(),
	"amountPaid" => $pay->getAmountPaid(),
	"comments" => $pay->getComments(),
);

$errors = new ErrorCollector();

if (isset($_POST["payEditSubmit"])) {

	$continue = true;

	$data["date"] = $_POST["date"];
	try {
		$pay->setTransactionDate(Format::date($data["date"], Format::MYSQL_DATE_FORMAT));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction date.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	try {
		$pay->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["payee"] = (int) $_POST["payee"];
	try {
		$pay->setPayee($data["payee"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid payer.", ErrorCollector::WARNING);
	}

	$data["value"] = $_POST["value"];
	try {
		$value = new Money($data["value"]);
		$pay->setTransactionValue($value);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	$data["amountPaid"] = $_POST["amountPaid"];
	try {
		$amountPaid = new Money($data["amountPaid"]);
		$pay->setAmountPaid($amountPaid);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid amount paid.", ErrorCollector::WARNING);
	}

	$data["comments"] = $_POST["comments"];
	try {
		$pay->setComments($data["comments"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $pay->save();
		if ($success) {
			Auth::redirect("./?finances&pay=" . $pay->getPID());
		} else {
			$errors->addError("Unable to save changes.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; <a href="./?finances&pay=<?php echo $pay->getPID(); ?>">Manual Pay Record</a> &gt; Edit</h2>

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
					<th><label for="payee">Payee</label></th>
					<td>
						<select name="payee" id="payee">
							<?php if ($data["payee"] == "") { ?>
								<option value="0" disabled selected>Select Payee</option>
							<?php } ?>
							<option value="0" disabled>Current Employees</option>
							<?php foreach (Employee::getCurrent() as $employee) { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($data["payee"] == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php } ?>
							<option value="0" disabled>Past Employees</option>
							<?php foreach (Employee::getPast() as $employee) { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($data["payee"] == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="value">Transaction Amount</label></th>
					<td><input name="value" id="value" type="text" value="<?php echo $data["value"]; ?>" placeholder="$"></td>
				</tr>
				<tr>
					<th><label for="amountPaid">Amount Paid</label></th>
					<td><input name="amountPaid" id="amountPaid" type="text" value="<?php echo $data["amountPaid"]; ?>" placeholder="$"></td>
				</tr>
				<tr>
					<th><label for="comments">Comments</label></th>
					<td><textarea name="comments" id="comments"><?php echo $data["comments"]; ?></textarea></td>
				</tr>
			</table>

		</section>

		<a href="./?finances&pay=<?php echo $pay->getPID(); ?>&delete" class="top-right-button delete-link"><i class="fa fa-trash-o"></i>Delete Transaction</a>
		<input type="hidden" name="payEditSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

	</form>

<?php include_once "php/footer.php";
die();
