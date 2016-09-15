<?php

if (isset($_GET['add'])) {

	include "php/pages/notes/notes-add.php";

} else if (isset($_GET['id']) && ctype_digit($_GET['id'])) {

	if (isset($_GET['edit'])) {

		include "php/pages/notes/notes-edit.php";

	} else if (isset($_GET['delete'])) {

		include "php/pages/notes/notes-delete.php";

	}

}

include "php/pages/notes/notes-overview.php";
die();