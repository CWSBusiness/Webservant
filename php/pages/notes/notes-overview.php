<?php include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-pencil-square-o"></i> Notes</h2>

<section>
	<a href="./?notes&add" class="top-right-button"><i class="fa fa-plus"></i>New Note</a>
	<h2 class="section-heading">My Notes</h2>
	
		<?php $my_notes = Note::getUserNotes(User::current());

		if (count($my_notes) == 0) {
			echo '<p>You have no notes.</p>';
		} else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<th>Title</th>
						<th>Last Edited</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($my_notes as $note) { ?>
					<tr>
						<td><a href="./?notes&id=<?php echo $note->getPID(); ?>&edit"><?php echo $note->getTitle(); ?></a></td>
						<td title="<?php echo Format::date($note->getModificationTime(), Format::DATETIME_FORMAT); ?>">
							<?php echo Format::relativeTime($note->getModificationTime()); ?>
						</td>
						<td class="action-column">
							<a href="./?notes&id=<?php echo $note->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
							<a href="./?notes&id=<?php echo $note->getPID(); ?>&delete" title="Delete"><i class="fa fa-trash-o"></i></a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
</section>

<?php include_once "php/footer.php";