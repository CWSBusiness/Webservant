<?php

class ErrorCollector implements IteratorAggregate, Countable {

	private $level = 0;
	private $errors = array();

	const INFO = 1;
	const SUCCESS = 2;
	const WARNING = 4;
	const DANGER = 8;

	private static $levels = array(
		self::INFO => "info",
		self::SUCCESS => "success",
		self::WARNING => "warning",
		self::DANGER => "danger",

		JSON_HEX_AMP
	);

	public function addError($message, $level) {

		if (!is_int($level)) {
			try {
				$level = (int) $level;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for error level, got " . gettype($level) . " instead.");
			}
		}
		if ($level <= 0 || ($level & ($level - 1)) != 0) {        // using the constants above, the error level should be a power of two
			throw new InvalidArgumentException("Invalid error level supplied as argument.");
		}

		$count = array_push($this->errors, $message);
		// bitwise OR the error level. This way you can quickly tell the highest level set, but also if any given level is set
		$this->level = $this->level | $level;
		return $count - 1;

	}

//	public function removeError($index) {
//		if (array_key_exists($index, $this->errors)) {
//
//		} else {
//			throw new BadMethodCallException("Attempt to remove non-existent error from collector.");
//		}
//	}

	public function reset() {
		$this->errors = array();
	}

	public function hasErrors() {
		return (count($this->errors) > 0) ? true : false;
	}

	public function __toString() {
		if (count($this->errors) == 0) {
			return "";
		}

		$str = "<div class=\"status-box " . self::$levels[$this->level] . "\">" . PHP_EOL;
		if (count($this->errors) == 1) {
			$str .= $this->errors[0];
		} else {
			$str .= "<ul>" . PHP_EOL;
			foreach ($this->errors as $error) {
				$str .= "<li>" . $error . "</li>" . PHP_EOL;
			}
			$str .= "</ul>" . PHP_EOL;
		}
		$str .= "</div>" . PHP_EOL;

		return $str;
	}

	public function getIterator() {
		return new ArrayIterator($this->errors);
	}

	public function count() {
		return count($this->errors);
	}
}