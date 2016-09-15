<?php

if (isset($_GET['add'])) {
	
	include "php/pages/users/users-add.php";

} else if (isset($_GET['edit']) && !empty($_GET['edit']) && ctype_digit($_GET['edit'])) {

	include "php/pages/users/users-edit.php";
	
} else if (isset($_GET['delete']) && !empty($_GET['delete']) && ctype_digit($_GET['delete'])) {
	
	include "php/pages/users/users-delete.php";
	
}

include "php/pages/users/users-overview.php";
die();