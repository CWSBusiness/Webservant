<?php

class RGBA {

	private $r;
	private $g;
	private $b;
	private $a;

	// private constructor, called by the different "overloaded" public constructors below
	private function __construct($r, $g, $b, $a) {
		$this->setRed($r);
		$this->setGreen($g);
		$this->setBlue($g);
		$this->setAlpha($a);
	}

	// public constructors

	/**
	 * @param string $hex
	 *
	 * @return RGBA
	 */
	public static function withHex($hex) {
		if (substr($hex, 0, 1) == "#") {
			$hex = substr($hex, 1);
		}
		if (strlen($hex) == 3) {
			$rStr = substr($hex, 0, 1);
			$gStr = substr($hex, 1, 1);
			$bStr = substr($hex, 2, 1);
			$rStr = $rStr . $rStr;
			$gStr = $gStr . $gStr;
			$bStr = $bStr . $bStr;
		} else if (strlen($hex) == 6) {
			$rStr = substr($hex, 0, 2);
			$gStr = substr($hex, 2, 2);
			$bStr = substr($hex, 4, 2);
		} else {
			throw new InvalidArgumentException("Improperly formatted hex string supplied as argument.");
		}
		$r = hexdec($rStr);
		$g = hexdec($gStr);
		$b = hexdec($bStr);
		// $r = base_convert($rStr, 16, 10);
		// $g = base_convert($gStr, 16, 10);
		// $b = base_convert($bStr, 16, 10);
		return new self($r, $g, $b, 1);
	}

	/**
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return RGBA
	 */
	public static function withRGB($r, $g, $b) {
		return new self($r, $g, $b, 1);
	}

	/**
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 * @param float $a
	 *
	 * @return RGBA
	 */
	public static function withRGBA($r, $g, $b, $a) {
		return new self($r, $g, $b, $a);
	}

	// predefined color constant constructors

	public static function black() {
		return new self(0, 0, 0, 1);
	}
	public static function white() {
		return new self(255, 255, 255, 1);
	}
	public static function transparent() {
		return new self(0, 0, 0, 0);
	}
	public static function red() {
		return new self(255, 0, 0, 1);
	}
	public static function green() {
		return new self(0, 255, 0, 1);
	}
	public static function blue() {
		return new self(0, 0, 255, 1);
	}
	public static function cyan() {
		return new self(0, 255, 255, 1);
	}
	public static function magenta() {
		return new self(255, 0, 255, 1);
	}
	public static function yellow() {
		return new self(255, 255, 0, 1);
	}

	// Color getters/setters

	/**
	 * @return int
	 */
	public function getRed() {
		return $this->r;
	}

	/**
	 * @param int $r
	 */
	public function setRed($r) {
		if (is_float($r)) {
			$r = (int) $r;
		}
		if (!is_int($r)) {
			throw new InvalidArgumentException("Expected int for R value, got " . gettype($r) . " instead.");
		}
		if ($r > 255) {
			$r = 255;
		} else if ($r < 0) {
			$r = 0;
		}
		$this->r = $r;
	}

	/**
	 * @return int
	 */
	public function getGreen() {
		return $this->g;
	}

	/**
	 * @param int $g
	 */
	public function setGreen($g) {
		if (is_float($g)) {
			$g = (int) $g;
		}
		if (!is_int($g)) {
			throw new InvalidArgumentException("Expected int for G value, got " . gettype($g) . " instead.");
		}
		if ($g > 255) {
			$g = 255;
		} else if ($g < 0) {
			$g = 0;
		}
		$this->g = $g;
	}

	/**
	 * @return int
	 */
	public function getBlue() {
		return $this->b;
	}

	/**
	 * @param int $b
	 */
	public function setBlue($b) {
		if (is_float($b)) {
			$b = (int) $b;
		}
		if (!is_int($b)) {
			throw new InvalidArgumentException("Expected int for B value, got " . gettype($b) . " instead.");
		}
		if ($b > 255) {
			$b = 255;
		} else if ($b < 0) {
			$b = 0;
		}
		$this->b = $b;
	}

	/**
	 * @return int|float
	 */
	public function getAlpha() {
		if ($this->a == 0) {
			return 0;
		} else if ($this->a == 1) {
			return 1;
		} else {
			return $this->a;
		}
	}

	/**
	 * @param float $a
	 */
	public function setAlpha($a) {
		if (!is_float($a) && !is_int($a)) {
			throw new InvalidArgumentException("Expected float for A value, got " . gettype($a) . " instead.");
		}
		if ($a > 1) {
			$a = 1;
		} else if ($a < 0) {
			$a = 0;
		}
		$this->a = $a;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		if ($this->a == 1) {
			return "rgb(" . $this->r . ", " . $this->g . ", " . $this->b . ")";
		}
		if ($this->a == 0) {
			$a = 0;
		} else {
			$a = $this->a;
		}
		return "rgba(" . $this->r . ", " . $this->g . ", " . $this->b . ", " . $a . ")";
	}

	public function getBrightness() {
		return sqrt((0.299 * $this->r * $this->r) + (0.587 * $this->g * $this->g) + (0.114 * $this->b * $this->b));
	}

	public function blend(RGBA $other) {
		$this->setRed(($this->r + $other->r) / 2);
		$this->setGreen(($this->g + $other->g) / 2);
		$this->setBlue(($this->b + $other->b) / 2);
		$this->setAlpha(($this->a + $other->a) / 2);
	}

	public function lighten($amount) {
		if (!is_int($amount)) {
			throw new InvalidArgumentException("Expected int for lighten amount, got " . gettype($amount) . " instead.");
		}
		$this->setRed($this->r + $amount);
		$this->setGreen($this->g + $amount);
		$this->setBlue($this->b + $amount);
	}

	public function darken($amount) {
		if (!is_int($amount)) {
			throw new InvalidArgumentException("Expected int for darken amount, got " . gettype($amount) . " instead.");
		}
		$this->setRed($this->r - $amount);
		$this->setGreen($this->g - $amount);
		$this->setBlue($this->b - $amount);
	}

	public function __clone() {
		return new self($this->r, $this->g, $this->b, $this->a);
	}

}