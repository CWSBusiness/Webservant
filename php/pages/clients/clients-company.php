<?php

$company = Client::getCompanyNameByID($_GET['company']);
$projects = Project::getProjectsByCompanyID($_GET['company']);
$clients = Client::getClientsByCompanyID($_GET["company"]);

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <?php echo $company; ?></h2>

<?php if ($_GET["company"] > 0) { ?>
<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?clients&company=<?php echo $_GET["company"]; ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Company Name</a>
	<?php } ?>
	<h2 class="section-heading">Contacts</h2>
	<?php if (count($clients) == 0) { ?>
		<p>No contacts to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Position</th>
					<th>Current Contact?</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($clients as $person) { ?>
				<tr>
					<td><a href="./?clients&id=<?php echo $person->getPID(); ?>"><?php echo $person; ?></a></td>
					<td><?php echo $person->getPosition(); ?></td>
					<td><?php echo ($person->isCurrentContact()) ? "<i class=\"fa fa-check\"></i>" : "<i class=\"fa fa-times\"></i>"; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>
<?php } ?>

<section>
	<h2 class="section-heading">Projects</h2>
	<?php if (count($projects) == 0) { ?>
		<p>No projects to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Project</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($projects as $project) { ?>
				<tr>
					<td><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a></td>
					<td><?php echo ($project->isActive()) ? "Active" : "Inactive"; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php include_once "php/footer.php";
die();