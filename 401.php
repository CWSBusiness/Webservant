<?php

// include globally-defined constants
require_once "php/constants.php";

// register the classes directory for auto-loading
spl_autoload_register(function($class) {
	include_once "php/classes/$class.php";
});

include_once "php/header.php"; ?>


<h2 class="breadcrumbs">Authorization Required</h2>

<p>You need to log in to view the content you’ve attempted to access.</p>
<p>Unfortunately, the server couldn’t verify that you have authenticated.</p>
<p>Try a <a href="javascript:document.location.reload(true)">refresh</a> and re-enter your credentials.</p>
<p>If you’re not sure what happened, try a <a href="javascript:document.location.reload(true)">refresh</a>.</p>

<h2 style="margin-top: 3em;"><a href="./"><i class="fa fa-home"></i>Return Home</a></h2>

<?php include_once "php/footer.php";
die(); ?>