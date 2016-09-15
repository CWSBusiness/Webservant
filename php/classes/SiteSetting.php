<?php

class SiteSetting extends ActiveRecordAbstract {

	private $type;
	private $value;
	private $label;
	private $shortname;

	const CHECKBOX = 1;

	public static function settingTypes() {
		return array(
			self::CHECKBOX => "checkbox",
		);
	}

	public function __construct($type, $value, $shortname, $label) {
		$this->setType($type);
		$this->setValue($value);
		$this->setShortname($shortname);
		$this->setLabel($label);
	}

	/**
	 * @param int $id
	 *
	 * @return SiteSetting
	 * @throws Exception
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo  = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM settings WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new Exception("Unable to retrieve site setting by ID.");
			}
		}
		throw new InvalidArgumentException("Invalid site setting ID supplied to constructor.");
	}

	/**
	 * @param string $name
	 *
	 * @return SiteSetting
	 * @throws Exception
	 */
	public static function withShortname($name) {
		if (Validate::plainText($name)) {
			try {
				$pdo  = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM settings WHERE shortname = :shortname");
				$stmt->bindParam(":shortname", $name);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new Exception("Unable to retrieve site setting by ID.");
			}
		}
		throw new InvalidArgumentException("Invalid site setting shortname supplied to constructor.");
	}

	/**
	 * @param $row
	 *
	 * @return SiteSetting
	 */
	private static function withRow($row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Site setting primary ID missing from constructor.");
		}
		if (!isset($row["type"])) {
			throw new InvalidArgumentException("Site setting type label missing from constructor.");
		}
		if (!isset($row["shortname"])) {
			throw new InvalidArgumentException("Site setting shortname missing from constructor.");
		}
		if (!isset($row["label"])) {
			throw new InvalidArgumentException("Site setting label missing from constructor.");
		}
		if (!isset($row["value"])) {
			throw new InvalidArgumentException("Site setting value from constructor.");
		}

		$temp = new self($row["type"], $row["value"], $row["shortname"], $row["label"]);
		$temp->setPID($row["pid"]);

		return $temp;

	}

	/**
	 * @return SiteSetting[]
	 * @throws Exception
	 */
	public static function getAll() {
		$content = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from settings");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$content[] = self::withRow($row);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve site settings list.");
		}
		return $content;
	}

	protected function insert() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO settings (pid, type, shortname, label, value) VALUES (NULL, :type, :shortname, :label, :value)");
			$stmt->bindParam(":type", $this->type);
			$stmt->bindParam(":shortname", $this->shortname);
			$stmt->bindParam(":label", $this->label);
			$stmt->bindParam(":value", $this->value);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE settings SET type = :type, shortname = :shortname, label = :label, value = :value WHERE pid = :pid");
			$stmt->bindParam(":type", $this->type);
			$stmt->bindParam(":shortname", $this->shortname);
			$stmt->bindParam(":label", $this->label);
			$stmt->bindParam(":value", $this->value);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM settings WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		if (Validate::plainText($type)) {
			$this->type = $type;
		} else {
			throw new InvalidArgumentException("Invalid setting type supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	public function setLabel($text) {
		if (Validate::plainText($text)) {
			$this->label = $text;
		} else {
			throw new InvalidArgumentException("Invalid label text supplied as argument.");
		}
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		if (Validate::int($value)) {
			$this->value = $value;
		} else {
			throw new InvalidArgumentException("Invalid setting value supplied as argument.");
		}
	}

	public function getShortname() {
		return $this->shortname;
	}

	public function setShortname($name) {
		if (Validate::plainText($name)) {
			$this->shortname = $name;
		} else {
			throw new InvalidArgumentException("Invalid setting shortname supplied as argument.");
		}
	}

	public function __toString() {
		return $this->getLabel();
	}

}