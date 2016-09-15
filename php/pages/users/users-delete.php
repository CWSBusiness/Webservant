<?php

if (!User::current()->isAdmin()) {
	Auth::redirect("./");
}

$errors = new ErrorCollector();

if (isset($_POST['userDeleteSubmit'])) {
	$user = User::withID($_GET['delete']);
	$success = $user->delete();
	if ($success) {
		Auth::redirect("./?users");
	} else {
		$errors->addError("The user could not be deleted.", ErrorCollector::DANGER);
	}
}

include_once "php/header.php"; ?>

<h2 class="breadcrumbs"><i class="fa fa-user"></i> <a href="./?users">Users</a> &gt; Delete User</h2>

<form action="" method="post">

	<section>
		<h2 class="section-heading">Please Confirm</h2>
		<p>Are you sure you wish to delete the user "<?php echo User::withID( $_GET['delete'] )->getUsername(); ?>"?</p>
	</section>

	<input type="hidden" name="userDeleteSubmit">
	<button type="submit" class="delete-button"><i class="fa fa-trash-o"></i>Delete User</button>
	<a href="./?users" class="delete-link">cancel</a>

</form>

<?php include_once "php/footer.php";