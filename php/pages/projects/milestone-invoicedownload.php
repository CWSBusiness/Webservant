<?php

try {
	$project = Project::withID($_GET["id"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects");
}

try {
	$milestone = $project->getMilestone($_GET["milestone"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET["id"]);
}

if (!$milestone->getInvoiceFile()) {
	Auth::redirect("./?projects&id=" . $_GET["id"] . "&milestone=" . $_GET["milestone"]);
}

// File type output
header("Content-type: application/pdf");
// Set as attachment and name the file
header('Content-Disposition: attachment; filename="Invoice - ' . $project->getName() . '"');
// Get the source file
readfile($milestone->getInvoiceFile());