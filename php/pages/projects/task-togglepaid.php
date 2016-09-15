<?php

$project = Project::withID($_GET['id']);

try {
	$milestone = $project->getMilestone($_GET['milestone']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id']);
}

try {
	$task = $milestone->getTask($_GET['togglepaid']);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());
}

//var_dump($task);

$task->setPaidStatus(!$task->getPaidStatus());

//var_dump($task);

$project->save();
Auth::redirect("./?projects&id=" . $_GET['id'] . "&milestone=" . $milestone->getNameForParameter());

die();