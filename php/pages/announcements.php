<?php if (isset($_GET["add"])) {

	include "php/pages/announcements/announcements-add.php";

} else if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {

	if (isset($_GET["add"])) {

		include "php/pages/announcements/announcements-add.php";

	} else if (isset($_GET["edit"])) {

		include "php/pages/announcements/announcements-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/announcements/announcements-delete.php";

	}

}

include "php/pages/announcements/announcements-all.php";

die();