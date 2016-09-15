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

$expenseHistory = FinanceRecord::getExpensesInRange($rangeStart, $rangeEnd);

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Expense History</h2>

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
	<h2 class="section-heading">Expenses from <?php echo Format::date($rangeStart); ?> to <?php echo ($rangeEnd) ? Format::date($rangeEnd) : "now"; ?></h2>

	<?php if (count($expenseHistory) == 0) { ?>
		<p>No transactions to display.</p>
	<?php } else { ?>
		<div class="table-overflow-container">
		<table class="data-table" style="width: auto">
			<thead>
			<tr>
				<th>Date</th>
				<th class="numeric-column">Amount</th>
				<th class="numeric-column">Reimbursed</th>
				<th class="numeric-column">Outstanding</th>
				<th>Description</th>
				<th>Payer</th>
				<th>Payee</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php

			$expensesTotal    = new Money("0");
			$paidTotal        = new Money("0");
			$outstandingTotal = new Money("0");

			foreach ($expenseHistory as $transaction) {
				$transactionTotal       = $transaction->getTransactionValue();
				$transactionPaid        = $transaction->getAmountPaid();
				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payer                  = $transaction->getPayer();
				$payee                  = $transaction->getPayee();

				$expensesTotal->add($transactionTotal);
				$paidTotal->add($transactionPaid);
				$outstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionTotal; ?></td>
					<td class="numeric-column"><?php echo ($transactionPaid->isZero()) ? "" : $transactionPaid; ?></td>
					<td class="numeric-column"><?php echo ($transactionOutstanding->isZero()) ? "" : $transactionOutstanding; ?></td>
					<td><a href="./?finances&expense=<?php echo $transaction->getPID(); ?>"><?php echo Format::truncate($transaction->getDescription(), 30); ?></a></td>
					<td><a href="./?finances&employee=<?php echo $payer->getPID(); ?>"><?php echo $payer; ?></a></td>
					<td><?php echo $payee; ?></td>
					<td class="action-column">
						<?php if (User::current()->isSuperAdmin()) { ?>
						<a href="./?finances&expense=<?php echo $transaction->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			<tr class="sum-row">
				<td></td>
				<td class="numeric-column"><?php echo ($expensesTotal->isNegative()) ? '<span class="money-subtract">' . $expensesTotal . '</span>' : $expensesTotal; ?></td>
				<td class="numeric-column"><?php echo ($paidTotal->isNegative()) ? '<span class="money-subtract">' . $paidTotal . '</span>' : $paidTotal; ?></td>
				<td class="numeric-column"><?php echo ($outstandingTotal->isNegative()) ? '<span class="money-subtract">' . $outstandingTotal . '</span>' : $outstandingTotal; ?></td>
				<td></td>
				<td></td>
				<td></td>
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