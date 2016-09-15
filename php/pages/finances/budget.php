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
$incomeHistory = FinanceRecord::getIncomesInRange($rangeStart, $rangeEnd);
$payHistory = FinanceRecord::getEmployeePaymentsInRange($rangeStart, $rangeEnd);

/** @var FinanceRecord[] $allEntries */
$allEntries = array_merge($expenseHistory, $incomeHistory, $payHistory);
usort($allEntries, "FinanceRecord::sortByTransactionDate");

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Budget Report</h2>

<section class="print-hide">
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
	<h2 class="section-heading">All Transactions from <?php echo Format::date($rangeStart); ?> to <?php echo ($rangeEnd) ? Format::date($rangeEnd) : "now"; ?></h2>

	<?php if (count($allEntries) == 0) { ?>
		<p>No transactions to display.</p>
	<?php } else { ?>
		<div class="table-overflow-container">
		<table class="data-table">
			<thead>
			<tr>
				<th>Date</th>
				<th>Type</th>
				<th class="numeric-column">Value</th>
				<th class="numeric-column">Paid</th>
				<th class="numeric-column">Outstanding</th>
				<th>Payer</th>
				<th>Payee</th>
				<th>Description</th>
			</tr>
			</thead>
			<tbody>
			<?php

			$profitTotal = new Money("0");
			$paidTotal = new Money("0");
			$outstandingTotal = new Money("0");
			foreach ($allEntries as $transaction) {
				$type                   = $transaction->getTransactionType();
				$payer                  = $transaction->getPayer();
				$payee                  = $transaction->getPayee();
				$transactionTotal       = $transaction->getTransactionValue();
				$transactionPaid        = $transaction->getAmountPaid();
				$transactionOutstanding = $transaction->getAmountOutstanding();
				if ($type == FinanceRecord::EXPENSE) {
					$subtraction    = true;
					$payerText      = '<a href="./?finances&employee=' . $payer->getPID() . '">' . $payer . '</a>';
					$payeeText      = $payee;
				} else if ($type == FinanceRecord::EMPLOYEE_PAYMENT) {
					$subtraction    = true;
					$payerText      = '<a href="./?clients&company=0">' . $payer . '</a>';
					$payeeText      = '<a href="./?finances&employee=' . $payee->getPID() . '">' .  $payee . '</a>';
				} else if ($type == FinanceRecord::INCOMING_PAYMENT) {
					if ($payer == COMPANY_NAME) {
						$payer      = 0;
					}
					$subtraction    = false;
					if ($transaction->getPID()) {
						$payerText  = $payer;
					} else {
						$payerText  = '<a href="./?clients&company=' . $payer . '">' . Client::getCompanyNameByID($payer) . '</a>';
					}
					$payeeText      = '<a href="./?clients&company=0">' . $payee . '</a>';
				}

				if ($subtraction) {
					$profitTotal->subtract($transactionTotal);
					$paidTotal->subtract($transactionPaid);
					$outstandingTotal->subtract($transactionOutstanding);
				} else {
					$profitTotal->add($transactionTotal);
					$paidTotal->add($transactionPaid);
					$outstandingTotal->add($transactionOutstanding);
				}

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td><?php echo $transaction->getTransactionTypeText(); ?></td>
					<td class="numeric-column">
						<?php if (!$transactionTotal->isZero()) {
							echo ($subtraction) ? '<span class="money-subtract">' . $transactionTotal . '</span>' : $transactionTotal;
						} ?>
					</td>
					<td class="numeric-column">
						<?php if (!$transactionPaid->isZero()) {
							echo ($subtraction) ? '<span class="money-subtract">' . $transactionPaid . '</span>' : $transactionPaid;
						} ?>
					</td>
					<td class="numeric-column">
						<?php if (!$transactionOutstanding->isZero()) {
							echo ($subtraction) ? '<span class="money-subtract">' . $transactionOutstanding . '</span>' : $transactionOutstanding;
						} ?>
					</td>
					<td><?php echo $payerText; ?></td>
					<td><?php echo $payeeText; ?></td>
					<td>
						<?php if ($type == FinanceRecord::INCOMING_PAYMENT || $type == FinanceRecord::EMPLOYEE_PAYMENT) { ?>
						<a href="./?projects&id=<?php echo $transaction->getProjectID(); ?>&milestone=<?php echo $transaction->getProjectMilestone(); ?>"><?php echo Format::truncate($transaction->getDescription(), 40); ?></a>
						<?php } else { ?>
						<a href="./?finances&expense=<?php echo $transaction->getPID(); ?>"><?php echo Format::truncate($transaction->getDescription(), 40); ?></a>
						<?php } ?>
					</td>
				</tr>

			<?php } ?>
			<tr class="sum-row">
				<td></td>
				<td></td>
				<td class="numeric-column"><?php echo ($profitTotal->isNegative()) ? '<span class="money-subtract">' . $profitTotal . '</span>' : $profitTotal; ?></td>
				<td class="numeric-column"><?php echo ($paidTotal->isNegative()) ? '<span class="money-subtract">' . $paidTotal . '</span>' : $paidTotal; ?></td>
				<td class="numeric-column"><?php echo ($outstandingTotal->isNegative()) ? '<span class="money-subtract">' . $outstandingTotal . '</span>' : $outstandingTotal; ?></td>
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