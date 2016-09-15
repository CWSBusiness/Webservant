<?php

$client = Client::withID($_GET['id']);
$projects = $client->getProjects();
$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <?php echo $client; ?></h2>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?clients&id=<?php echo $client->getPID() ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Client</a>
	<?php } ?>
	<h2 class="section-heading">Contact Info</h2>
	<table>
		<tr>
			<th>Organization</th>
			<td><a href="./?clients&company=<?php echo $client->getCompanyID(); ?>"><?php echo $client->getCompanyName(); ?></a></td>
		</tr>
		<tr>
			<th>Position</th>
			<td><?php echo $client->getPosition(); ?></td>
		</tr>
		<tr>
			<th>Contact Status</th>
			<td><?php echo ($client->isCurrentContact()) ? "Current Client" : "Past Client"; ?></td>
		</tr>
		<?php if ($client->getEmail() != "") { ?>
			<tr>
				<th>Email</th>
				<td><a href="mailto:<?php echo $client->getEmail(); ?>"><i class="fa fa-envelope"></i><?php echo $client->getEmail(); ?></a></td>
			</tr>
		<?php }
		if ($client->getPhone() != "") { ?>
			<tr>
				<th>Phone Number</th>
				<td><a href="tel:<?php echo Format::tel($client->getPhone()); ?>"><i class="fa fa-phone"></i><?php echo Format::phone($client->getPhone()); ?></a></td>
			</tr>
		<?php } ?>
	</table>
</section>

<section>
	<h2 class="section-heading">Projects with <?php echo $client->getFirstName(); ?></h2>
	<?php if (count($projects) == 0) { ?>
		<p>No projects to display.</p>
	<?php } else { ?>
		<table>
			<tr>
				<th>Project</th>
				<th>Status</th>
			</tr>
			<?php foreach ($projects as $project) { ?>
				<tr>
					<td><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a></td>
					<td><?php echo ($project->isActive()) ? "Active" : "Inactive"; ?></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
</section>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?clients&id=<?php echo $client->getPID(); ?>&notes" class="top-right-button"><i class="fa fa-edit"></i>Edit Notes</a>
	<?php } ?>
	<h2 class="section-heading">Notes</h2>
	<?php if ($client->getNotes() == "") { ?>
		<p>No notes to display.</p>
	<?php } else {
		echo $parsedown->text($client->getNotes());
	} ?>
</section>

<?php include_once "php/footer.php";
die();