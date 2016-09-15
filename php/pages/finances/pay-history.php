<?php

$timeZero   = new DateTime();
$timeZero->setTimestamp(0);             // beginning of UNIX timestamps

$oneYearAgo = new DateTime();
$oneYearAgo->modify("-1 year");
$oneYearAgo->setTime(0, 0, 0);          // round to the nearest day

$rangeStart = $oneYearAgo;
$rangeEnd   = NULL;

if (isset($_POST["rangeSubmit"])) {

	if ($_POST["rangeStart"]) {
		$temp = DateTime::createFromFormat(Format::DATE_FORMAT, $_POST["rangeStart"]);
		if ($temp > $timeZero) {
			$rangeStart = clone $temp;
		}
		unset($temp);
	}

	if ($_POST["rangeEnd"]) {
		$temp = DateTime::createFromFormat(Format::DATE_FORMAT, $_POST["rangeEnd"]);
		if ($temp > $timeZero) {
			$rangeEnd = clone $temp;
		}
		unset($temp);
	}

}

$payHistory = FinanceRecord::getEmployeePaymentsInRange($rangeStart, $rangeEnd);

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Pay History</h2>

<section>
	<form action="" method="post">
		<table>
			<tr>
				<td><input name="rangeStart" id="rangeStart" type="text" value="<?php echo ($rangeStart) ? Format::date($rangeStart) : ""; ?>" placeholder="Expense records date back to <?php echo Format::date(FinanceRecord::getOldestExpenseDate()); ?>"></td>
				<td style="text-align: center;">to</td>
				<td><input name="rangeEnd" id="rangeEnd" type="text" value="<?php echo ($rangeEnd) ? Format::date($rangeEnd) : ""; ?>" placeholder="Leave blank to include results up to now"></td>
				<td style="width: 1px;"><button type="submit">Reload</button></td>
			</tr>
		</table>
		<input type="hidden" name="rangeSubmit">
	</form>
</section>

<section>
	<h2 class="section-heading">Employee Payments from <?php echo Format::date($rangeStart); ?> to <?php echo ($rangeEnd) ? Format::date($rangeEnd) : "now"; ?></h2>

	<?php if (count($payHistory) == 0) { ?>
		<p>No transactions to display.</p>
	<?php } else { ?>
		<div class="table-overflow-container">
		<table class="data-table">
			<thead>
			<tr>
				<th>Date</th>
				<th class="numeric-column">Total</th>
				<th class="numeric-column">Paid</th>
				<th class="numeric-column">Outstanding</th>
				<th>Employee</th>
				<th>Payment For</th>
			</tr>
			</thead>
			<tbody>
			<?php $paymentSum = new Money("0");

			$paysTotal        = new Money("0");
			$paidTotal        = new Money("0");
			$outstandingTotal = new Money("0");

			foreach ($payHistory as $transaction) {

				$transactionTotal       = $transaction->getTransactionValue();
				$transactionPaid        = $transaction->getAmountPaid();
				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payee                  = $transaction->getPayee();

				$paysTotal->add($transactionTotal);
				$paidTotal->add($transactionPaid);
				$outstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionTotal; ?></td>
					<td class="numeric-column"><?php echo ($transactionPaid->isZero()) ? "" : $transactionPaid; ?></td>
					<td class="numeric-column"><?php echo ($transactionOutstanding->isZero()) ? "" : $transactionOutstanding; ?></td>
					<td><a href="./?finances&employee=<?php echo $payee->getPID(); ?>"><?php echo $payee; ?></td>
				<?php if ($transaction->getPID()) { ?>
					<td><a href="./?finances&pay=<?php echo $transaction->getPID(); ?>"><?php echo $transaction->getDescription(); ?></a></td>
					<?php if (User::current()->isSuperAdmin()) { ?>
					<td class="action-column"><a href="./?finances&pay=<?php echo $transaction->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a></td>
					<?php } ?>
				<?php } else { ?>
					<td><?php echo $transaction->getDescription(); ?></td>
					<td></td>
				<?php } ?>
				</tr>
			<?php } ?>
				<tr class="sum-row">
					<td></td>
					<td class="numeric-column"><?php echo $paysTotal; ?></td>
					<td class="numeric-column"><?php echo $paidTotal; ?></td>
					<td class="numeric-column"><?php echo $outstandingTotal; ?></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
		</div>
	<?php } ?>

</section>

<?php
include_once "php/footer.php";
die();