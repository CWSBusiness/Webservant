<?php

if (isset($_GET['add'])) {

	include "php/pages/employees/employees-add.php";

} else if (isset($_GET['id']) && !empty($_GET['id']) && ctype_digit($_GET['id'])) {

	if (isset($_GET['edit'])) {

		include "php/pages/employees/employees-edit.php";

	} else if (isset($_GET['delete'])) {

		include "php/pages/employees/employees-delete.php";

	} else if (isset($_GET['profilepic'])) {

		include "php/pages/employees/employees-profilepic.php";

	} else if (isset($_GET['notes'])) {

		include "php/pages/employees/employees-notes.php";

	} else {

		include "php/pages/employees/employees-single.php";

	}

}

include "php/pages/employees/employees-overview.php";
die();