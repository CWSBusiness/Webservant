<?php if (isset($_GET['add'])) {
	
	include "php/pages/applications/applications-add.php";
	
} else if (isset($_GET['edit']) && !empty($_GET['edit']) && ctype_digit($_GET['edit'])) {
	
	include "php/pages/applications/applications-edit.php";
	
} else if (isset($_GET['id']) && !empty($_GET['id']) && ctype_digit($_GET['id'])) {

	if (isset($_GET['resumedownload'])) {

		include "php/pages/applications/applications-resumedownload.php";

	} else if (isset($_GET['review'])) {

		include "php/pages/applications/applications-review.php";

	} else if (isset($_GET['edit'])) {

		include "php/pages/applications/applications-edit.php";

	} else if (isset($_GET['delete'])) {

		include "php/pages/applications/applications-delete.php";

	} else if (isset($_GET['notes'])) {

		include "php/pages/applications/applications-notes.php";

	} else {

		include "php/pages/applications/applications-single.php";

	}

}

include "php/pages/applications/applications-overview.php";

die();