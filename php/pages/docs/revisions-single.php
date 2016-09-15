<?php

$doc = Doc::withID($_GET["id"]);
$revisions = $doc->getRevisionHistory();
if (array_key_exists($_GET["revision"], $revisions)) {
	$revision = clone $revisions[$_GET["revision"]];
	unset($revisions);
} else {
	Auth::redirect("./?docs");
}

$parsedown = new Parsedown();

include_once "php/header.php"; ?>

<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>&source" class="top-right-button"><i class="fa fa-file-code-o"></i>Source</a>
<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>&diff" class="top-right-button"><i class="fa fa-files-o"></i>Diff</a>
<span class="top-right-button"><i class="fa fa-file-text-o"></i>Content</span>
<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>&revisions">Revisions</a> &gt; Details</h2>

<section class="markdown">
	<?php echo $parsedown->text($revision->getContents()); ?>
</section>

<?php include_once "php/footer.php";
die();