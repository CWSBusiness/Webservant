<?php

// include globally-defined constants
require_once "php/constants.php";

// register the classes directory for auto-loading
spl_autoload_register(function($class) {
	include_once "php/classes/$class.php";
});

include_once "php/header.php"; ?>


	<h2 class="breadcrumbs">Forbidden</h2>

	<p>Oops! Whatever you’ve attempted to access is restricted.</p>
	<p>There may be nothing at all here, or there may be a very good reason you can’t see something.</p>
	<p>If a link took you here, please <a href="mailto:<?php echo ADMIN_EMAIL; ?>">let me know</a> so we can fix the issue.</p>

	<h2 style="margin-top: 3em;"><a href="./"><i class="fa fa-home"></i>Return Home</a></h2>

<?php include_once "php/footer.php";
die(); ?>