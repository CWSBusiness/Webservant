<?php $applications = JobApp::getPending();

if (count($applications) > 0) { ?>
<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?applications&add" class="top-right-button"><i class="fa fa-plus"></i>Manual Add</a>
	<?php } ?>
	<h2 class="section-heading">Pending Applications</h2>
	<table class="data-table">
		<thead>
			<tr>
				<th>Date</th>
				<th>Name</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($applications as $application) { ?>
			<tr>
				<td title="<?php echo Format::date($application->getSubmissionDate(), Format::DATETIME_FORMAT); ?>"><?php echo Format::relativeTime($application->getSubmissionDate()) ?></td>
				<td><a href="./?applications&id=<?php echo $application->getPID(); ?>"><?php echo $application; ?></a></td>
				<td class="action-column">
					<?php if (User::current()->isSuperAdmin()) { ?>
					<a href="./?applications&id=<?php echo $application->getPID(); ?>&edit" title="Edit"><i class="fa fa-pencil"></i></a>
					<a href="./?applications&id=<?php echo $application->getPID(); ?>&review">Decide</a>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</section>
<?php } ?>
