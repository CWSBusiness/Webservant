<?php

try {
	$pay = FinanceRecord::withID($_GET["pay"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$payer = $pay->getPayer();
$payee = $pay->getPayee();

$parsedown = new Parsedown();

include_once "php/header.php"; ?>

	<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Manual Pay Record</h2>

	<section>
		<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?finances&pay=<?php echo $pay->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit</a>
		<?php } ?>
		<h2 class="section-heading">Transaction Details</h2>

		<table>
			<tr>
				<th>Date of Transaction</th>
				<td><?php echo Format::date($pay->getTransactionDate()); ?></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><?php echo $pay->getDescription(); ?></td>
			</tr>
			<tr>
				<th>Payer</th>
				<td><?php echo $payer; ?></td>
			</tr>
			<tr>
				<th>Payee</th>
				<td><a href="./?finances&employee=<?php echo $payee->getPID(); ?>"><?php echo $payee; ?></a></td>
			</tr>
			<tr>
				<th>Transaction Amount</th>
				<td><?php echo $pay->getTransactionValue(); ?></td>
			</tr>
			<tr>
				<th>Amount Paid</th>
				<td><?php echo $pay->getAmountPaid(); ?></td>
			</tr>
			<?php if ($pay->getComments() != "") { ?>
				<tr>
					<th>Comments</th>
					<td style="width: 80%;"><?php echo $parsedown->text($pay->getComments()); ?></td>
				</tr>
			<?php } ?>
		</table>

	</section>

<?php
include_once "php/footer.php";
die();
