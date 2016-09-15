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

$a = explode(PHP_EOL, $revision->getContents());
$b = explode(PHP_EOL, $doc->getContents());

//require_once "php/classes/Diff.php";
require_once "php/classes/Diff/Renderer/Html/Inline.php";
require_once "php/classes/Diff/Renderer/Html/SideBySide.php";

$diff = new Diff($a, $b);
$diffRenderer = new Diff_Renderer_Html_Inline();

include_once "php/header.php"; ?>

<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>&source" class="top-right-button"><i class="fa fa-file-code-o"></i>Source</a>
<span class="top-right-button"><i class="fa fa-files-o"></i>Diff</span>
<a href="./?docs&id=<?php echo $doc->getPID(); ?>&revision=<?php echo $_GET["revision"]; ?>" class="top-right-button"><i class="fa fa-file-text-o"></i>Content</a>
<h2 class="breadcrumbs"><i class="fa fa-book"></i> <a href="./?docs">Docs</a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>"><?php echo $doc; ?></a> &gt; <a href="./?docs&id=<?php echo $doc->getPID(); ?>&revisions">Revisions</a> &gt; Details</h2>

<section class="markdown">
	<?php $output = $diff->render($diffRenderer);
	if (trim($output) == "") { ?>
		<p>No content differences to display.</p>
	<?php } else {
		echo $output;
	} ?>
</section>

<?php include_once "php/footer.php";
die();