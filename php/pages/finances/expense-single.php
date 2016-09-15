<?php

try {
	$expense = FinanceRecord::withID($_GET["expense"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$payer = $expense->getPayer();
$payee = $expense->getPayee();

$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Expense</h2>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?finances&expense=<?php echo $expense->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit</a>
	<?php } ?>
	<h2 class="section-heading">Transaction Details</h2>

	<table>
		<tr>
			<th>Date of Transaction</th>
			<td><?php echo Format::date($expense->getTransactionDate()); ?></td>
		</tr>
		<?php if ($expense->getProjectID() > 0) { ?>
		<tr>
			<th>Project</th>
			<td><?php echo Project::withID($expense->getProjectID()); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<th>Description</th>
			<td><?php echo $expense->getDescription(); ?></td>
		</tr>
		<tr>
			<th>Payer</th>
			<td><?php if ($payer instanceof Employee) { ?>
					<a href="./?employees&id=<?php echo $payer->getPID(); ?>"><?php echo $payer; ?></a>
				<?php } else {
					echo $payer;
				} ?>
			</td>
		</tr>
		<tr>
			<th>Payee</th>
			<td><?php if ($payee instanceof Employee) { ?>
					<a href="./?employees&id=<?php echo $payee->getPID(); ?>"><?php echo $payee; ?></a>
				<?php } else {
					echo $payee;
				} ?>
			</td>
		</tr>
		<tr>
			<th>Transaction Amount</th>
			<td><?php echo $expense->getTransactionValue(); ?></td>
		</tr>
		<tr>
			<th>Reimbursed?</th>
			<td><?php echo ($expense->payerNeedsReimbursement()) ? "<i class=\"fa fa-times\"></i>Not yet" : "<i class=\"fa fa-check\"></i>Paid back" ; ?></td>
		</tr>
		<?php if ($expense->getExpenseReceipt()) { ?>
			<tr>
				<th>Receipt</th>
				<td><a href="<?php echo $expense->getExpenseReceipt(); ?>"><i class="fa fa-file-text-o"></i>View</a> <a href="./?finances&expense=<?php echo $expense->getPID(); ?>&receiptdownload"><i class="fa fa-download"></i>Download</a></td>
			</tr>
		<?php } ?>
		<?php if ($expense->getComments() != "") { ?>
		<tr>
			<th>Comments</th>
			<td style="width: 80%;"><?php echo $parsedown->text($expense->getComments()); ?></td>
		</tr>
		<?php } ?>
	</table>

</section>

<?php include_once "php/footer.php";
die();
