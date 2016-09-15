<?php

$projects = Project::getActive();
$cal = new MonthCalendar(new DateTime());

foreach ($projects as $project) {
	foreach ($project->getMilestones() as $milestone) {
		if ($milestone->getStatus() > 0) {
			$text = "<a href=\"./?projects&id=" . $project->getPID() . "\">" . $project->getName() . "</a> – Milestone <a href=\"./?projects&id=" . $project->getPID() . "&milestone=" . $milestone->getNameForParameter() . "\">" . $milestone->getName() . "</a>";
			try {
				$cal->addEvent($milestone->getDueDate(), $text);
			} catch (OutOfBoundsException $e) {}
		}
	}
}

include_once "php/header.php" ?>

<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?projects&add" class="top-right-button"><i class="fa fa-plus"></i>New Project</a>
<?php } ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> Projects</h2>

<section>
	<h2 class="section-heading">This Month – <?php echo date("F Y", time()); ?></h2>
	<?php echo $cal; ?>
</section>

<section>
	<a href="./?projects&viewall" class="top-right-button"><i class="fa fa-bars"></i>All Projects</a>
	<h2 class="section-heading">Current Projects</h2>
	<?php if (count($projects) == 0) { ?>
	<p>There are no current projects to display.</p>
	<?php } else { ?>
	<table class="data-table">
		<thead>
			<tr>
				<th>Project Name</th>
				<th>Client</th>
				<!--<th>Status</th>-->
			</tr>
		</thead>
		<tbody>
		<?php foreach ($projects as $project) { ?>
			<tr>
				<td><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project->getName(); ?></a></td>
				<td><a href="./?clients&company=<?php echo $project->getCompanyID(); ?>"><?php echo $project->getCompanyName(); ?></a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } ?>
</section>

<?php include_once "php/footer.php";
die();