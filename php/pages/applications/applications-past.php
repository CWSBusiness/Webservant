<?php $applications = JobApp::getPast();

if (count($applications) == 0) { ?>
	<p>No job applications to display.</p>
<?php } else { ?>
	<table class="data-table">
		<thead>
			<tr>
				<th>Date</th>
				<th>Name</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($applications as $application) { ?>
			<tr>
				<td title="<?php echo Format::date($application->getSubmissionDate(), Format::DATETIME_FORMAT); ?>"><?php echo Format::date($application->getSubmissionDate()) ?></td>
				<td><a href="./?applications&id=<?php echo $application->getPID(); ?>"><?php echo $application; ?></a></td>
				<td><?php echo $application->getStatusText(); ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>
