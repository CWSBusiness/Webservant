<?php

try {
	$client = Client::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?clients");
}

$status_message = "";

$errors = new ErrorCollector();

if (isset($_POST["deleteSubmit"])) {
	if (count($client->getProjects()) > 0) {
		$errors->addError("Client records with a project history cannot be deleted.", ErrorCollector::DANGER);
	} else {
		$result = $client->delete();
		if ($result) {
			Auth::redirect("./?clients");
		} else {
			$errors->addError("An error occurred trying to delete the client record.", ErrorCollector::DANGER);
		}
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-briefcase"></i> <a href="./?clients">Clients</a> &gt; <a href="./?clients&id=<?php echo $client->getPID(); ?>"><?php echo $client; ?></a> &gt; Delete</h2>

<form action="" method="post">

	<?php echo $errors; ?>

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you want to delete all records of <?php echo $client; ?>?</p>
		<p>This action can't be undone.</p>
	</section>

	<input type="hidden" name="deleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete</button>
	<a href="./?clients&id=<?php echo $client->getPID(); ?>&edit" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";
die();