<?php

$pid = $_GET["company"];
$companyOrig = Client::getCompanyNameByID($pid);
$company = $companyOrig;

$errors = new ErrorCollector();

if (isset($_POST["companyEditSubmit"])) {

	$company = $_POST["company"];
	if (Validate::plainText($company)) {
		try {
			Client::setCompanyNameForAll($pid, $company);
			Auth::redirect("./?clients&company=" . $pid);
		} catch (Exception $e) {
			$errors->addError($e->getMessage(), ErrorCollector::DANGER);
		}
	} else {
		$errors->addError("Please enter a valid name for the organization.", ErrorCollector::WARNING);
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <a href="./?clients&company=<?php echo $pid; ?>"><?php echo $companyOrig; ?></a> &gt; Edit Name</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Rename</h2>

		<table>
			<tr>
				<th><label for="company">New Name</label></th>
				<td><input type="text" name="company" id="company" value="<?php echo $company; ?>" placeholder="or enter a new organization name"></td>
			</tr>
		</table>

	</section>

	<input type="hidden" name="companyEditSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();