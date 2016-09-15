<?php

if (isset($_GET["add"])) {

	include "php/pages/docs/docs-add.php";

} else if (isset($_GET["id"])) {

	if (isset($_GET["revision"])) {

		if (isset($_GET["diff"])) {

			include "php/pages/docs/revisions-diff.php";

		} else if (isset($_GET["source"])) {

			include "php/pages/docs/revisions-source.php";

		} else {

			include "php/pages/docs/revisions-single.php";

		}

	} else if (isset($_GET["revisions"])) {

		include "php/pages/docs/revisions-all.php";

	} else if (isset($_GET["edit"])) {

		include "php/pages/docs/docs-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/docs/docs-delete.php";

	} else {

		include "php/pages/docs/docs-single.php";

	}

}

include "php/pages/docs/docs-overview.php";
die();