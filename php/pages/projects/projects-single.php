<?php

$parsedown = new Parsedown();

$project = Project::withID($_GET['id']);
$activeMilestones = $project->getActiveMilestones();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <?php echo $project; ?></h2>

<?php if ($project->getDescription() != "") { ?>
<section>
	<?php echo $parsedown->text($project->getDescription()); ?>
</section>
<?php } ?>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?projects&id=<?php echo $project->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Project Info</a>
	<?php } ?>
	<h2 class="section-heading">Project Overview</h2>

	<table>
		<?php if ($project->getURL() != "") { ?>
			<tr>
				<th>Project URL</th>
				<td><a href="<?php echo $project->getURL(); ?>"><?php echo $project->getURL(); ?></a></td>
			</tr>
		<?php } ?>
		<tr>
			<th>Status</th>
			<td>
				<?php if ($project->isActive()) {
					$count = count($activeMilestones);
					echo "Active (" . $count . " current milestone" . (($count == 1) ? "" : "s") . ")";
				} else {
					echo "Inactive";
				} ?>
		</tr>
	</table>

</section>

<section>
	<?php if (User::current()->isSuperAdmin() && $project->getCompanyID() > 0) { ?>
		<a href="./?projects&id=<?php echo $project->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Clients</a>
	<?php } ?>
	<h2 class="section-heading">Client Information</h2>

	<table>
		<tr>
			<th>Organization</th>
			<td><a href="./?clients&company=<?php echo $project->getCompanyID(); ?>"><?php echo $project->getCompanyName() ; ?></a></td>
		</tr>
		<tr>
			<th>Points of Contact</th>
			<td>
				<?php $contacts = $project->getTechnicalContacts();
				if (count($contacts) == 0) { ?>
					<p>No contacts.</p>
				<?php } else { ?>
					<ul>
						<?php foreach ($contacts as $contact) { ?>
							<li><a href="./?clients&id=<?php echo $contact->getPID(); ?>"><?php echo $contact; ?></a></li>
						<?php } ?>
					</ul>
				<?Php } ?>
			</td>
		</tr>
	</table>

</section>

<section>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones" class="top-right-button"><i class="fa fa-diamond"></i>All Milestones</a>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>&addmilestone" class="top-right-button"><i class="fa fa-plus"></i>Add</a>
	<h2 class="section-heading">Active Milestones</h2>
	<?php if (count($activeMilestones) == 0) { ?>
		<p>There are currently no active milestones.</p>
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
			<?php foreach ($activeMilestones as $milestone) {
				$completed = 0;
				$numTasks = count($milestone->getTasks());
				foreach ($milestone->getTasks() as $task) {
					if ($task->isCompleted()) {
						$completed++;
					}
				} ?>
				<tr>
					<td><?php echo(Format::date($milestone->getDueDate())); ?></td>
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

<section>
	<a href="./?projects&id=<?php echo $project->getPID(); ?>&fileadd" class="top-right-button"><i class="fa fa-upload"></i>Upload</a>
	<h2 class="section-heading">Files</h2>
	<?php include "php/pages/projects/files-list.php"; ?>
</section>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?projects&id=<?php echo $project->getPID(); ?>&notes" class="top-right-button"><i class="fa fa-edit"></i>Edit Notes</a>
	<?php } ?>
	<h2 class="section-heading">Project Notes</h2>
	<?php
	echo $parsedown->text($project->getNotes());
	?>
</section>

<?php include_once "php/footer.php";
die();