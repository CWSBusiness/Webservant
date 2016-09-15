<?php

$doc = Doc::withID($_GET["id"]);
$revisions = $doc->getRevisionHistory();
if (array_key_exists($_GET["revision"], $revisions)) {
	$revision = clone $revisions[$_GET["revision"]];
	unset($revisions);
} else {
	Auth::redirect("./?docs");
}

include_once "php/header.php"; ?>

	<span class="top-right-button"><i class="fa fa-file-code-o"></i>Source</span>
	<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>&diff" class="top-right-button"><i class="fa fa-files-o"></i>Diff</a>
	<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>" class="top-right-button"><i class="fa fa-file-text-o"></i>Content</a>
	<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>&revisions">Revisions</a> &gt; Details</h2>

	<section>
		<label for="docContents" class="label-hide">Contents</label>
		<textarea name="docContents" id="docContents"><?php echo $revision->getContents(); ?></textarea>
	</section>

<?php include_once "php/footer.php";
die();