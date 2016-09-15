<?php

try {
	$app = JobApp::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?applications");
}

if (!$app->resumeIsFile()) {
	Auth::redirect("./?applications&id=" . $app->getPID());
}

// File type output
header("Content-type: application/pdf");
// Set as attachment and name the file
header('Content-Disposition: attachment; filename="' . $app->getLastName() . ', ' . $app->getFirstName() . '"');
// Get the source file
readfile($app->getResumeFile());