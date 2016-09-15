<?php

// include globally-defined constants
require_once "php/constants.php";

// register the classes directory for auto-loading
spl_autoload_register(function($class) {
	include_once "php/classes/$class.php";
});

include_once "php/header.php"; ?>


<h2 class="breadcrumbs">Not Found</h2>

<p>Whatever you’re trying to find could not be located on the server.</p>
<p>You may have ended up here because of a mistyped URL or a broken link. If that’s the case, please <a href="mailto:<?php echo ADMIN_EMAIL; ?>">let us know about it</a> so we can fix the issue.</p>

<h2 style="margin-top: 3em;"><a href="./"><i class="fa fa-home"></i>Return Home</a></h2>

<?php include_once "php/footer.php";
die(); ?>