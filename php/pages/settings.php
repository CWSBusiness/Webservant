<?php

if (isset($_GET['changepass'])) {
	
	include "php/pages/settings/settings-changepass.php";
	
} else if (isset($_GET['username'])) {
	
	include "php/pages/settings/settings-username.php";

} else if (isset($_GET['profilepic'])) {

	include "php/pages/settings/settings-profilepic.php";

}

include "php/pages/settings/settings-overview.php";
die();
