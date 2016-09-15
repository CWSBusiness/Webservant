<?php

$doc = Doc::withID($_GET["id"]);
$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<a href="./?docs&id=<?php echo $doc->getPID(); ?>&edit" class="top-right-button"><i class="fa fa-pencil"></i>Edit</a>
<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revisions" class="top-right-button"><i class="fa fa-history"></i>Revisions</a>
<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <?php echo $doc; ?></h2>

<section class="markdown">
	<?php echo $parsedown->text($doc->getContents()); ?>
</section>

<?php include_once "php/footer.php";
die();