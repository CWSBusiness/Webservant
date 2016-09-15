<?php

$project = Project::withID($_GET['id']);

$assets = $project->getAssetsDirectoryList();

?>

<?php if (count($assets) == 0) { ?>
	<p>No files have been uploaded.</p>
<?php } else { ?>
<table class="data-table">
	<thead>
		<tr>
			<th>Name</th>
			<th>Size</th>
			<th>Uploaded</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($assets as $file) {
			$modificationTime = DateTime::createFromFormat("U", filemtime($file));
			$fileBasename = basename($file);
			$fileParameterName = urlencode($fileBasename);
			?>
			<tr>
				<td><a href="<?php echo $file; ?>"><?php echo Format::truncate($fileBasename, 70); ?></a></td>
				<td><?php echo Format::bytes(filesize($file)); ?></td>
				<td title="<?php echo Format::date($modificationTime, Format::DATETIME_FORMAT); ?>"><?php echo Format::relativeTime($modificationTime); ?></td>
				<td class="action-column"><a href="./?projects&id=<?php echo $project->getPID(); ?>&file=<?php echo $fileParameterName; ?>&download" title="Download"><i class="fa fa-download"></i></a><a href="./?projects&id=<?php echo $project->getPID(); ?>&file=<?php echo $fileParameterName; ?>&edit" title="Rename"><i class="fa fa-pencil"></i></a><a href="./?projects&id=<?php echo $project->getPID(); ?>&file=<?php echo $fileParameterName; ?>&delete" title="Delete"><i class="fa fa-trash-o"></i></a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php }
