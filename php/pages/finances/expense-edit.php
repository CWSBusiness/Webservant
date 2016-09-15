<?php

try {
	$expense = FinanceRecord::withID($_GET["expense"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$data = array(
	"date" => $expense->getTransactionDate(),
	"description" => $expense->getDescription(),
	"project" => $expense->getProjectID(),
	"payer" => $expense->getPayer()->getPID(),
	"payee" => $expense->getPayee(),
	"value" => $expense->getTransactionValue()->__toString(),
	"payerNeedsReimbursement" => $expense->payerNeedsReimbursement(),
	"comments" => $expense->getComments(),
);

$errors = new ErrorCollector();

if (isset($_POST["expenseEditSubmit"])) {

	$continue = true;

	$data["date"] = $_POST["date"];
	try {
		$expense->setTransactionDate(Format::date($data["date"], Format::MYSQL_DATE_FORMAT));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction date.", ErrorCollector::WARNING);
	}

	$data["project"] = $_POST["project"];
	try {
		$expense->setProjectID($data["project"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please choose a valid project value.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	try {
		$expense->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["payer"] = (int) $_POST["payer"];
	try {
		$expense->setPayer($data["payer"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please choose a valid payer.", ErrorCollector::WARNING);
	}

	$data["payee"] = $_POST["payee"];
	try {
		$expense->setPayee($data["payee"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid payee.", ErrorCollector::WARNING);
	}

	$data["payerNeedsReimbursement"] = !isset($_POST["payerNeedsReimbursement"]);
	$expense->setReimbursementStatus($data["payerNeedsReimbursement"]);

	$data["value"] = $_POST["value"];
	try {
		$value = new Money($data["value"]);
		$expense->setTransactionValue($value);
		if ($data["payerNeedsReimbursement"]) {
			$expense->setAmountPaid(new Money("0"));
		} else {
			$expense->setAmountPaid($value);
		}
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	if ($_POST["receiptoptions"] == 0) {
		$expense->setExpenseReceipt(false);
	} else if ($_POST["receiptoptions"] == 1) {
		if (isset($_FILES["receipt"]["size"]) && $_FILES["receipt"]["size"] == 0) {
			if (!$expense->getExpenseReceipt()) {
				$continue = false;
				$errors->addError("No file was selected to upload.", ErrorCollector::WARNING);
			}
		} else if (!$expense->getExpenseReceipt() || isset($_FILES["receipt"])) {
			try {
				$expense->setExpenseReceipt($_FILES["receipt"]);
			} catch (Exception $e) {
				$continue = false;
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		}
	}

	$data["comments"] = $_POST["comments"];
	try {
		$expense->setComments($data["comments"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $expense->save();
		if ($success) {
			Auth::redirect("./?finances&expense=" . $expense->getPID());
		} else {
			$errors->addError("Unable to save changes.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; <a href="./?finances&expense=<?php echo $expense->getPID(); ?>">Expense</a> &gt; Edit</h2>

<form action="" method="post" enctype="multipart/form-data">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Transaction Details</h2>

		<table>
			<tr>
				<th><label for="date">Date of Transaction</label></th>
				<td colspan="2"><input name="date" id="date" type="text" value="<?php echo Format::date($data["date"]); ?>"></td>
			</tr>
			<tr>
				<th><label for="project">Project</label></th>
				<td colspan="2">
					<select name="project" id="project">
						<option value="0" <?php echo ($data["project"] == 0) ? "selected" : ""; ?>>No Project</option>
						<?php foreach (Project::getAll() as $project) { ?>
						<option value="<?php echo $project->getPID() ?>" <?php echo ($data["project"] == $project->getPID()) ? "selected" : ""; ?>><?php echo $project; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td colspan="2"><input name="description" id="description" type="text" value="<?php echo $data["description"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="payer">Payer</label></th>
				<td colspan="2">
					<select name="payer" id="payer">
						<option value="0" disabled>Current Employees</option>
						<?php foreach (Employee::getCurrent() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>" <?php echo ($data["payer"] == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
						<?php } ?>
						<option value="0" disabled>Past Employees</option>
						<?php foreach (Employee::getPast() as $employee) { ?>
							<option value="<?php echo $employee->getPID(); ?>" <?php echo ($data["payer"] == $employee->getPID()) ? "selected" : ""; ?>><?php echo $employee; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="payee">Payee</label></th>
				<td colspan="2"><input name="payee" id="payee" type="text" value="<?php echo $data["payee"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="value">Transaction Amount</label></th>
				<td colspan="2"><input name="value" id="value" type="text" value="<?php echo $data["value"]; ?>" placeholder="$"></td>
			</tr>
			<tr>
				<th><span class="label">Reimbursed?</span></th>
				<td colspan="2">
					<input type="checkbox" name="payerNeedsReimbursement" id="payerNeedsReimbursement" value="1" <?php echo ($data["payerNeedsReimbursement"]) ? "" : "checked"; ?>>
					<label for="payerNeedsReimbursement" style="display: inline-block;">Payer has been reimbursed</label>
				</td>
			</tr>
			<tr>
				<th><span class="label">Receipt</span></th>
				<td style="vertical-align: top;">
					<input type="radio" name="receiptoptions" id="receiptnone" value="0" <?php echo (!$expense->getExpenseReceipt()) ? "checked" : ""; ?>>
					<label for="receiptnone">No receipt</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="receiptoptions" id="receiptfile" value="1" <?php echo ($expense->getExpenseReceipt()) ? "checked" : ""; ?>>
					<?php if ($expense->getExpenseReceipt()) { ?>
					<label for="receiptfile">Uploaded PDF</label>
					<p><?php echo $expense->getExpenseReceipt(); ?></p>
					<label for="receiptfile">Replace File:</label>
					<?php } else { ?>
					<label for="receiptfile">Upload PDF</label>
					<?php } ?>
					<br>
					<input type="file" name="receipt" id="receipt">
					<span id="receiptFileLabel" class="file-input-label"></span>
				</td>
			</tr>
			<tr>
				<th><label for="comments">Comments</label></th>
				<td colspan="2"><textarea name="comments" id="comments"><?php echo $data["comments"]; ?></textarea></td>
			</tr>
		</table>

	</section>

	<a href="./?finances&expense=<?php echo $expense->getPID(); ?>&delete" class="top-right-button delete-link"><i class="fa fa-trash-o"></i>Delete Transaction</a>
	<input type="hidden" name="expenseEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<script>
	$(document).ready(function () {
		$('#receiptFileLabel').html("No file selected");
		$('#receipt').change(function() {
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$('#receiptFileLabel').html(filename);
		});
	});
</script>

<?php include_once "php/footer.php";
die();
