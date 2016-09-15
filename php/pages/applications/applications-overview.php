<?php include_once "php/header.php" ?>

<h2 class="breadcrumbs"><i class="fa fa-user-plus"></i> Job Applications</h2>

<?php include "php/pages/applications/applications-pending.php"; ?>

<section id="past-applications">
	<h2 class="section-heading">Past Applications</h2>
	<?php include "php/pages/applications/applications-past.php"; ?>
</section>

<?php include_once "php/footer.php";
die();