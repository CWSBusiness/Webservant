<?php

// include globally-defined constants
require_once "php/constants.php";

// register the classes directory for auto-loading
spl_autoload_register(function($class) {
	include_once "php/classes/$class.php";
});

include_once "php/header.php"; ?>


	<h2 class="breadcrumbs">Internal Server Error</h2>

	<p>The server encountered an internal error or misconfiguration and was unable to complete your request.</p>
	<p>Basically, something went wrong while trying to load your page. Hopefully a good hard <a href="javascript:document.location.reload(true)">refresh</a> will fix the problem.</p>
	<p>If this persists, please <a href="mailto:<?php echo ADMIN_EMAIL; ?>">let us know</a> so we can fix the issue.</p>

	<h2 style="margin-top: 3em;"><a href="./"><i class="fa fa-home"></i>Return Home</a></h2>

<?php include_once "php/footer.php";
die(); ?>