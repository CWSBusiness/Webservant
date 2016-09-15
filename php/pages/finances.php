<?php

if (isset($_GET["budget"])) {

	include "php/pages/finances/budget.php";

} else if (isset($_GET["employee"])) {

	include "php/pages/finances/employee-history.php";

} if (isset($_GET["expensehistory"])) {

	include "php/pages/finances/expense-history.php";

} else if (isset($_GET["expenseadd"])) {

	include "php/pages/finances/expense-add.php";

} else if (isset($_GET["expense"])) {

	if (isset($_GET["receiptdownload"])) {

		include "php/pages/finances/expense-receiptdownload.php";

	} else if (isset($_GET["edit"])) {

		include "php/pages/finances/expense-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/finances/expense-delete.php";

	} else {

		include "php/pages/finances/expense-single.php";

	}

} else if (isset($_GET["incomehistory"])) {

	include "php/pages/finances/income-history.php";

} else if (isset($_GET["incomeadd"])) {

	include "php/pages/finances/income-add.php";

} else if (isset($_GET["income"])) {

	if (isset($_GET["edit"])) {

		include "php/pages/finances/income-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/finances/income-delete.php";

	} else {

		include "php/pages/finances/income-single.php";

	}

} else if (isset($_GET["payhistory"])) {

	include "php/pages/finances/pay-history.php";

} else if (isset($_GET["payadd"])) {

	include "php/pages/finances/pay-add.php";

} else if (isset($_GET["pay"])) {

	if (isset($_GET["edit"])) {

		include "php/pages/finances/pay-edit.php";

	} else if (isset($_GET["delete"])) {

		include "php/pages/finances/pay-delete.php";

	} else {

		include "php/pages/finances/pay-single.php";

	}

}

include "php/pages/finances/finances-overview.php";
die();