<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

$errors = new ErrorCollector();

if (isset($_POST["applicationsRejectSubmit"])) {

	try {
		$app->reject();
		Auth::redirect("./?applications&id=" . $app->getPID());
	} catch (Exception $e) {
		$errors->addError("An error occurred rejecting the application.", ErrorCollector::DANGER);
	}

}

$radio = 0;

if (isset($_POST["applicationsApproveSubmit"])) {

	if ($_POST["positionPick"] == 0) {      // the "choose a position dropdown" radio button was selected

		if (!isset($_POST["positionSelect"]) || $_POST["positionSelect"] == 0) {
			$errors->addError("You must select a position.", ErrorCollector::WARNING);
		} else {
			$employee = $app->approve($_POST["positionSelect"]);
			Auth::redirect("./?employees&id=" . $employee->getPID());
		}

	} else if ($_POST["positionPick"] == 1) {   // the "manual entry input" radio button was selectec

		$radio = 1;

		if (isset($_POST["positionInput"]) && Validate::plainText($_POST["positionInput"])) {
			$employee = $app->approve($_POST["positionInput"]);
			Auth::redirect("./?employees&id=" . $employee->getPID());
		} else {
			$errors->addError("Please enter a valid position title.", ErrorCollector::DANGER);
		}

	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> <a href="./?applications">Job Applications</a> &gt; <a href="./?applications&id=<?php echo $app->getPID(); ?>"><?php echo $app; ?></a> &gt; Decide</h2>

<?php echo $errors; ?>

<form action="" method="post">

	<section>
		<h2 class="section-heading">Approve Application</h2>
		<p>Permanently approve the job application.<br>This will create an employee record from the applicable data.</p>

		<table>
			<tr>
				<th rowspan="2"><label for="positionSelect">Assign a Position</label></th>
				<td><input type="radio" name="positionPick" id="positionPickSelect" value="0" <?php echo ($radio == 0) ? "checked" : ""; ?>></td>
				<td>
					<select id="positionSelect" name="positionSelect">
						<option value="0" selected disabled>Choose a Position</option>
						<?php foreach ($app->getPositionsAppliedFor() as $code => $position) { ?>
							<option value="<?php echo $code; ?>"><?php echo $position["title"]; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><input type="radio" name="positionPick" id="positionPickInput" value="1" <?php echo ($radio == 1) ? "checked" : ""; ?>></td>
				<td><input type="text" id="positionInput" value="" placeholder="Or enter a new position"></td>
			</tr>
		</table>
	</section>

	<input type="hidden" name="applicationsApproveSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Approve</button>

</form>

<form action="" method="post">

	<section>
		<h2 class="section-heading">Reject Application</h2>
		<p>Permanently reject the job application.</p>
	</section>

	<input type="hidden" name="applicationsRejectSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-times-circle"></i>Reject</button>

</form>

<?php include_once "php/footer.php";
die();