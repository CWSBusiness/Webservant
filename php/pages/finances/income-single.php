<?php

try {
	$income = FinanceRecord::withID($_GET["income"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

$payer = $income->getPayer();
$payee = $income->getPayee();

$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-usd"></i> <a href="./?finances">Finances</a> &gt; Manual Income Record</h2>

	<section>
		<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?finances&income=<?php echo $income->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit</a>
		<?php } ?>
		<h2 class="section-heading">Transaction Details</h2>

		<table>
			<tr>
				<th>Date of Transaction</th>
				<td><?php echo Format::date($income->getTransactionDate()); ?></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><?php echo $income->getDescription(); ?></td>
			</tr>
			<tr>
				<th>Payer</th>
				<td><?php echo $payer; ?></td>
			</tr>
			<tr>
				<th>Payee</th>
				<td><?php echo $payee; ?></td>
			</tr>
			<tr>
				<th>Transaction Amount</th>
				<td><?php echo $income->getTransactionValue(); ?></td>
			</tr>
			<?php if ($income->getComments() != "") { ?>
				<tr>
					<th>Comments</th>
					<td style="width: 80%;"><?php echo $parsedown->text($income->getComments()); ?></td>
				</tr>
			<?php } ?>
		</table>

	</section>

<?php
include_once "php/footer.php";
die();
