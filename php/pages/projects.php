<?php

if (isset($_GET['add'])) {

	include "php/pages/projects/projects-add.php";

} else if (isset($_GET['viewall'])) {

	include "php/pages/projects/projects-all.php";

} else if (isset($_GET['id']) && !empty($_GET['id']) && ctype_digit($_GET['id'])) {

	if (isset($_GET['fileadd'])) {

		include "php/pages/projects/files-add.php";

	} else if (isset($_GET['file'])) {

		if (isset($_GET['download'])) {

			include "php/pages/projects/files-download.php";

		} else if (isset($_GET['edit'])) {

			include "php/pages/projects/files-edit.php";

		} else if (isset($_GET['delete'])) {

			include "php/pages/projects/files-delete.php";

		}

	} else if (isset($_GET['milestone'])) {

		if (isset($_GET['contractdownload'])) {

			include "php/pages/projects/milestone-contractdownload.php";

		} else if (isset($_GET['invoicedownload'])) {

			include "php/pages/projects/milestone-invoicedownload.php";

		} else if (isset($_GET['addtask'])) {

			include "php/pages/projects/task-add.php";

		} else if (isset($_GET['edittask'])) {

			include "php/pages/projects/task-edit.php";

		} else if (isset($_GET['deletetask'])) {

			include "php/pages/projects/task-delete.php";

		} else if (isset($_GET['assigntask'])) {

			include "php/pages/projects/task-assign.php";

		} else if (isset($_GET['toggletask'])) {

			include "php/pages/projects/task-checkuncheck.php";

		} else if (isset($_GET['togglepaid'])) {

			include "php/pages/projects/task-togglepaid.php";

		} else if (isset($_GET['add'])) {

			include "php/pages/projects/milestone-add.php";

		} if (isset($_GET['edit'])) {

			include "php/pages/projects/milestone-edit.php";

		} else if (isset($_GET['delete'])) {

			include "php/pages/projects/milestone-delete.php";

		} else if (!empty($_GET['milestone'])) {

			include "php/pages/projects/milestone-single.php";

		} else {

			include "php/pages/projects/milestone-all.php";

		}

	} if (isset($_GET['addmilestone'])) {

		include "php/pages/projects/milestone-add.php";

	} else if (isset($_GET['edit'])) {

		include "php/pages/projects/projects-edit.php";

	} else if (isset($_GET['delete'])) {

		include "php/pages/projects/projects-delete.php";

	} else if (isset($_GET["milestones"])) {

		include "php/pages/projects/milestone-all.php";

	} else if (isset($_GET['notes'])) {

		include "php/pages/projects/projects-notes.php";

	} else {

		include "php/pages/projects/projects-single.php";

	}

}

include "php/pages/projects/projects-overview.php";
die();