<?php

/**
 * Class Money
 */
class Money {

	private $value;

	const DECIMAL_PLACES = 2;

	public function __construct($string) {

		if (!isset($string) || $string == "") {
			throw new InvalidArgumentException("Invalid amount supplied as argument.");
		}

		if (is_int($string)) {
			$string = (string) $string;                     // cast the int to a string
		} else if (is_float($string)) {
			$string = number_format($string, self::DECIMAL_PLACES, ".", "");   // $value, decimal places, decimal point, thousands separator
		} else if (substr($string, 0, 1) == "$") {
			$string = substr($string, 1);                   // remove the dollar sign
		}

		if (!is_numeric($string)) {
			throw new InvalidArgumentException("Invalid amount supplied as argument.");
		}

		try {
			$this->value = bcadd($string, "0", self::DECIMAL_PLACES);
		} catch (Exception $e) {
			throw new InvalidArgumentException("Invalid amount supplied as argument.");
		}

	}

	/**
	 * Add another money value to the current value
	 * @param Money $other          The other money value
	 */
	public function add(Money $other) {
		if (is_null($other)) {
			throw new InvalidArgumentException("Null reference supplied to method.");
		}
		$this->value = bcadd($this->value, $other->value, self::DECIMAL_PLACES);
	}

	/**
	 * Subtract another money value from the current value
	 * @param Money $other          The other money value
	 */
	public function subtract(Money $other) {
		if (is_null($other)) {
			throw new InvalidArgumentException("Null reference supplied to method.");
		}
		$this->value = bcsub($this->value, $other->value, self::DECIMAL_PLACES);
	}

	/**
	 * Multiply the current money value by another
	 * @param Money $other          The other money value
	 */
	public function multiply(Money $other) {
		if (is_null($other)) {
			throw new InvalidArgumentException("Null reference supplied to method.");
		}
		$this->value = bcmul($this->value, $other->value, self::DECIMAL_PLACES);
	}

	/**
	 * Scale (multiply) the current money value by a specified factor
	 * @param int|float $scale      The scale factor
	 */
	public function scale($scale) {
		if (is_null($scale)) {
			throw new InvalidArgumentException("Null reference supplied to method.");
		}
		if (is_numeric($scale)) {
			$this->multiply(new Money($scale));
		} else {
			throw new InvalidArgumentException("Invalid scale factor supplied as argument.");
		}
	}

	/**
	 * @param int|float $divisor    The number to divide by
	 *
	 * @return string               The remainder
	 */
	public function divide($divisor) {
		if (is_int($divisor) || ctype_digit($divisor)) {
			$divisor = (string) $divisor;
			$remainder = bcmod($this->value, $divisor);
			$this->value = bcdiv($this->value, $divisor, self::DECIMAL_PLACES);
			return $remainder;
		} else {
			throw new InvalidArgumentException("Invalid divisor supplied as argument.");
		}
	}

	/**
	 * Get the result of a comparison of two money values
	 * @param Money $other      The other money value
	 * @return int              Returns 1 if the current value is greater than $other, -1 if the current value is lesser, and 0 if the values are equal
	 */
	public function compare(Money $other) {
		if (is_null($other)) {
			throw new InvalidArgumentException("Null reference supplied to method.");
		}
		return bccomp($this->value, $other->value, self::DECIMAL_PLACES);
	}

	public function isZero() {
		return (bccomp($this->value, "0", self::DECIMAL_PLACES) == 0);
	}

	public function isNegative() {
		return (bccomp($this->value, "0", self::DECIMAL_PLACES) < 0);
	}

	public function __clone() {
		return new self($this->value);
	}

	public function __toString() {
//		if (substr($this->value, -3, 3) == ".00") {
//			return "$" . substr($this->value, 0, -3);
//		}
		if (bccomp($this->value, "0", self::DECIMAL_PLACES) < 0) {
			return "-$" . substr($this->value, 1);
		} else {
			return "$" . $this->value;
		}
	}

}