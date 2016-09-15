<?php

try {
	$project = Project::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects");
}

$assets = $project->getAssetsDirectoryList();
$file   = urldecode(basename($_GET["file"]));
$path   = $assets->getDirectory() . DIRECTORY_SEPARATOR . $file;

if (!file_exists($path)) {
	Auth::redirect("./?projects");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($path);

// File type output
header("Content-type: " . $mimeType);
// Set as attachment and name the file
header('Content-Disposition: attachment; filename="' . $file . '"');
// Get the source file
readfile($path);