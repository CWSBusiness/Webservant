<?php

$clients = Client::getAll();

$currentClients = array();
$pastClients = array();

foreach ($clients as $client) {
	if ($client->isCurrentContact()) {
		$currentClients[] = $client;
	} else {
		$pastClients[] = $client;
	}
}

include_once "php/header.php" ?>

	<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> Clients</h2>

	<section>
		<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?clients&add" class="top-right-button"><i class="fa fa-plus"></i>Add New</a>
		<?php } ?>
		<h2 class="section-heading">Current Clients</h2>
		<?php if (count($currentClients) == 0) { ?>
			<p>No current clients to display.</p>
		<?php } else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Organization</th>
						<th>Position</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($currentClients as $client) { ?>
					<tr>
						<td><a href="./?clients&id=<?php echo $client->getPID(); ?>"><?php echo $client; ?></a></td>
						<td><a href="./?clients&company=<?php echo $client->getCompanyID(); ?>"><?php echo $client->getCompanyName(); ?></a></td>
						<td><?php echo $client->getPosition(); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</section>

	<section>
		<h2 class="section-heading">Past Clients</h2>
		<?php if (count($pastClients) == 0) { ?>
			<p>No past clients to display.</p>
		<?php } else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Organization</th>
						<th>Position</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($pastClients as $client) { ?>
					<tr>
						<td><a href="./?clients&id=<?php echo $client->getPID(); ?>"><?php echo $client; ?></a></td>
						<td><a href="./?clients&company=<?php echo $client->getCompanyID(); ?>"><?php echo $client->getCompanyName(); ?></a></td>
						<td><?php echo $client->getPosition(); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</section>

<?php include_once "php/footer.php";
die();