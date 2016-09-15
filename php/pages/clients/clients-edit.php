<?php

try {
	$client = Client::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?clients");
}

$data = array(
	"firstName" => $client->getFirstName(),
	"lastName" => $client->getLastName(),
	"companyName" => $client->getCompanyName(),
	"companyID" => $client->getCompanyID(),
	"position" => $client->getPosition(),
	"is_active" => $client->isCurrentContact(),
	"email" => $client->getEmail(),
	"phone" => $client->getPhone()
);

$newClientName = "";

$errors = new ErrorCollector();

if (isset($_POST["clientEditSubmit"])) {

	$continue = true;

	$data["firstName"] = $_POST["firstName"];
	try {
		$client->setFirstName($data["firstName"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
	}

	$data["lastName"] = $_POST["lastName"];
	try {
		$client->setLastName($data["lastName"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
	}

	if (isset($_POST["newClient"]) && $_POST["newClient"] != "") {
		$data["companyName"] = $newClientName = $_POST["newClient"];
		$data["companyID"] = Client::getNewCompanyID();
		try {
			$client->setCompanyID($data["companyID"]);
			$client->setCompanyName($data["companyName"]);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a valid organization name.", ErrorCollector::WARNING);
		}
	} else {
		$data["companyID"] = $_POST["clientSelect"];
		$data["companyName"] = Client::getCompanyNameByID($data["companyID"]);
		try {
			$client->setCompanyID($data["companyID"]);
			$client->setCompanyName($data["companyName"]);
		} catch (InvalidArgumentException $e) {
			$continue = false;
			$errors->addError("Please enter a new organization name or choose an existing one.", ErrorCollector::WARNING);
		}
	}

	$data["position"] = $_POST["position"];
	try {
		$client->setPosition($data["position"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid position title.", ErrorCollector::WARNING);
	}

	$data["is_active"] = (isset($_POST["status"])) ? true : false;
	if ($data["is_active"]) {
		$client->activate();
	} else {
		$client->deactivate();
	}

	$data["email"] = $_POST["email"];
	try {
		$client->setEmail($data["email"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid email address.", ErrorCollector::WARNING);
	}

	$data["phone"] = $_POST["phone"];
	try {
		$client->setPhone($data["phone"]);
		$data["phone"] = Format::phone($data["phone"]);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$result = $client->save();
		if ($result) {
			Auth::redirect("./?clients&id=" . $client->getPID());
		} else {
			$errors->addError("An error occurred trying to save your changes.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <a href="./?clients&id=<?php echo $client->getPID(); ?>"><?php echo $client; ?></a> &gt; Edit</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Client Info</h2>
		<table>
			<tr>
				<th><label for="firstName">Full Name</label></th>
				<td>
					<label for="firstName" class="label-hide">First Name</label>
					<input name="firstName" id="firstName" type="text" value="<?php echo $data["firstName"]; ?>" placeholder="First Name" autocorrect="off" spellcheck="false">
				</td>
				<td>
					<label for="lastName" class="label-hide">Last Name</label>
					<input name="lastName" id="lastName" type="text" value="<?php echo $data["lastName"]; ?>" placeholder="Last Name" autocorrect="off" spellcheck="false">
				</td>
			</tr>
			<tr>
				<th><label for="clientSelect">Organization</label></th>
				<td>
					<select name="clientSelect" id="clientSelect">
						<?php foreach (Client::getAllCompanies() as $companyID => $companyName) { ?>
							<option value="<?php echo $companyID; ?>" <?php echo ($data["companyID"] == $companyID) ? "selected" : ""; ?>><?php echo $companyName; ?></option>
						<?php } ?>
					</select>
				</td>
				<td>
					<input type="text" name="newClient" id="newClient" value="<?php echo $newClientName; ?>" placeholder="or enter a new organization name">
					<p>Note: to rename an existing organization for all its clients,<br>do so from the company details page</p>
				</td>
			</tr>
			<tr>
				<th><label for="position">Position</label></th>
				<td colspan="2"><input name="position" id="position" type="text" value="<?php echo $data["position"]; ?>"></td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Contact Info</h2>
		<table>
			<tr>
				<th><span class="label">Current Contact</span></th>
				<td>
					<input name="status" id="status" type="checkbox" value="1" style="width: auto;" <?php echo ($data["is_active"]) ? "checked" : ""; ?>>
					<label for="status">Current technical contact</label>
				</td>
			</tr>
			<tr>
				<th><label for="email">Email Address</label></th>
				<td><input name="email" id="email" type="email" value="<?php echo $data["email"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td><input type="tel" id="phone" name="phone" value="<?php echo $data["phone"]; ?>"></td>
			</tr>
		</table>
	</section>

	<a href="./?clients&id=<?php echo $client->getPID(); ?>&delete" class="top-right-button delete-link" tabindex="-1"><i class="fa fa-trash-o"></i>Delete Client</a>
	<input type="hidden" name="clientEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();