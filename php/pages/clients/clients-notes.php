<?php

try {
	$client = Client::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?clients");
}

$notes = $client->getNotes();

$errors = new ErrorCollector();

if (isset($_POST["clientNotesSubmit"])) {

	$continue = true;

	$notes = $_POST["clientNotes"];
	try {
		$client->setNotes($notes);
	} catch (InvalidArgumentException $e) {
		$continue = false;
		$errors->addError("Please validate your input.", ErrorCollector::WARNING);
	}

	if ($continue) {
		$success = $client->save();
		if ($success) {
			Auth::redirect("./?clients&id=" . $client->getPID());
		} else {
			$errors->addError("Your changes were unable to be saved.", ErrorCollector::DANGER);
		}
	}

}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <a href="./?clients&id=<?php echo $client->getPID(); ?>"><?php echo $client; ?></a> &gt; Notes</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Notes</h2>
		<label for="clientNotes" class="label-hide">Notes</label>
		<textarea id="clientNotes" name="clientNotes"><?php echo $notes; ?></textarea>
	</section>

	<input type="hidden" name="clientNotesSubmit">
	<button type="submit"><i class="fa fa-check-circle"></i>Save Changes</button>

</form>

<?php include_once "php/footer.php";
die();