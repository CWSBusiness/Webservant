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

$incomeHistory = FinanceRecord::getIncomesInRange($rangeStart, $rangeEnd);

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Income History</h2>

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
	<h2 class="section-heading">Incomes from <?php echo Format::date($rangeStart); ?> to <?php echo ($rangeEnd) ? Format::date($rangeEnd) : "now"; ?></h2>

	<?php if (count($incomeHistory) == 0) { ?>
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
				<th>Payer</th>
				<th>Payment For</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php

			$incomeTotal = new Money("0");
			$paidTotal = new Money("0");
			$outstandingTotal = new Money("0");
			foreach ($incomeHistory as $transaction) {

				$transactionTotal       = $transaction->getTransactionValue();
				$transactionPaid        = $transaction->getAmountPaid();
				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payer                  = $transaction->getPayer();
				$projectID              = $transaction->getProjectID();

				$incomeTotal->add($transactionTotal);
				$paidTotal->add($transactionPaid);
				$outstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionTotal; ?></td>
					<td class="numeric-column"><?php echo ($transactionPaid->isZero()) ? "" : $transactionPaid; ?></td>
					<td class="numeric-column"><?php echo ($transactionOutstanding->isZero()) ? "" : $transactionOutstanding; ?></td>
				<?php if ($transaction->getPID()) { ?>
					<td><?php echo $payer; ?></td>
					<td><a href="./?finances&income=<?php echo $transaction->getPID(); ?>"><?php echo $transaction->getDescription(); ?></a></td>
					<?php if (User::current()->isSuperAdmin()) { ?>
					<td class="action-column"><a href="./?finances&income=<?php echo $transaction->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a></td>
					<?php } ?>
				<?php } else { ?>
					<td><a href="./?clients&company=<?php echo $payer; ?>"><?php echo Client::getCompanyNameByID($payer); ?></a></td>
					<td><a href="./?projects&id=<?php echo $projectID; ?>&milestone=<?php echo $transaction->getProjectMilestone(); ?>"><?php echo $transaction->getDescription(); ?></a></td>
					<td></td>
				<?php } ?>
				</tr>
			<?php } ?>
			<tr class="sum-row">
				<td></td>
				<td class="numeric-column"><?php echo $incomeTotal; ?></td>
				<td class="numeric-column"><?php echo $paidTotal; ?></td>
				<td class="numeric-column"><?php echo $outstandingTotal; ?></td>
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