<?php

$announcements = Announcement::getAll();
$parsedown     = new Parsedown();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-bullhorn"></i> Announcements</h2>

<section>
	<a href="./?announcements&add" class="top-right-button"><i class="fa fa-plus"></i>Add New</a>
	<h2 class="section-heading">All Announcements</h2>
	<?php if (count($announcements) == 0) { ?>
		<p>No announcements have been created.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Up Time</th>
					<th>Down Time</th>
					<th>Group</th>
					<th>Name</th>
					<th>Contents</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($announcements as $announcement) { ?>
				<tr>
					<td><?php echo Format::date($announcement->getUpTime(), Format::DATETIME_FORMAT) ?></td>
					<td><?php echo Format::date($announcement->getDownTime(), Format::DATETIME_FORMAT) ?></td>
					<td><?php echo $announcement->getGroupTextShort(); ?></td>
					<td><?php echo $announcement; ?></td>
					<td><?php echo $parsedown->text($announcement->getContents()); ?></td>
					<td class="action-column">
						<a href="./?announcements&id=<?php echo $announcement->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
						<a href="./?announcements&id=<?php echo $announcement->getPID(); ?>&delete" title="Delete"><i class="fa fa-trash-o"></i></a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php include_once "php/footer.php";
die();