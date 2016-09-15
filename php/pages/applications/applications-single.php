<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; <?php echo $app; ?></h2>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?applications&id=<?php echo $app->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit Application</a>
	<?php } ?>
	<h2 class="section-heading">Applicant Information</h2>
	<table>
		<tr>
			<th>Date of Submission</th>
			<td><?php echo Format::date($app->getSubmissionDate(), Format::DATETIME_FORMAT); ?></td>
		</tr>
		<tr>
			<th>Application Status</th>
			<td><?php echo $app->getStatusText(); ?></td>
		</tr>
		<tr>
			<th>Full Name</th>
			<td><?php echo $app; ?></td>
		</tr>
		<tr>
			<th>Birthday</th>
			<td><?php echo Format::date($app->getBirthday(), Format::BIRTHDAY_FORMAT); ?></td>
		</tr>
		<tr>
			<th>netID</th>
			<td><?php echo $app->getNetID(); ?></td>
		</tr>
		<tr>
			<th>Faculty / Major</th>
			<td><?php echo $app->getFaculty(); ?> / <?php echo $app->getMajor(); ?></td>
		</tr>
		<tr>
			<th>Year</th>
			<td><?php echo Format::ordinal($app->getYear()); ?></td>
		</tr>
	</table>
</section>

<section>
	<h2 class="section-heading">Additional Information</h2>
	<table>
		<tr>
			<th>Email Address</th>
			<td><a href="mailto:<?php echo $app->getEmail(); ?>"><i class="fa fa-envelope"></i><?php echo $app->getEmail(); ?></a></td>
		</tr>
		<tr>
			<th>Phone Number</th>
			<td><a href="tel:<?php echo Format::tel($app->getPhone()); ?>"><i class="fa fa-phone"></i><?php echo Format::phone($app->getPhone()); ?></a></td>
		</tr>
		<?php if ($app->getWebsiteURL() != "") { ?>
		<tr>
			<th>Website</th>
			<td><a href="<?php echo $app->getWebsiteURL(); ?>"><i class="fa fa-globe"></i><?php echo $app->getWebsiteURL(); ?></a></td>
		</tr>
		<?php }
		if ($app->getGitHubURL() != "") { ?>
		<tr>
			<th>GitHub</th>
			<td><a href="<?php echo $app->getGitHubURL(); ?>"><i class="fa fa-github"></i><?php echo $app->getGitHubURL(); ?></a></td>
		</tr>
		<?php }
		if ($app->resumeIsURL() || $app->resumeIsFile()) { ?>
		<tr>
			<th>Resumé</th>
			<td><?php if ($app->resumeIsURL()) { ?>
				<a href="<?php echo $app->getResumeURL(); ?>"><i class="fa fa-link"></i><?php echo Format::truncate($app->getResumeURL(), 40); ?></a>
				<?php } else { ?>
				<a href="<?php echo $app->getResumeFile(); ?>"><i class="fa fa-file-text-o"></i>View</a> <a href="./?applications&id=<?php echo $app->getPID(); ?>&resumedownload"><i class="fa fa-download"></i>Download</a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
</section>

<section>
	<?php if ($app->getStatus() == 0 && User::current()->isSuperAdmin()) { ?>
		<a href="./?applications&id=<?php echo $app->getPID(); ?>&review" class="top-right-button">Decide</a>
	<?php } ?>
	<h2 class="section-heading">Position Choices</h2>

		<?php $myPositions = $app->getPositionsAppliedFor();
		if (empty($myPositions)) { ?>
			<p>No positions selected.</p>
		<?php } else {
			$allPositions = Employee::positions(); ?>
			<table>
			<?php foreach ($allPositions as $code => $position) { ?>
				<tr>
					<td class="icon-column"><?php echo (array_key_exists($code, $myPositions)) ? "<i class=\"fa fa-check\"></i>" : "" ?></td>
					<td><?php echo $position["title"]; ?></td>
				</tr>
			<?php } ?>
	</table>
		<?php } ?>
</section>

<section>
	<h2 class="section-heading">Application Questions</h2>
	<?php if (count($app->getQuestions()) == 0) { ?>
		<p>No answers to display.</p>
	<?php } else { ?>
	<ol>
		<?php foreach ($app->getQuestions() as $question) {
			if ($question["question"] != "") { ?>
				<li style="margin-bottom: 2rem;">
					<h3 style="font-weight: 400; margin-bottom: 0.5rem;"><?php echo $parsedown->text($question["question"]); ?></h3>
					<?php echo $parsedown->text($question["answer"]); ?>
				</li>
			<?php }
		} ?>
	</ol>
	<?php } ?>
</section>

<section>
	<?php if (User::current()->isSuperAdmin()) { ?>
	<a href="./?applications&id=<?php echo $app->getPID(); ?>&notes" class="top-right-button"><i class="fa fa-edit"></i>Edit Notes</a>
	<?php } ?>
	<h2 class="section-heading">Notes</h2>
	<?php echo ($app->getNotes() == "") ? "<p>No notes to display.</p>" : $parsedown->text($app->getNotes()); ?>
</section>

<?php
include_once "php/footer.php";
die();