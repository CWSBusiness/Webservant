<?php

// get a random inspirational quote. Uses the forismatic.com API
$conn = curl_init();
curl_setopt($conn, CURLOPT_URL, "http://api.forismatic.com/api/1.0/");
curl_setopt($conn, CURLOPT_POST, 3);
curl_setopt($conn, CURLOPT_POSTFIELDS, "method=getQuote&format=json&lang=en");
curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($conn);
curl_close($conn);

$quote = "";
if ($result) {
	$result = json_decode($result, true);

	if (!is_null($result)) {

		$quote .= "<blockquote>";
		$quote .= $result["quoteText"];
		if (isset($result["quoteAuthor"]) && $result["quoteAuthor"] != "") {
			$quote .= "<cite>" . $result["quoteAuthor"] . "</cite>";
		}
		$quote .= "</blockquote>";

	}

}

// set the greeting text
if (User::current()->isEmployee()) {
	$today = new DateTime();
	$today->setTime(0, 0, 0);
	$birthday = Employee::current()->getBirthday();
	$birthday->setDate(date("Y", time()), $birthday->format("m"), $birthday->format("d"));
	if ($birthday == $today) {
		$greeting = "Happy birthday, " . Employee::current()->getFirstName() . "!";
	} else {
		$greeting = "Hello, " . Employee::current()->getFirstName() . "!";
	}
} else {
	$greeting = "Hello, " . User::current()->getUsername() . "!";
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-dashboard"></i> Dashboard</h2>

<?php echo $quote; ?>

<section id="announcements">
	<?php if (User::current()->isSuperAdmin()) { ?>
		<div class="top-right-button"><a href="./?announcements"><i class="fa fa-bullhorn"></i>Announcements</a></div>
	<?php } ?>
	<h2 class="section-heading"><?php echo $greeting; ?></h2>
	<?php include "php/pages/announcements/announcements-current.php"; ?>
</section>

<?php if (User::current()->isAdmin()) { ?>
	
<?php include "php/pages/applications/applications-pending.php"; ?>

<section>
	<a href="./?projects&viewall" class="top-right-button"><i class="fa fa-bars"></i>All Projects</a>
	<h2 class="section-heading">Current Projects</h2>
	<?php $activeProjects = Project::getActive();
	if (count($activeProjects) == 0) { ?>
		<p>There are no current projects to display.</p>
	<?php } else { ?>
		<table class="data-table">
			<thead>
			<tr>
				<th>Project Name</th>
				<th>Client</th>
				<!--<th>Status</th>-->
			</tr>
			</thead>
			<tbody>
			<?php foreach ($activeProjects as $project) { ?>
				<tr>
					<td><a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project->getName(); ?></a></td>
					<td><a href="./?clients&company=<?php echo $project->getCompanyID(); ?>"><?php echo $project->getCompanyName(); ?></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</section>

<?php } ?>


<?php include_once "php/footer.php";
die();