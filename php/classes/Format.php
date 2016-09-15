<?php

/**
 * Format class
 *
 * A static class containing all the various format filters used by WebServant
 *
 * @author Brandon Bloch
 * @version 1.0
 */
class Format {
	
	private static $initialized = false;

	private function __construct() {}
	private function __clone() {}

	const DATE_FORMAT = "n/j/Y";
	const TIME_FORMAT = "g:i A";
	const DATETIME_FORMAT = "n/j/Y \\a\\t g:i A";
	const DATEPICKER_FORMAT = "Y/m/d H:i";
	const BIRTHDAY_FORMAT = "F j, Y";
	const MYSQL_DATE_FORMAT = "Y-m-d";
	const MYSQL_TIMESTAMP_FORMAT = "Y-m-d H:i:s";

	private static function initialize() {
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;
	}

	/**
	 * Append an ordinal suffix (the "th" in "nth") to an integer
	 *
	 * @param int $int          The integer to be displayed
	 *
	 * @return string           An ordinal suffix appended to the integer
	 */
	public static function ordinal($int) {
		if (is_int($int) || ctype_digit($int)) {
			$mod100 = abs($int) % 100;
			$ends   = array("th", "st", "nd", "rd", "th", "th", "th", "th", "th", "th");
			if (($mod100) > 10 && ($mod100) < 14) {
				return $int . "th";
			}
			return $int . $ends[abs($int) % 10];
		}
		throw new InvalidArgumentException("Expected integer input to format, got " . gettype($int) . " instead.");
	}

	/**
	 * Truncate a string to a specified number of characters, appending an ellipsis if necessary
	 *
	 * @param string $string    The string to format
	 * @param int $maxChars     The maximum number of characters allowed before truncation
	 *
	 * @return string           The truncated string, with an ellipsis if any characters were cut off
	 */
	public static function truncate($string, $maxChars = 50) {
		return (strlen($string) > $maxChars + 3) ? substr($string, 0, $maxChars) . "..." : $string;
	}

	/**
	 * Format a variety of date/time types with a specific date format
	 *
	 * @param mixed $ts         A DateTime object, UNIX timestamp, or date/time string
	 * @param string $format    The date format to use (defaults to DATE_FORMAT)
	 *
	 * @return bool|string      The formatted date
	 */
	public static function date($ts, $format = Format::DATE_FORMAT) {
		self::initialize();

		if ($ts instanceof DateTime) {
			$blank = new DateTime(date("Y-m-d", 0));
			if ($ts == $blank) {
				return "";
			}
			$blank->setTime(date("H", time()), date("i", time()), date("s", time()));
			if ($ts == $blank) {
				return "";
			}
			return $ts->format($format);
		}

		if (empty($ts) || trim($ts) == "") {
			return "";
		}

		if (!ctype_digit($ts)) {
			$ts = strtotime($ts);
		}

		return date($format, $ts);
		
	}

	/**
	 * Format a variety of date/time types as a relative date/time string.
	 * Examples include "3 hours ago", "Just now", "Last month", or "May 2013".
	 * Works for both past dates/times (as in examples above) and future dates/times
	 *
	 * @param mixed $ts         A DateTime object, UNIX timestamp, or date/time string
	 *
	 * @return bool|string      The relative date/time string
	 */
	public static function relativeTime($ts) {
		self::initialize();

		// convert the input to a UNIX timestamp
		if ($ts instanceof DateTime) {
			$ts = $ts->getTimestamp();
		} else if(!ctype_digit($ts)) {
        	$ts = strtotime($ts);
		}

		$diff = time() - $ts;

		if($diff == 0) {

        	return 'Now';

		} else if($diff > 0) {

			$day_diff = floor($diff / 86400);
			if($day_diff == 0) {
				if($diff < 60) return 'Just now';
				if($diff < 120) return '1 minute ago';
				if($diff < 3600) return floor($diff / 60) . ' minutes ago';
				if($diff < 7200) return '1 hour ago';
				if($diff < 86400) return floor($diff / 3600) . ' hours ago';
			}
			if($day_diff == 1) return 'Yesterday';
			if($day_diff < 7) return $day_diff . ' days ago';
			if($day_diff == 7) return '1 week ago';
			if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
			if($day_diff < 60) return 'Last month';

			return date('F Y', $ts);

		} else {

			$diff = abs($diff);
			$day_diff = floor($diff / 86400);
			if($day_diff == 0) {
				if($diff < 120) return 'In a minute';
				if($diff < 3600) return 'In ' . floor($diff / 60) . ' minutes';
				if($diff < 7200) return 'In an hour';
				if($diff < 86400) return 'In ' . floor($diff / 3600) . ' hours';
			}
			if($day_diff == 1) return 'Tomorrow';
			if($day_diff < 4) return date('l', $ts);
			if($day_diff < 7 + (7 - date('w'))) return 'Next week';
			if(ceil($day_diff / 7) < 4) return 'In ' . ceil($day_diff / 7) . ' weeks';
        	if(date('n', $ts) == date('n') + 1) return 'Next month';

        	return date('F Y', $ts);

    	}
	}

	/**
	 * Format a phone number for display purposes.
	 * Puts a valid phone number (as determined by Validate::phone) in the format (xxx) xxx-xxxx.
	 * Works with optional country code for North America (+, 1, or +1).
	 *
	 * @param string $digits        The phone number to format
	 *
	 * @return string               The formatted phone number
	 */
	public static function phone($digits) {
		$plusPrefix = false;
		$onePrefix = false;
		$digits = preg_replace("/[^0-9\\+]/i", "", $digits);
		if (strlen($digits) < 10 || strlen($digits) > 12) {
			return $digits;
		}
		if ($digits[0] == "+") {
			$plusPrefix = true;
			$digits = substr($digits, 1);
		}
		if (strlen($digits) == 11 && $digits[0] == "1") {
			$onePrefix = true;
			$digits = substr($digits, 1);
		}
		$phoneStr = "(" . substr($digits, 0, 3) . ") " . substr($digits, 3, 3) . "-" . substr($digits, 6, 4);
		if ($plusPrefix) {
			$phoneStr = "+1 " . $phoneStr;
		} else if ($onePrefix) {
			$phoneStr = "1 " . $phoneStr;
		}

		return $phoneStr;
	}

	/**
	 * Format a phone number for hyperlinking purposes.
	 * Puts a valid phone number (as determined by Validate::phone) in the format xxxxxxxxxx.
	 * Works with optional country code (+, 1, or +1).
	 *
	 * @param string $digits        The phone number to format
	 *
	 * @return string               The formatted phone number
	 */
	public static function tel($digits) {
		return $digits = preg_replace("/[^0-9\\+]/i", "", $digits);
	}

	/**
	 * Format a filesize for display.
	 * Takes a file size, specified in bytes, as an integer.
	 *
	 * @param int $size         The filesize to format
	 * @param int $precision    The number of decimal places to round to (defaults to 2)
	 *
	 * @return string           The formatted filesize
	 */
	public static function bytes($size, $precision = 2) {
		try {
			$size = (int) $size;
		} catch (Exception $e) {
			throw new InvalidArgumentException("Expected integer filesize, got " . gettype($size) . " instead.");
		}
		$prefixes = array("bytes", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		$magnitude = 0;
		while ($size >= 1024 || $size <= -1024) {
			$size = $size / 1024;
			$magnitude ++;
		}
		if (!array_key_exists($magnitude, $prefixes)) {
			throw new OutOfRangeException("The filesize is too large to format.");
		}
		return round($size, $precision) . " " . $prefixes[$magnitude];
	}
	
}
