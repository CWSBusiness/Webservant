<?php

$project = Project::withID($_GET['id']);

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

try {
	$task = $milestone->getTask($_GET['toggletask']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
}

if ($task->isCompleted()) {
	$task->uncheck();
} else {
	$task->check();
}

$project->save();
Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());

die();