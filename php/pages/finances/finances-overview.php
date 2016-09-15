<?php

include_once "php/header.php"; ?>

<?php // budget reporting functionality is for administrators only
if (User::current()->isAdmin()) { ?>
<a href="./?finances&budget" class="top-right-button"><i class="fa fa-bank"></i>Generate Budget Report</a>
<?php } ?>
<h2 class="breadcrumbs"><i class="fa fa-usd"></i> Finances</h2>

<?php if (User::current()->isEmployee()) {
	$employeeExpenses = FinanceRecord::getOutstandingExpensesByEmployee(Employee::current()->getPID());
	$employeePays = FinanceRecord::getOutstandingPaymentsForEmployee(Employee::current()->getPID());
	/** @var FinanceRecord[] $employeeFinances */
	$employeeFinances = array_merge($employeeExpenses, $employeePays);
	usort($employeeFinances, "FinanceRecord::sortByTransactionDate");
	?>
<section>
	<a href="./?finances&employee=<?php echo Employee::current()->getPID(); ?>" class="top-right-button"><i class="fa fa-history"></i>History</a>
	<h2 class="section-heading">Personal Finances</h2>
	<?php if (count($employeeFinances) == 0) { ?>
		<p>No outstanding finances to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Date</th>
					<th class="numeric-column">Outstanding</th>
					<th>Type</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
			<?php

			$personalOutstandingTotal = new Money("0");
			foreach ($employeeFinances as $transaction) {

				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payee                  = $transaction->getPayee();

				$personalOutstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionOutstanding; ?></td>
					<td><?php echo $transaction->getTransactionTypeText(); ?></td>
					<td><?php echo Format::truncate($transaction->getDescription(), 30); ?></td>
				</tr>
			<?php } ?>
				<tr class="sum-row">
					<td></td>
					<td class="numeric-column"><?php echo $personalOutstandingTotal; ?></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
</section>
<?php } ?>

<?php // financial summaries are for administrators only
if (User::current()->isAdmin()) {
	$expenses = FinanceRecord::getOutstandingExpenses();
	$incomes = FinanceRecord::getOutstandingIncomes();
	$pays = FinanceRecord::getOutstandingEmployeePayments(); ?>
<section>
	<a href="./?finances&expensehistory" class="top-right-button"><i class="fa fa-history"></i>History</a>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?finances&expenseadd" class="top-right-button"><i class="fa fa-plus"></i>Add</a>
	<?php } ?>
	<h2 class="section-heading">Expenses Awaiting Reimbursement</h2>
	<?php if (count($expenses) == 0) { ?>
		<p>No expenses to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Date</th>
					<th class="numeric-column">Outstanding</th>
					<th>Description</th>
					<th>Payer</th>
					<th>Payee</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php

			$expenseOutstandingTotal = new Money("0");
			foreach ($expenses as $transaction) {

				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payer                  = $transaction->getPayer();
				$payee                  = $transaction->getPayee();

				$expenseOutstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionOutstanding; ?></td>
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
					<td class="numeric-column"><?php echo $expenseOutstandingTotal; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
</section>

<section>
	<a href="./?finances&incomehistory" class="top-right-button"><i class="fa fa-history"></i>History</a>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?finances&incomeadd" class="top-right-button"><i class="fa fa-plus"></i>Add</a>
	<?php } ?>
	<h2 class="section-heading">Outstanding Incomes</h2>
	<?php if (count($incomes) == 0) { ?>
		<p>No income is currently being awaited.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Date</th>
					<th class="numeric-column">Outstanding</th>
					<th>Payer</th>
					<th>Payment For</th>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php $incomeSum = new Money("0");

			$incomeOutstandingTotal = new Money("0");
			foreach ($incomes as $transaction) {

				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payer                  = $transaction->getPayer();
				$projectID              = $transaction->getProjectID();

				$incomeOutstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionOutstanding; ?></td>
				<?php if ($transaction->getPID()) { ?>
					<td><?php echo $payer; ?></td>
					<td><a href="./?finances&income=<?php echo $transaction->getPID(); ?>"><?php echo Format::truncate($transaction->getDescription(), 30); ?></a></td>
					<td class="action-column">
						<?php if (User::current()->isSuperAdmin()) { ?>
						<a href="./?finances&income=<?php echo $transaction->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
						<?php } ?>
					</td>
				<?php } else { ?>
					<td><a href="./?clients&company=<?php echo $payer; ?>"><?php echo Client::getCompanyNameByID($payer); ?></a></td>
					<td><a href="./?projects&id=<?php echo $projectID; ?>&milestone=<?php echo $transaction->getProjectMilestone(); ?>"><?php echo $transaction->getDescription(); ?></a></td>
					<td></td>
				<?php } ?>
				</tr>
			<?php } ?>
				<tr class="sum-row">
					<td></td>
					<td class="numeric-column"><?php echo $incomeOutstandingTotal; ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
</section>

<section>
	<a href="./?finances&payhistory" class="top-right-button"><i class="fa fa-history"></i>History</a>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?finances&payadd" class="top-right-button"><i class="fa fa-plus"></i>Add</a>
	<?php } ?>
	<h2 class="section-heading">Employees Awaiting Pay</h2>
	<?php if (count($pays) == 0) { ?>
		<p>No employees are currently awaiting pay.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Date</th>
					<th class="numeric-column">Outstanding</th>
					<th>Employee</th>
					<th>Payment For</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php

			$paymentsOutstandingTotal = new Money("0");
			foreach ($pays as $transaction) {

				$transactionOutstanding = $transaction->getAmountOutstanding();
				$payee                  = $transaction->getPayee();

				$paymentsOutstandingTotal->add($transactionOutstanding);

				?>
				<tr>
					<td><?php echo Format::date($transaction->getTransactionDate()); ?></td>
					<td class="numeric-column"><?php echo $transactionOutstanding; ?></td>
					<td><a href="./?finances&employee=<?php echo $payee->getPID(); ?>"><?php echo $payee; ?></td>
					<?php if ($transaction->getPID()) { ?>
						<td><a href="./?finances&pay=<?php echo $transaction->getPID(); ?>"><?php echo Format::truncate($transaction->getDescription(), 30); ?></a></td>
						<td class="action-column">
							<?php if (User::current()->isSuperAdmin()) { ?>
							<a href="./?finances&pay=<?php echo $transaction->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
							<?php } ?>
						</td>
					<?php } else { ?>
						<td><?php echo $transaction->getDescription(); ?></td>
						<td></td>
					<?php } ?>
				</tr>
			<?php } ?>
				<tr class="sum-row">
					<td></td>
					<td class="numeric-column"><?php echo $paymentsOutstandingTotal; ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php } ?>

<?php include_once "php/footer.php";
die();