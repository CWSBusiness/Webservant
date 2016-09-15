<?php

$docs = Doc::getAllWithoutContents();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-book"></i> Docs</h2>

<section>
	<a href="./?docs&add" class="top-right-button"><i class="fa fa-plus"></i>Create</a>
	<h2 class="section-heading">All Documents</h2>
	<?php if (count($docs) == 0) { ?>
		<p>No documents to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>Title</th>
					<th>Last Edited</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($docs as $doc) { ?>
				<tr>
					<td><a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a></td>
					<td><span title="<?php echo Format::date($doc->getModificationTime(), Format::DATETIME_FORMAT) ?>"><?php echo Format::relativeTime($doc->getModificationTime()); ?></span>, by <a href="./?employees&id=<?php echo $doc->getLastEditorID(); ?>"><?php echo $doc->getLastEditor(); ?></a></td>
					<td class="action-column">
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&edit" title="Edit Doc"><i class="fa fa-pencil"></i></a>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revisions" title="Revision History"><i class="fa fa-history"></i></a>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php include_once "php/footer.php";
die();