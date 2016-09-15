<?php

$projects = Project::getAll();

include_once "php/header.php" ?>

<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?projects&add" class="top-right-button"><i class="fa fa-plus"></i>New Project</a>
<?php } ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; All</h2>

<section>
	<a href="./?projects" class="top-right-button"><i class="fa fa-clock-o"></i>Current Projects</a>
	<h2 class="section-heading">All Projects</h2>
	<?php if (count($projects) == 0) { ?>
		<p>No projects to display.</p>
		<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Project Name</th>
					<th>Client</th>
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