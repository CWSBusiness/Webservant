<?php

$project = Project::withID($_GET['id']);
$parsedown = new Parsedown();

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

$tasks = $milestone->getTasks();
$payingTasks = array();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; <?php echo $milestone; ?></h2>

<?php if ($milestone->getDescription() != "") { ?>
	<section>
		<?php echo $parsedown->text($milestone->getDescription()); ?>
	</section>
<?php } ?>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Milestone</a>
	<?php } ?>
	<h2 class="section-heading">Overview</h2>

	<table>
		<tr>
			<th>Due Date</th>
			<td><?php echo Format::date($milestone->getDueDate()); ?></td>
		</tr>
		<tr>
			<th>Status</th>
			<td><?php echo $milestone->getStatusText(); ?></td>
		</tr>
		<tr>
			<th>Team Lead</th>
			<td>
				<?php if ($milestone->getTeamLeadID() == 0) { ?>
					Unassigned
				<?php } else { ?>
					<a href="./?employees&id=<?php echo $milestone->getTeamLead()->getPID(); ?>"><?php echo $milestone->getTeamLead(); ?></a>
				<?php } ?>
			</td>
		</tr>
		<?php if (User::current()->isAdmin()) {
			if ($milestone->getContractFile()) { ?>
				<tr>
					<th>Contract</th>
					<td><a href="<?php echo $milestone->getContractFile(); ?>"><i class="fa fa-file-text-o"></i>View</a> <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&contractdownload"><i class="fa fa-download"></i>Download</a></td>
				</tr>
			<?php }
			if ($milestone->getInvoiceFile()) { ?>
				<tr>
					<th>Invoice</th>
					<td><a href="<?php echo $milestone->getInvoiceFile(); ?>"><i class="fa fa-file-text-o"></i>View</a> <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&invoicedownload"><i class="fa fa-download"></i>Download</a></td>
				</tr>
			<?php }
		} ?>
	</table>

</section>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&addtask" class="top-right-button"><i class="fa fa-plus"></i>Add Task</a>
	<?php } ?>
	<h2 class="section-heading">Tasks</h2>

	<?php if (count($tasks) == 0) { ?>
		<p>No tasks to display.</p>
	<?php } else { ?>
		<table style="table-layout: fixed">
			<thead>
			<tr>
				<th style="width: 7rem;">Completed</th>
				<th class="numeric-column" style="width: 5rem;">Bounty</th>
				<th style="width: 12rem;">Assignees</th>
				<th>Task Details</th>
				<th style="width: 9rem;"></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($tasks as $id => $task) {
				if (!$task->getBounty()->isZero() && count($task->getAssignees()) > 0) {
					$payingTasks[$id] = $task;
				} ?>
				<tr>
					<td style="vertical-align: top;"><?php echo ($task->isCompleted()) ? "<i class=\"fa fa-check\"></i>" : ""; ?></td>
					<td class="numeric-column" style="vertical-align: top;"><?php echo $task->getBounty(); ?></td>
					<td style="vertical-align: top;">
						<ol>
						<?php
						$assignees = $task->getAssignees();
						for ($i = 0; $i < $task->getMaxAssignees(); $i++) { ?>
							<li><?php echo (isset($assignees[$i])) ? $assignees[$i] : "" ?></li>
						<?php } ?>
						</ol>
					<td style="vertical-align: top;">
						<a href="#" class="task-toggle" id="<?php echo $id; ?>"><i class="fa fa-caret-right"></i><?php echo $task; ?></a>
						<div id="task_<?php echo $id; ?>_contents" style="display: none; margin-left: 2rem;"><?php echo $parsedown->text($task->getDescription()); ?></div>
					</td>
					<td class="action-column" style="vertical-align: top;">
						<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edittask=<?php echo $id; ?>" title="Edit Task"><i class="fa fa-pencil"></i></a>
						<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&assigntask=<?php echo $id; ?>" title="Edit Assignees"><i class="fa fa-male"></i></a>
						<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&toggletask=<?php echo $id; ?>" title="<?php echo ($task->isCompleted()) ? "Mark as not completed" : "Mark as completed"; ?>"><i class="fa <?php echo ($task->isCompleted()) ? "fa-times-circle" : "fa-check-circle"; ?>"></i></a>
						<?php if ($task->getBounty()->isZero()) { ?>
							<i class="fa fa-usd disabled" title="Task has no bounty"></i>
						<?php } else { ?>
						<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&togglepaid=<?php echo $id; ?>" title="<?php echo ($task->getPaidStatus()) ? "Mark as not paid" : "Mark as paid"; ?>"><?php echo ($task->getPaidStatus()) ? '<i class="fa fa-usd"></i>' : '<i class="fa fa-usd" style="opacity: 0.5;"></i>' ?></a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php if (User::current()->isAdmin() || User::current()->getPID() == $milestone->getTeamLeadID()) { ?>
<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Financials</a>
	<?php } ?>
	<h2 class="section-heading">Milestone Financials</h2>
	<table class="data-table">
		<thead>
			<tr>
				<th>Description</th>
				<th class="numeric-column">Total</th>
				<th class="numeric-column">Paid</th>
				<th class="numeric-column">Outstanding</th>
			</tr>
		</thead>
		<tbody>
		<?php // start totalling the milestone financials
		$profitTotal = new Money("0");
		$paidTotal = new Money("0");
		$outstandingTotal = new Money("0");

		// include revenue
		$revenueTotal = $milestone->getRevenue();
		$revenuePaid = $milestone->getAmountPaid();
		$revenueOutstanding = $milestone->getAmountOutstanding();
		$profitTotal->add($revenueTotal);
		$paidTotal->add($revenuePaid);
		$outstandingTotal->add($revenueOutstanding); ?>
			<tr>
				<td>Milestone Income</td>
				<td class="numeric-column"><?php echo $milestone->getRevenue(); ?></td>
				<td class="numeric-column"><?php echo $milestone->getAmountPaid(); ?></td>
				<td class="numeric-column"><?php echo ($revenueOutstanding->isZero()) ? "" : $milestone->getAmountOutstanding(); ?></td>
			</tr>
		<?php
		// include team lead pay
		$teamLeadPay = $milestone->getTeamLeadPay();
		$teamLeadPayTotal = $teamLeadPay->getTransactionValue();

			$teamLeadPayGiven = $teamLeadPay->getAmountPaid();
			$teamLeadPayOutstanding = $teamLeadPay->getAmountOutstanding();
			$profitTotal->subtract($teamLeadPayTotal);
			$paidTotal->subtract($teamLeadPayGiven);
			$outstandingTotal->subtract($teamLeadPayOutstanding); ?>
			<tr>
				<td>Team Lead Pay</td>
				<td class="numeric-column"><span class="money-subtract"><?php echo $teamLeadPayTotal; ?></span></td>
				<td class="numeric-column"><span class="money-subtract"><?php echo $teamLeadPayGiven; ?></span></td>
				<td class="numeric-column"><?php echo ($teamLeadPayOutstanding->isZero()) ? "" : '<span class="money-subtract">' . $teamLeadPayOutstanding . '</span>'; ?></td>
			</tr>
		<?php
		// include pay for tasks with bounties
		foreach ($payingTasks as $id => $task) {
			/** @var Task $task */
			$bountyTotal = $task->getBounty();
			if ($task->getPaidStatus()) {
				$bountyPaid = $task->getBounty();
				$bountyOutstanding = new Money("0");
			} else {
				$bountyPaid = new Money("0");
				$bountyOutstanding = $task->getBounty();
			}
			$profitTotal->subtract($bountyTotal);
			$paidTotal->subtract($bountyPaid);
			$outstandingTotal->subtract($bountyOutstanding);
			$payees = count($task->getAssignees());
			?>
			<tr>
				<td><?php echo $task . " (" . $payees . " " . (($payees == 1) ? "payee" : "payees") . ")"; ?></td>
				<td class="numeric-column"><span class="money-subtract"><?php echo $bountyTotal; ?></span></td>
				<td class="numeric-column"><?php echo ($bountyPaid->isZero()) ? "" : '<span class="money-subtract">' . $bountyPaid . '</span>'; ?></td>
				<td class="numeric-column"><?php echo ($bountyOutstanding->isZero()) ? "" : '<span class="money-subtract">' . $bountyOutstanding . '</span>'; ?></td>
			</tr>
		<?php } ?>
			<tr class="sum-row">
				<td></td>
				<td class="numeric-column"><?php echo ($profitTotal->isNegative()) ? '<span class="money-subtract">' . $profitTotal . '</span>' : $profitTotal; ?></td>
				<td class="numeric-column"><?php echo ($paidTotal->isNegative()) ? '<span class="money-subtract">' . $paidTotal . '</span>' : $paidTotal; ?></td>
				<td class="numeric-column"><?php echo ($outstandingTotal->isNegative()) ? '<span class="money-subtract">' . $outstandingTotal . '</span>' : $outstandingTotal; ?></td>
			</tr>
		</tbody>
	</table>
</section>
<?php } ?>

<script>
	$(function () {
		$(".task-toggle").on("click", function () {
			var id = $(this).attr("id"),
				icon = $(this).find("i");
			$("#task_" + id + "_contents").slideToggle();
			if (icon.hasClass("fa-caret-right")) {
				icon.removeClass("fa-caret-right");
				icon.addClass("fa-caret-down");
			} else {
				icon.removeClass("fa-caret-down");
				icon.addClass("fa-caret-right");
			}
			return false;
		});
	});
</script>

<?php include_once "php/footer.php";
die();