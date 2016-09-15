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

$employee = Employee::withID($_GET["employee"]);

if (User::current()->isAdmin() || (User::current()->isEmployee() && Employee::current()->getPID() == $employee->getPID())) {
} else {
	Auth::redirect("./?finances");
}

$employeeExpenseHistory = FinanceRecord::getExpensesByEmployeeInRange($employee->getPID(), $rangeStart, $rangeEnd);
$employeePayHistory = FinanceRecord::getPaymentsForEmployeeInRange($employee->getPID(), $rangeStart, $rangeEnd);

/** @var FinanceRecord[] $employeeHistory */
$employeeHistory = array_merge($employeeExpenseHistory, $employeePayHistory);
usort($employeeHistory, "FinanceRecord::sortByTransactionDate");

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; <?php echo (User::current()->isEmployee() && Employee::current()->getPID() == $employee->getPID()) ? "Personal" : $employee; ?></h2>

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
		<h2 class="section-heading">Finances from <?php echo Format::date($rangeStart); ?> to <?php echo ($rangeEnd) ? Format::date($rangeEnd) : "now"; ?></h2>

		<?php if (count($employeeHistory) == 0) { ?>
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

				$balanceTotal     = new Money("0");
				$paidTotal        = new Money("0");
				$outstandingTotal = new Money("0");
				foreach ($employeeHistory as $transaction) {
					$type                   = $transaction->getTransactionType();
					$payer                  = $transaction->getPayer();
					$payee                  = $transaction->getPayee();
					$transactionTotal       = $transaction->getTransactionValue();
					$transactionPaid        = $transaction->getAmountPaid();
					$transactionOutstanding = $transaction->getAmountOutstanding();

					$balanceTotal->add($transactionTotal);
					$paidTotal->add($transactionPaid);
					$outstandingTotal->add($transactionOutstanding);

					?>
					<tr>
						<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
						<td>
							<?php echo $transaction->getTransactionTypeText(); ?>
						</td>
						<td class="numeric-column"><?php echo $transactionTotal; ?></td>
						<td class="numeric-column"><?php echo ($transactionPaid->isZero()) ? "" : $transactionPaid; ?></td>
						<td class="numeric-column"><?php echo ($transactionOutstanding->isZero()) ? "" : $transactionOutstanding; ?></td>
						<td>
							<?php echo $payer; ?>
						</td>
						<td>
							<?php echo $payee; ?>
						</td>
						<td>
							<?php if ($type == FinanceRecord::EMPLOYEE_PAYMENT) {
								if ($transaction->getPID()) { ?>
								<a href="./?finances&pay=<?php echo $transaction->getPID(); ?>"><?php echo $transaction->getDescription(); ?></a>
								<?php } else { ?>
								<a href="./?projects&id=<?php echo $transaction->getProjectID(); ?>&milestone=<?php echo $transaction->getProjectMilestone(); ?>"><?php echo $transaction->getDescription(); ?></a>
								<?php } ?>
							<?php } else if ($type == FinanceRecord::EXPENSE) { ?>
								<a href="./?finances&expense=<?php echo $transaction->getPID(); ?>"><?php echo $transaction->getDescription(); ?></a>
							<?php } ?>
						</td>
					</tr>

				<?php } ?>
				<tr class="sum-row">
					<td></td>
					<td></td>
					<td class="numeric-column"><?php echo ($balanceTotal->isNegative()) ? '<span class="money-subtract">' . $balanceTotal . '</span>' : $balanceTotal; ?></td>
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