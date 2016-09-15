<?php

$employee = Employee::withID($_GET['id']);

$parsedown = new Parsedown();

$projects = Project::getAll();

/** @var Project[] $currentProjects */
$currentProjects = array();
/** @var Project[] $projectHistory */
$projectHistory = array();

foreach ($projects as $project) {
	foreach ($project->getMilestones() as $milestone) {
		if ($milestone->getTeamLeadID() == $employee->getPID()) {
			if ($milestone->getStatus() > 0) {
				array_push($currentProjects, $project);
			}
			array_push($projectHistory, $project);
		} else {
			foreach ($milestone->getTasks() as $task) {
				foreach ($task->getAssignees() as $assignee) {
					if ($employee == $assignee) {
						if ($milestone->getStatus() > 0 && !in_array($project, $currentProjects)) {
							array_push($currentProjects, $project);
						}
						if (!in_array($project, $projectHistory)) {
							array_push($projectHistory, $project);
						}
					}
				}
			}
		}
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-male"></i> <a href="./?employees">Employees</a> &gt; <?php echo $employee; ?></h2>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?employees&id=<?php echo $employee->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Employee</a>
	<?php } ?>
	<h2 class="section-heading">Employment Details</h2>

	<table>
		<tr>
			<th>Position</th>
			<td><?php echo $employee->getPosition(); ?></td>
		</tr>
		<?php if ($employee->getApplicationID() != 0) { ?>
			<tr>
				<th>Job App</th>
				<td><a href="./?applications&id=<?php echo $employee->getApplicationID(); ?>">View Job Application</a></td>
			</tr>
		<?php } ?>
		<tr>
			<th>Start Date</th>
			<td><?php echo Format::date($employee->getStartDate()); ?></td>
		</tr>
		<tr>
			<th>End Date</th>
			<td><?php echo ($employee->isCurrentEmployee()) ? "N/A (current employee)" : Format::date($employee->getEndDate()); ?></td>
		</tr>
		<tr>
			<th>Finances</th>
			<td><a href="./?finances&employee=<?php echo $employee->getPID(); ?>"><i class="fa fa-usd"></i>View Financial History</a></td>
		</tr>
	</table>
</section>

<section>
	<h2 class="section-heading">Contact Info</h2>

	<table>
		<tr>
			<th>Email Address</th>
			<td><a href="mailto:<?php echo $employee->getEmail(); ?>"><i class="fa fa-envelope"></i><?php echo $employee->getEmail(); ?></a></td>
		</tr>
		<?php if ($employee->getPhone() != "") { ?>
			<tr>
				<th>Phone Number</th>
				<td><a href="tel:<?php echo Format::tel($employee->getPhone()); ?>"><i class="fa fa-phone"></i><?php echo Format::phone($employee->getPhone()); ?></a></td>
			</tr>
		<?php } ?>
		<?php if ($employee->getWebsiteURL() != "") { ?>
			<tr>
				<th>Website</th>
				<td><a href="<?php echo $employee->getWebsiteURL(); ?>"><i class="fa fa-globe"></i><?php echo $employee->getWebsiteURL(); ?></a></td>
			</tr>
		<?php }
		if ($employee->getGitHubURL() != "") { ?>
			<tr>
				<th>GitHub</th>
				<td><a href="<?php echo $employee->getGitHubURL(); ?>"><i class="fa fa-github"></i><?php echo $employee->getGitHubURL(); ?></a></td>
			</tr>
		<?php }
		if ($employee->getLinkedInURL() != "") { ?>
			<tr>
				<th>LinkedIn</th>
				<td><a href="<?php echo $employee->getLinkedInURL(); ?>"><i class="fa fa-linkedin-square"></i><?php echo $employee->getLinkedInURL(); ?></a></td>
			</tr>
		<?php } ?>
	</table>
</section>

<section>
	<h2 class="section-heading">Profile</h2>

	<table>
		<tr>
			<th>Full Name</th>
			<td><?php echo $employee; ?></td>
		</tr>
		<tr>
			<th>Birthday</th>
			<td><?php echo Format::date($employee->getBirthday(), Format::BIRTHDAY_FORMAT); ?></td>
		</tr>
		<tr>
			<th>NetID</th>
			<td><?php echo $employee->getNetID(); ?></td>
		</tr>
		<tr>
			<th>Faculty</th>
			<td><?php echo $employee->getFaculty(); ?></td>
		</tr>
		<tr>
			<th>Major</th>
			<td><?php echo $employee->getMajor(); ?></td>
		</tr>
		<?php if (User::current()->isSuperAdmin()) { ?>
		<tr>
			<th>Profile Pic</th>
			<td>
				<?php if ($employee->getProfilePic()) { ?>
					<div class="profile-pic" style="background-image: url('<?php echo $employee->getProfilePic(); ?>'); float: left;"></div>
					<a href="./?employees&id=<?php echo $employee->getPID(); ?>&profilepic"><i class="fa fa-picture-o"></i>Change Pic</a>
					<div style="clear: both;"></div>
				<?php } else { ?>
					<a href="./?employees&id=<?php echo $employee->getPID(); ?>&profilepic"><i class="fa fa-picture-o"></i>Add Pic</a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
</section>

<section>
	<h2 class="section-heading">Current Projects</h2>
	<?php if (count($currentProjects) == 0) { ?>
	<p>No projects to display.</p>
	<?php } else { ?>
	<ul>
		<?php foreach ($currentProjects as $project) { ?>
		<li><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a></li>
		<?php } ?>
	</ul>
	<?php } ?>
</section>

<section>
	<h2 class="section-heading">Project History</h2>
	<?php if (count($projectHistory) == 0) { ?>
	<p>No projects to display.</p>
	<?php } else { ?>
	<ul>
		<?php foreach ($projectHistory as $project) { ?>
		<li><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a></li>
		<?php } ?>
	</ul>
	<?php } ?>
</section>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?employees&id=<?php echo $employee->getPID(); ?>&notes" class="top-right-button"><i class="fa fa-edit"></i>Edit Notes</a>
	<?php } ?>
	<h2 class="section-heading">Notes</h2>
	<?php if ($employee->getNotes() == "") { ?>
		<p>No notes to display.</p>
	<?php } else {
		echo $parsedown->text($employee->getNotes());
	} ?>
</section>

<?php include_once "php/footer.php";
die();