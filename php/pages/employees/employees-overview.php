<?php

$employeesCurrent = Employee::getCurrent();
$employeesPast = Employee::getPast();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> Employees</h2>

<?php if (!User::current()->isAdmin()) {
	Auth::redirect("./");
} ?>

	<section>
		<?php if (User::current()->isSuperAdmin()) { ?>
		<a href="./?employees&add" class="top-right-button"><i class="fa fa-plus"></i>Add New</a>
		<?php } ?>
		<h2 class="section-heading">Current Employees</h2>
		<?php if (count($employeesCurrent) == 0) { ?>
			<p>No current employees to display.</p>
		<?php } else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<?php if (User::current()->isSuperAdmin()) { ?>
						<th></th>
						<?php } ?>
						<th>Name</th>
						<th>Position</th>
						<th>Start Date</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($employeesCurrent as $row) { ?>
						<tr>
							<?php if (User::current()->isSuperAdmin()) { ?>
							<td>
								<?php if ($row->getProfilePic()) { ?>
									<a href="./?employees&id=<?php echo $row->getPID(); ?>&profilepic" title="Change Profile Pic" class="profile-pic profile-pic-small" style="background-image: url('<?php echo $row->getProfilePic(); ?>');"></a>
								<?php } else { ?>
									<a href="./?employees&id=<?php echo $row->getPID(); ?>&profilepic" title="Add Profile Pic" class="profile-pic profile-pic-small"><i class="fa fa-picture-o" style="font-size: 1em; position: relative; top: 0.2em;"></i></a>
								<?php } ?>
							</td>
							<?php } ?>
							<td>
								<a href="./?employees&id=<?php echo $row->getPID(); ?>">
									<?php echo $row->getLastName() . ", " . $row->getFirstName(); ?>
								</a>
							</td>
							<td><?php echo $row->getPosition(); ?></td>
							<td><?php echo Format::date($row->getStartDate()); ?></td>
							<td class="action-column">
								<?php if (User::current()->isSuperAdmin()) { ?>
								<a href="./?employees&id=<?php echo $row->getPID(); ?>&edit" title="Edit <?php echo $row; ?>"><i class="fa fa-pencil"></i></a>
								<?php } ?>
								<?php if (User::current()->isAdmin()) { ?>
								<a href="./?finances&employee=<?php echo $row->getPID(); ?>" title="View Employee Finances"><i class="fa fa-usd"></i></a>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</section>

	<section>
		<h2 class="section-heading">Past Employees</h2>
		<?php if (count($employeesPast) == 0) { ?>
			<p>No past employees to display.</p>
		<?php } else { ?>
			<table class="data-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Position</th>
						<th>Start Date</th>
						<th>End Date</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($employeesPast as $row) { ?>
						<tr>
							<td><a href="./?employees&id=<?php echo $row->getPID(); ?>"><?php echo $row->getLastName() . ", " . $row->getFirstName(); ?></a></td>
							<td><?php echo $row->getPosition(); ?></td>
							<td><?php echo Format::date($row->getStartDate()); ?></td>
							<td><?php echo Format::date($row->getEndDate()); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</section>

<?php include_once "php/footer.php";
die();