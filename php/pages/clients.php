<?php

if (isset($_GET["add"])) {

	include "php/pages/clients/clients-add.php";

} else if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {

	if (isset($_GET["edit"])) {

		include "php/pages/clients/clients-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/clients/clients-delete.php";

	} else if (isset($_GET["notes"])) {

		include "php/pages/clients/clients-notes.php";

	} else {

		include "php/pages/clients/clients-single.php";

	}

} else if (isset($_GET["company"]) && ctype_digit($_GET["company"])) {

	if (isset($_GET["edit"])) {

		include "php/pages/clients/clients-companyedit.php";

	} else {

		include "php/pages/clients/clients-company.php";

	}
}

include "php/pages/clients/clients-overview.php";
die();