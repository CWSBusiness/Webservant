<?php

$data = array(
	"date" => new DateTime(),
	"description" => "",
	"project" => 0,
	"payer" => "",
	"payee" => "",
	"value" => "",
	"payerNeedsReimbursement" => 1,
	"comments" => "",
	"receiptoption" => 0,
);

$errors = new ErrorCollector();

if (isset($_POST["expenseAddSubmit"])) {

	$continue = true;

	$data["date"] = $_POST["date"];
	if (!Validate::date($data["date"])) {
		$continue = false;
		$errors->addError("Please enter a valid transaction date.", ErrorCollector::WARNING);
	}

	try {
		$data["project"] = (int) $_POST["project"];
		if ($data["project"] < 0) {
			throw new Exception();
		}
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please choose a valid project value.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	if (!Validate::plainText($data["description"])) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	try {
		if (!isset($_POST["payer"])) {
			throw new Exception();
		}
		$data["payer"] = (int) $_POST["payer"];
		if ($data["payer"] <= 0) {
			throw new Exception();
		}
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please choose a valid payer.", ErrorCollector::WARNING);
	}

	$data["payee"] = $_POST["payee"];
	if (!Validate::plainText($data["payee"])) {
		$continue = false;
		$errors->addError("Please enter a valid payee.", ErrorCollector::WARNING);
	}

	$data["payerNeedsReimbursement"] = !isset($_POST["payerNeedsReimbursement"]);

	try {
		$data["value"] = new Money($_POST["value"]);
		if ($data["payerNeedsReimbursement"]) {
			$amountPaid = new Money("0");
		} else {
			$amountPaid = $data["value"];
		}
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid transaction amount.", ErrorCollector::WARNING);
	}

	$data["receiptoption"] = (int) $_POST["receiptoptions"];

	$data["comments"] = $_POST["comments"];
	if (!Validate::HTML($data["comments"], true)) {
		$continue = false;
		$errors->addError("Please validate your comments.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$expense = new FinanceRecord(FinanceRecord::EXPENSE, Format::date($data["date"], Format::MYSQL_DATE_FORMAT), $data["description"], $data["payer"], $data["payee"], $data["value"], $amountPaid, $data["payerNeedsReimbursement"], $data["project"], $data["comments"]);
		$success = $expense->save();
		if ($success) {
			try {
				if ($data["receiptoption"] == 1) {
					$expense->setExpenseReceipt($_FILES["receipt"]);
					$expense->save();
				}
				Auth::redirect("./?finances&expense=" . $expense->getPID());
			} catch (Exception $e) {
				$expense->delete();
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		} else {
			$errors->addError("Unable to create the record.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Add Expense</h2>

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
						<?php if ($data["payer"] == "") { ?>
						<option value="0" disabled selected>Select Payer</option>
						<?php } ?>
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
					<input type="radio" name="receiptoptions" id="receiptnone" value="0" <?php echo ($data["receiptoption"] == 0) ? "checked" : ""; ?>>
					<label for="receiptnone">No receipt</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="receiptoptions" id="receiptfile" value="1" <?php echo ($data["receiptoption"] == 1) ? "checked" : ""; ?>>
					<label for="receiptfile">Upload file</label>
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

	<input type="hidden" name="expenseAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Add Record</button>

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
