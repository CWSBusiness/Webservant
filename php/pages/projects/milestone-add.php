<?php
$project = Project::withID($_GET['id']);

$data = array(
	"name" => "",
	"status" => 1,
	"revenue" => "",
	"dueDate" => "",
	"teamLeadID" => "",
	"description" => "",
);

$errors = new ErrorCollector();

$timeZero = new DateTime();
$timeZero->setTimestamp(0);

if (isset($_POST["milestoneAddSubmit"])) {

	$continue = true;

	$data["name"] = $_POST["milestoneName"];
	if (!Validate::plainText($data["name"])) {
		$continue = false;
		$errors->addError("Please enter a valid milestone name.", ErrorCollector::WARNING);
	} else if (array_key_exists(strtolower($data["name"]), $project->getMilestones())) {
		$continue = false;
		$errors->addError("The milestone name you chose is already in use.", ErrorCollector::WARNING);
	}

	$data["dueDate"] = $_POST["dueDate"];
	try {
		$data["dueDate"] = DateTime::createFromFormat(Format::DATE_FORMAT, $data["dueDate"]);
		if ($timeZero > $data["dueDate"]) {
			throw new Exception();
		}
	} catch (Exception $e) {
		$continue = false;
		$errors->addError("Please enter a valid due date.", ErrorCollector::WARNING);
	}

	$data["description"] = $_POST["description"];
	if (!Validate::plainText($data["description"], true)) {
		$continue = false;
		$errors->addError("Please enter a valid description.", ErrorCollector::WARNING);
	}

	$data["revenue"] = $_POST["revenue"];
	try {
		$data["revenue"] = new Money($data["revenue"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid revenue value.", ErrorCollector::WARNING);
	}

	$data["status"] = $_POST["milestoneStatus"];

	$data["teamLeadID"] = $_POST["teamLead"];

	if ($continue) {
		try {
			$zeroMoney = new Money("0");
			$milestone = new Milestone($project->getPID(), $data["name"], $data["dueDate"], $data["teamLeadID"], $data["status"], $data["revenue"], $zeroMoney, $zeroMoney, $zeroMoney, array(), $data["description"]);
			$project->addMilestone($milestone);
			$project->save();
			Auth::redirect("./?projects&id=" . $project->getPID() . "&milestone=" . $milestone->getNameForParameter());
		} catch (InvalidArgumentException $e) {
			$errors->addError("The milestone could not be created.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-cloud"></i> <a href="./?projects">Projects</a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>"><?php echo $project; ?></a> &gt; <a href="./?projects&id=<?php echo $project->getPID(); ?>&milestones">Milestones</a> &gt; Add</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Overview</h2>

		<table>
			<tr>
				<th><label for="milestoneName">Milestone Name</label></th>
				<td><input id="milestoneName" name="milestoneName" type="text" value="<?php echo $data["name"]; ?>" placeholder="This cannot be changed later"></td>
			</tr>
			<tr>
				<th><label for="dueDate">Due Date</label></th>
				<td><input id="dueDate" name="dueDate" type="text" value="<?php echo ($data["dueDate"] instanceof DateTime) ? $data["dueDate"]->format(Format::DATE_FORMAT) : $data["dueDate"]; ?>" placeholder="MM/DD/YYYY"></td>
			</tr>
			<tr>
				<th><label for="description">Description</label></th>
				<td><textarea id="description" name="description"><?php echo $data["description"]; ?></textarea></td>
			</tr>
			<tr>
				<th><label for="revenue">Revenue</label></th>
				<td><input id="revenue" name="revenue" type="text" value="<?php echo $data["revenue"]; ?>" placeholder="$"></td>
			</tr>
			<tr>
				<th><label for="milestoneStatus">Status</label></th>
				<td>
					<select name="milestoneStatus" id="milestoneStatus">
						<?php foreach (Milestone::statusValues() as $statusInt => $statusText) { ?>
							<option value="<?php echo $statusInt; ?>" <?php echo ($data["status"] == $statusInt) ? "selected" : ""; ?>><?php echo $statusText; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="teamLead">Team Lead</label></th>
				<td>
					<select name="teamLead" id="teamLead">
						<option value="0" <?php echo ($data["teamLeadID"] == 0) ? "selected" : ""; ?>>Unassigned</option>
						<option value="0" disabled>Current Team Leads</option>
						<?php foreach (Employee::getCurrent() as $employee) {
							if (strtolower($employee->getPosition()) == "team lead" || strtolower($employee->getPosition()) == "project manager") { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($employee->getPID() == $data["teamLeadID"]) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php }
						} ?>
						<option value="0" disabled>Past Team Leads</option>
						<?php foreach (Employee::getPast() as $employee) {
							if (strtolower($employee->getPosition()) == "team lead" || strtolower($employee->getPosition()) == "project manager") { ?>
								<option value="<?php echo $employee->getPID(); ?>" <?php echo ($employee->getPID() == $data["teamLeadID"]) ? "selected" : ""; ?>><?php echo $employee; ?></option>
							<?php }
						} ?>
					</select>
				</td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="milestoneAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";
die();