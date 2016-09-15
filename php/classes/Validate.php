<?php

// TODO make this set of validation and scrubbing functions extremely robust and fail-proof

/**
 * Validate class
 *
 * A static class containing all the various validation filters used by WebServant
 *
 * @author Brandon Bloch
 * @version 1.0
 */
class Validate {

	private static $initialized = false;

	private function __construct() {}
	private function __clone() {}

	private static function initialize() {
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;
	}

	/**
	 * Check whether a given input is either numeric or could be converted to a numeric type.
	 *
	 * @param string $value             The string to validate
	 *
	 * @return bool                     True if the string is numeric, and false otherwise
	 */
	public static function number($value) {
		self::initialize();
		if (!isset($value)) {
			return false;
		}
		return is_numeric($value);
	}

	public static function int($value) {
		self::initialize();
		if (!isset($value)) {
			return false;
		}
		return (is_int($value) || ctype_digit($value));
	}

	/**
	 * Check the validity of a given string as plain text.
	 * Allows all characters except those used in HTML (<tag> </tag>) and PHP (<?php ?>)
	 *
	 * @param string $string            The string to validate
	 * @param bool $allowEmpty          Whether to allow an empty string as valid input
	 *
	 * @return bool                     True if the string is valid plain text, and false otherwise
	 */
	public static function plainText($string, $allowEmpty = false) {
		self::initialize();
		if (!isset($string) || is_null($string)) {
			return false;
		}
		if (!is_string($string)) {
			return false;
		}
		if (trim($string) === "") {
			return ($allowEmpty) ? true : false;
		}
		return ($string === strip_tags($string));
	}

	/**
	 * Check the validity of a given string as HTML.
	 *
	 * @param string $string            The string to validate
	 * @param bool $allowEmpty          Whether to allow an empty string as valid input
	 *
	 * @return bool                     True if the string is valid HTML, and false otherwise
	 */
	public static function HTML($string, $allowEmpty = false) {
		self::initialize();
		if (!isset($string) || is_null($string)) {
			return false;
		}
		if (!is_string($string)) {
			return false;
		}
		if (trim($string) === "") {
			return ($allowEmpty) ? true : false;
		}
		if (stripos($string, "<?php") !== false || stripos($string, "?>" !== false)) {
			return false;
		}
		return true;
	}

	/**
	 * Check the validity of a given string as a name.
	 * Ensures the value is not empty, is plain text, and contains only letters
	 *
	 * @param string $name              The string to validate
	 *
	 * @return bool                     True if the string is a valid name, and false otherwise
	 */
	public static function name($name) {
		self::initialize();
		if (empty($name) || is_null($name)) {
			return false;
		}
		if (!is_string($name)) {
			return false;
		}
		if (trim($name) === "") {
			return false;
		}
		if ($name !== strip_tags($name)) {
			return false;
		}
		return (preg_match("/^[[:alpha:] '-]+$/", $name));
	}

	/**
	 * Check the validity of a given string as a netID.
	 *
	 * @param string $netID             The string to validate
	 *
	 * @return bool                     True if the string is a valid netID, and false otherwise
	 */
	public static function netID($netID) {
		self::initialize();
		if (empty($netID) || is_null($netID)) {
			return false;
		}
		if (!is_string($netID)) {
			return false;
		}
		if (trim($netID) === "") {
			return false;
		}
		if ($netID !== strip_tags($netID)) {
			return false;
		}
		return (preg_match("/^[[:digit:]]+[[:alpha:]]+[[:digit:]]*$/", $netID));
	}

	/**
	 * Check the validity of a given string as an email address.
	 *
	 * @param string $email             The string to validate
	 *
	 * @return bool                     True if the string is a valid email address, and false otherwise
	 */
	public static function email($email) {
		self::initialize();
		if (empty($email) || is_null($email)) {
			return false;
		}
		if (!is_string($email)) {
			return false;
		}
		if (trim($email) === "") {
			return false;
		}
		if ($email !== strip_tags($email)) {
			return false;
		}
		return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
	}

	/**
	 * Check the validity of a given string as a URL.
	 *
	 * @param string $url               The string to validate
	 *
	 * @return bool                     True if the string is a valid URL, and false otherwise
	 */
	public static function url($url) {
		self::initialize();
		if (empty($url) || is_null($url)) {
			return false;
		}
		if (!is_string($url)) {
			return false;
		}
		if (trim($url) === "") {
			return false;
		}
		if ($url !== strip_tags($url)) {
			return false;
		}
		return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
	}

	/**
	 * Check the validity of a given string as a phone number
	 *
	 * @param string $digits            The string to validate
	 *
	 * @return bool                     True if the string is a valid phone number, and false otherwise
	 */
	public static function phone($digits) {
		self::initialize();
		if (empty($digits) || is_null($digits)) {
			return false;
		}
		if (!is_string($digits)) {
			return false;
		}
		if (trim($digits) === "") {
			return false;
		}
		if ($digits !== strip_tags($digits)) {
			return false;
		}
		$digits = preg_replace("/[^0-9\\+]/i", "", $digits);
		return (preg_match("/^\\+?[0-9]{10,11}$/", $digits) === 1);
	}

	/**
	 * Check the validity of a given string as a username.
	 * The minimum length is specified in constants.php as USERNAME_MIN_LENGTH.
	 *
	 * @param string $username          The string to validate
	 *
	 * @return bool                     True if the string is a valid username, and false otherwise
	 */
	public static function username($username) {
		self::initialize();
		if (empty($username) || is_null($username)) {
			return false;
		}
		if (!is_string($username)) {
			return false;
		}
		if (trim($username) === "") {
			return false;
		}
		if ($username !== strip_tags($username)) {
			return false;
		}
		if (strlen($username) < USERNAME_MIN_LENGTH) {
			return false;
		}
		return (preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_\\-\\.]*$/", $username) === 1);
	}

	/**
	 * Check if the given value is a DateTime object, or an otherwise-valid date/time string
	 *
	 * @param mixed $date               The input to validate
	 *
	 * @return bool                     True if the input is a DateTime object or a valid date/time string, and false otherwise
	 */
	public static function date($date) {
		self::initialize();
		if ($date === false) {
			return false;
		}
		if ($date instanceof DateTime) {
			return true;
		}
		if (is_null($date) || !isset($date)) {
			return false;
		}
		if (trim($date) == "") {
			return false;
		}
		if ($date !== strip_tags($date)) {
			return false;
		}

		// TODO double-check this list of formats. It definitely isn't complete. Also align it with the set of formats Format::date() will accept
		$formats = array("d.m.Y", "d/m/Y", "Ymd", Format::DATE_FORMAT, Format::BIRTHDAY_FORMAT, Format::MYSQL_DATE_FORMAT, Format::MYSQL_TIMESTAMP_FORMAT);

		foreach ($formats as $format) {
			$output = DateTime::createFromFormat($format, $date);
			if ($output !== false) {
				return true;
			}
		}
		return false;

	}

	/**
	 * Check the validity of a given string as a password
	 *
	 * @param string $password          The string to validate
	 *
	 * @return bool                     True if the string is a valid password, and false otherwise
	 */
	public static function password($password) {
		self::initialize();
		if (empty($password) || is_null($password)) {
			return false;
		}
		if (!is_string($password)) {
			return false;
		}
		if (trim($password) === "") {
			return false;
		}
		if (strip_tags($password) !== $password) {
			return false;
		}
		if (strlen($password) < PASSWORD_MIN_LENGTH) {
			return false;
		}
		return true;
	}

}