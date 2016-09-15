<?php
$project = Project::withID($_GET['id']);
include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; Milestones</h2>

	<section>
		<?php if (User::current()->isSuperAdmin()) { ?>
			<a href="./?projects&id=<?php echo $project->getPID(); ?>&addmilestone" class="top-right-button"><i class="fa fa-plus"></i>Add Milestone</a>
		<?php } ?>
		<h2 class="section-heading">Milestone History</h2>
		<?php if (count($project->getMilestones()) == 0) { ?>
			<p>No milestones have been added to this project.</p>
		<?php } else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<th>Due Date</th>
						<th>Milestone Name</th>
						<th>Status</th>
						<th>Completed Tasks</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($project->getMilestones() as $milestone) {
					$tasks = $milestone->getTasks();
					$numTasks = count($tasks);
					$completed = 0;
					foreach ($tasks as $task) {
						if ($task->isCompleted()) {
							$completed++;
						}
					} ?>
					<tr>
						<td><?php echo Format::date($milestone->getDueDate()) ?></td>
						<td><a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>"><?php echo $milestone; ?></a></td>
						<td><?php echo $milestone->getStatusText(); ?></td>
						<td><?php echo $completed; ?>/<?php echo $numTasks; ?> (<?php echo ($numTasks == 0) ? "100" : floor(($completed/$numTasks) * 100) ?>%)</td>
						<td class="action-column">
						<?php if (User::current()->isSuperAdmin()) { ?>
							<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestone=<?php echo $milestone->getNameForParameter(); ?>&edit" title="Edit Milestone"><i class="fa fa-pencil"></i></a>
						<?php } ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</section>

<?php include_once "php/footer.php";
die();