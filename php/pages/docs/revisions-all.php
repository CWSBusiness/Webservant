<?php

$doc = Doc::withID($_GET["id"]);
$revisions = $doc->getRevisionHistory();
//unset($revisions[0]);

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; Revisions</h2>

<section>
	<h2 class="section-heading">Last 5 Revisions</h2>
	<table class="data-table">
		<thead>
			<tr>
				<th>Title</th>
				<th>Edit Details</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($revisions as $index => $revision) {
			$editor = $revision->getLastEditor(); ?>
			<tr>
				<td>
				<?php if ($index == 0) { ?>
					<a href="./?docs&id=<?php echo $doc->getPID(); ?>" title="View Revision"><?php echo $revision->getTitle(); ?></a>
					<?php echo ($index == 0) ? " (current version)" : "";
				} else { ?>
					<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $index; ?>"><?php echo $revision->getTitle(); ?></a>
				<?php } ?>
		</td>
				<td><span title="<?php echo Format::date($revision->getModificationTime(), Format::DATETIME_FORMAT); ?>"><?php echo Format::relativeTime($revision->getModificationTime()); ?></span>, by <a href="./?employees&id=<?php echo $editor->getPID(); ?>"><?php echo $editor; ?></a></td>
				<td class="action-column">
					<?php if ($index == 0) { ?>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>" title="View This Doc"><i class="fa fa-file-text-o"></i></a>
						<i class="fa fa-files-o disabled" title="This is the current version"></i>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&edit" title="Edit This Doc"><i class="fa fa-pencil"></i></a>
					<?php } else { ?>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $index; ?>" title="View Revision Content"><i class="fa fa-file-text-o"></i></a>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $index; ?>&diff" title="View Revision Diff"><i class="fa fa-files-o"></i></a>
						<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $index; ?>&source" title="View Revision Source"><i class="fa fa-file-code-o"></i></a>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</section>

<?php include_once "php/footer.php";
die();