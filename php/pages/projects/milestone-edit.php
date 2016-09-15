<?php
$project = Project::withID($_GET["id"]);
$milestone = $project->getMilestone($_GET["milestone"]);

$data = array(
	"status" => $milestone->getStatus(),
	"revenue" => $milestone->getRevenue(),
	"amountPaid" => $milestone->getAmountPaid(),
	"dueDate" => $milestone->getDueDate(),
	"teamLeadID" => $milestone->getTeamLeadID(),
	"teamLeadPay" => $milestone->getTeamLeadPay(),
	"description" => $milestone->getDescription()
);

$errors = new ErrorCollector();

if (isset($_POST["milestoneEditSubmit"])) {

	$continue = true;

	$data["dueDate"] = $_POST["dueDate"];
	try {
		$data["dueDate"] = DateTime::createFromFormat(Format::DATE_FORMAT, $data["dueDate"]);
		$milestone->setDueDate($data["dueDate"]);
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid due date.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	try {
		$milestone->setDescription($data["description"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["status"] = $_POST["milestoneStatus"];
	try {
		$milestone->setStatus($data["status"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please select a valid status.", ErrorCollector::WARNING);
	}

	$data["teamLeadID"] = $_POST["teamLead"];
	try {
		$milestone->setTeamLeadID($data["teamLeadID"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please select a valid team lead option.", ErrorCollector::WARNING);
	}

	if ($_POST["contractoptions"] == 0) {
		$milestone->setContractFile(false);
	} else if ($_POST["contractoptions"] == 1) {
		if (isset($_FILES["contract"]["size"]) && $_FILES["contract"]["size"] == 0) {
			if (!$milestone->getContractFile()) {
				$continue = false;
				$errors->addError("No contract file was selected to upload.", ErrorCollector::WARNING);
			}
		} else if (!$milestone->getContractFile() || isset($_FILES["contract"])) {
			try {
				$milestone->setContractFile($_FILES["contract"]);
			} catch (Exception $e) {
				$continue = false;
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		}
	}

	if ($_POST["invoiceoptions"] == 0) {
		$milestone->setInvoiceFile(false);
	} else if ($_POST["invoiceoptions"] == 1) {
		if (isset($_FILES["invoice"]["size"]) && $_FILES["invoice"]["size"] == 0) {
			if (!$milestone->getInvoiceFile()) {
				$continue = false;
				$errors->addError("No invoice file was selected to upload.", ErrorCollector::WARNING);
			}
		} else if (!$milestone->getInvoiceFile() || isset($_FILES["invoice"])) {
			try {
				$milestone->setInvoiceFile($_FILES["invoice"]);
			} catch (Exception $e) {
				$continue = false;
				$errors->addError($e->getMessage(), ErrorCollector::WARNING);
			}
		}
	}

	$data["revenue"] = $_POST["revenue"];
	try {
		$data["revenue"] = new Money($data["revenue"]);
		$milestone->setRevenue($data["revenue"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid revenue value.", ErrorCollector::WARNING);
	}

	$data["amountPaid"] = $_POST["amountPaid"];
	try {
		$data["amountPaid"] = new Money($data["amountPaid"]);
		$milestone->setAmountPaid($data["amountPaid"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid amount paid.", ErrorCollector::WARNING);
	}

	$teamLeadPaySet = true;
	try {
		$data["teamLeadPay"]->setTransactionValue(new Money($_POST["teamLeadPay"]));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$teamLeadPaySet = false;
		$errors->addError("Please enter a valid team lead total pay amount.", ErrorCollector::WARNING);
	}
	try {
		$data["teamLeadPay"]->setAmountPaid(new Money($_POST["teamLeadPayRunning"]));
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$teamLeadPaySet = false;
		$errors->addError("Please select a valid team lead currently paid amount.", ErrorCollector::WARNING);
	}
	if ($teamLeadPaySet) {
		$milestone->setTeamLeadPay($data["teamLeadPay"]);
	}

	if ($continue) {
		try {
			$project->save();
			Auth::redirect("./?projects&id=" . $project->getPID() . "&milestone=" . $milestone->getNameForParameter());
		} catch (InvalidArgumentException $e) {
			$errors->addError("Your changes could not be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $_GET['id']; ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <a href="./?projects&id=<?php echo $_GET['id']; ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a> &gt; Edit</h2>

<form action="" method="post" enctype="multipart/form-data">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Overview</h2>

		<table>
			<tr>
				<th><label for="dueDate">Due Date</label></th>
				<td colspan="2"><input id="dueDate" name="dueDate" type="text" value="<?php echo ($data["dueDate"] instanceof DateTime) ? $data["dueDate"]->format(Format::DATE_FORMAT) : $data["dueDate"]; ?>" placeholder="MM/DD/YYYY"></td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td colspan="2"><textarea id="description" name="description"><?php echo $data["description"]; ?></textarea></td>
			</tr>
			<tr>
				<th><label for="milestoneStatus">Status</label></th>
				<td colspan="2">
					<select name="milestoneStatus" id="milestoneStatus">
						<?php foreach (Milestone::statusValues() as $statusInt => $statusText) { ?>
							<option value="<?php echo $statusInt; ?>" <?php echo ($data["status"] == $statusInt) ? "selected" : ""; ?>><?php echo $statusText; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="teamLead">Team Lead</label></th>
				<td colspan="2">
					<select name="teamLead" id="teamLead">
						<option value="0" <?php echo ($data["teamLeadID"] == 0) ? "selected" : ""; ?>>Unassigned</option>
						<option value="0" disabled>Current Team Leads</option>
						<?php foreach (Employee::getCurrent() as $employee) {
							if (strtolower($employee->getPosition()) == "team lead" || strtolower($employee->getPosition()) == "project manager") { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($employee->getPID() == $data["teamLeadID"]) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php }
						} ?>
						<option value="0" disabled>Past Team Leads</option>
						<?php foreach (Employee::getPast() as $employee) {
							if (strtolower($employee->getPosition()) == "team lead" || strtolower($employee->getPosition()) == "project manager") { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($employee->getPID() == $data["teamLeadID"]) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php }
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><span class="label">Contract</span></th>
				<td style="vertical-align: top;">
					<input type="radio" name="contractoptions" id="contractnone" value="0" <?php echo (!$milestone->getContractFile()) ? "checked" : ""; ?>>
					<label for="contractnone">No contract</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="contractoptions" id="contractfile" value="1" <?php echo ($milestone->getContractFile()) ? "checked" : ""; ?>>
					<?php if ($milestone->getContractFile()) { ?>
						<label for="contractfile">Uploaded PDF</label>
						<p><?php echo $milestone->getContractFile(); ?></p>
						<label for="contractfile">Replace File:</label>
					<?php } else { ?>
						<label for="contractfile">Upload PDF</label>
					<?php } ?>
					<br>
					<input type="file" name="contract" id="contract">
					<span id="contractFileLabel" class="file-input-label"></span>
				</td>
			</tr>
			<tr>
				<th><span class="label">Invoice</span></th>
				<td style="vertical-align: top;">
					<input type="radio" name="invoiceoptions" id="invoicenone" value="0" <?php echo (!$milestone->getInvoiceFile()) ? "checked" : ""; ?>>
					<label for="invoicenone">No invoice</label>
				</td>
				<td style="vertical-align: top;">
					<input type="radio" name="invoiceoptions" id="invoicefile" value="1" <?php echo ($milestone->getInvoiceFile()) ? "checked" : ""; ?>>
					<?php if ($milestone->getInvoiceFile()) { ?>
						<label for="invoicefile">Uploaded PDF</label>
						<p><?php echo $milestone->getInvoiceFile(); ?></p>
						<label for="invoicefile">Replace File:</label>
					<?php } else { ?>
						<label for="invoicefile">Upload PDF</label>
					<?php } ?>
					<br>
					<input type="file" name="invoice" id="invoice">
					<span id="invoiceFileLabel" class="file-input-label"></span>
				</td>
			</tr>
		</table>

	</section>

	<section>
		<h2 class="section-heading">Milestone Financials</h2>

		<table>
			<tr>
				<th><label for="revenue">Revenue</label></th>
				<td><input id="revenue" name="revenue" type="text" value="<?php echo $data["revenue"]; ?>" placeholder="$"></td>
			</tr>
			<tr>
				<th><label for="amountPaid">Amount Paid</label></th>
				<td><input id="amountPaid" name="amountPaid" type="text" value="<?php echo $data["amountPaid"]; ?>" placeholder="$"></td>
			</tr>
			<tr>
				<th><label for="teamLeadPay">Team Lead Total Pay</label></th>
				<td><input id="teamLeadPay" name="teamLeadPay" type="text" value="<?php echo $data["teamLeadPay"]->getTransactionValue(); ?>" placeholder="$"></td>
			</tr>
			<tr>
				<th><label for="teamLeadPayRunning">Team Lead Pay Received</label></th>
				<td><input id="teamLeadPayRunning" name="teamLeadPayRunning" type="text" value="<?php echo $data["teamLeadPay"]->getAmountPaid(); ?>" placeholder="$"></td>
			</tr>
		</table>

	</section>

	<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Milestone</a>
	<input type="hidden" name="milestoneEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<script>
	$(document).ready(function () {
		$('#contractFileLabel').html("No file selected");
		$('#contract').change(function() {
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$('#contractFileLabel').html(filename);
		});
		$('#invoiceFileLabel').html("No file selected");
		$('#invoice').change(function() {
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$('#invoiceFileLabel').html(filename);
		});
	});
</script>

<?php include_once "php/footer.php";
die();