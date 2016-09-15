<?php

$client = array(
	"first_name" => "",
	"last_name" => "",
	"companyID" => "",
	"company_name" => "",
	"position" => "",
	"current_contact" => true,
	"phone" => "",
	"email" => ""
);

$newClientName = "";

$errors = new ErrorCollector();

if (isset($_POST["clientAddSubmit"])) {

	$continue = true;

	$client["first_name"] = $_POST["firstName"];
	if (!Validate::name($_POST["firstName"])) {
		$continue = false;
		$errors->addError("Please enter a valid first name.", ErrorCollector::WARNING);
	}

	$client["last_name"] = $_POST["lastName"];
	if (!Validate::name($_POST["lastName"])) {
		$continue = false;
		$errors->addError("Please enter a valid last name.", ErrorCollector::WARNING);
	}

	if (isset($_POST["newClient"]) && $_POST["newClient"] != "") {
		$newClientName = $_POST["newClient"];
		$client["company_name"] = $newClientName;
		if (Validate::plainText($_POST["newClient"])) {
			$client["companyID"] = Client::getNewCompanyID();
		} else {
			$continue = false;
			$errors->addError("Please enter a valid organization name.", ErrorCollector::WARNING);
		}
	} else {
		if (isset($_POST["clientSelect"]) && $_POST["clientSelect"] > 0) {
			$client["companyID"] = $_POST["clientSelect"];
			$client["company_name"] = Client::getCompanyNameByID($client["companyID"]);
		} else {
			$continue = false;
			$errors->addError("Please select an existing organization or enter a new one.", ErrorCollector::WARNING);
		}
	}

	$client["position"] = $_POST["position"];
	if (!Validate::plainText($_POST["position"])) {
		$continue = false;
		$errors->addError("Please enter a valid position title.", ErrorCollector::WARNING);
	}

	$client["current_contact"] = (isset($_POST["status"]));

	if ($_POST["phone"] == "") {
		$client["phone"] = "";
	} else if (Validate::phone($_POST["phone"])) {
		$client["phone"] = Format::phone($_POST["phone"]);
	} else {
		$client["phone"] = $_POST["phone"];
		$continue = false;
		$errors->addError("Please enter a valid phone number.", ErrorCollector::WARNING);
	}

	$client["email"] = $_POST["email"];
	if ($_POST["email"] != "") {
		if (!Validate::email($_POST["email"])) {
			$continue = false;
			$errors->addError("Please enter a valid email address.", ErrorCollector::WARNING);
		}
	}

	if ($continue) {
		$newClient = new Client($client["companyID"], $client["company_name"], $client["first_name"], $client["last_name"], $client["position"], $client["current_contact"], $client["phone"], $client["email"]);
		$result = $newClient->save();
		if ($result) {
			Auth::redirect("./?clients&id=" . $newClient->getPID());
		} else {
			$errors->addError("An error occurred trying to create the new client.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; New Client</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Client Info</h2>
		<table>
			<tr>
				<th><label for="firstName">Full Name</label></th>
				<td>
					<label for="firstName" class="label-hide">First Name</label>
					<input name="firstName" id="firstName" type="text" value="<?php echo $client["first_name"]; ?>" placeholder="First" autocorrect="off" spellcheck="false">
				</td>
				<td>
					<label for="lastName" class="label-hide">Last Name</label>
					<input name="lastName" id="lastName" type="text" value="<?php echo $client["last_name"]; ?>" placeholder="Last" autocorrect="off" spellcheck="false">
				</td>
			</tr>
			<tr>
				<th><label for="clientSelect">Organization</label></th>
				<td>
					<select name="clientSelect" id="clientSelect">
						<option value="0" disabled <?php echo ($client["companyID"] == "") ? "selected" : "" ?>>Select Company</option>
						<?php foreach (Client::getAllCompanies() as $companyID => $companyName) { ?>
							<option value="<?php echo $companyID; ?>" <?php echo ($client["companyID"] == $companyID) ? "selected" : "" ?>><?php echo $companyName; ?></option>
						<?php } ?>
					</select>
				</td>
				<td>
					<input type="text" name="newClient" id="newClient" value="<?php echo $newClientName; ?>" placeholder="or enter a new organization name">
				</td>
			</tr>
			<tr>
				<th><label for="position">Position</label></th>
				<td colspan="2"><input name="position" id="position" type="text" value="<?php echo $client["position"]; ?>" placeholder="eg. IT Administrator"></td>
			</tr>
		</table>
	</section>

	<section>
		<h2 class="section-heading">Contact Info</h2>
		<table>
			<tr>
				<th><span class="label">Current Contact</span></th>
				<td>
					<input name="status" id="status" type="checkbox" value="1" <?php echo ($client["current_contact"]) ? "checked" : ""; ?>>
					<label for="status">Make a current technical contact</label>
				</td>
			</tr>
			<tr>
				<th><label for="email">Email Address</label></th>
				<td><input name="email" id="email" type="email" value="<?php echo $client["email"]; ?>"></td>
			</tr>
			<tr>
				<th><label for="phone">Phone Number</label></th>
				<td><input type="tel" id="phone" name="phone" value="<?php echo $client["phone"]; ?>"></td>
			</tr>
		</table>
	</section>

	<input type="hidden" name="clientAddSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Create</button>

</form>

<?php include_once "php/footer.php";
die();