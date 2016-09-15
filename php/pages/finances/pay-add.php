<?php

$data = array(
	"date" => new DateTime(),
	"description" => "",
	"payee" => "",
	"value" => "",
	"amountPaid" => "",
	"comments" => "",
);

$errors = new ErrorCollector();

if (isset($_POST["payAddSubmit"])) {

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

	if (isset($_POST["payee"]) && $_POST["payee"]) {
		$data["payee"] = (int) $_POST["payee"];
	} else {
		$continue = false;
		$errors->addError("Please select a valid payee.", ErrorCollector::WARNING);
	}

	try {
		$data["value"] = new Money($_POST["value"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	try {
		$data["amountPaid"] = new Money($_POST["amountPaid"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid amount paid.", ErrorCollector::WARNING);
	}

	$data["comments"] = $_POST["comments"];
	if (!Validate::HTML($data["comments"], true)) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {

		$payment = new FinanceRecord(FinanceRecord::EMPLOYEE_PAYMENT, Format::date($data["date"], Format::MYSQL_DATE_FORMAT), $data["description"], COMPANY_NAME, $data["payee"], $data["value"], $data["amountPaid"], false, 0, $data["comments"]);

		$success = $payment->save();
		if ($success) {
			Auth::redirect("./?finances&pay=" . $payment->getPID());
		} else {
			$errors->addError("Unable to create the record.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Add Manual Pay Record</h2>

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

		<input type="hidden" name="payAddSubmit">
		<button type="submit"><i class="fa fa-check-circle"></i>Add Record</button>

	</form>

<?php include_once "php/footer.php";
die();
