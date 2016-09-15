<?php

class SiteContent extends ActiveRecordAbstract {

	private $description;
	private $category;
	private $contents;

	public function __construct($category, $description, $contents = "") {
		$this->setCategory($category);
		$this->setDescription($description);
		$this->setContents($contents);
	}

	/**
	 * @param $id
	 *
	 * @return SiteContent
	 * @throws Exception
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo  = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM site WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new Exception("Unable to retrieve site content by ID.");
			}
		}
		throw new InvalidArgumentException("Invalid site content ID supplied to constructor.");
	}

	/**
	 * @param $label
	 *
	 * @return SiteContent|SiteContent[]
	 */
	public static function withCategory($label) {
		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("SELECT * FROM site WHERE category = :category");
			$stmt->bindParam(":category", $label);
			$stmt->execute();
			$rows = $stmt->rowCount();
			if ($rows == 1) {
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} else if ($rows > 1) {
				$results = $stmt->fetchAll();
				if ($results === false) {
					throw new PDOException();
				}
				$contents = array();
				foreach ($results as $row) {
					$contents[] = self::withRow($row);
				}
				return $contents;
			} else {
				throw new Exception("Invalid site content category supplied as argument.");
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve site content by category.");
		}
	}

	/**
	 * @param $row
	 *
	 * @return SiteContent
	 */
	private static function withRow($row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Site content primary ID missing from constructor.");
		}
		if (!isset($row["category"])) {
			throw new InvalidArgumentException("Site content category label missing from constructor.");
		}
		if (!isset($row["description"])) {
			throw new InvalidArgumentException("Site content description missing from constructor.");
		}
		if (!isset($row["contents"])) {
			throw new InvalidArgumentException("Site contents missing from constructor.");
		}

		$temp = new self($row["category"], $row["description"], $row["contents"]);
		$temp->setPID($row["pid"]);

		return $temp;

	}

	/**
	 * @return SiteContent[]
	 * @throws Exception
	 */
	public static function getAll() {
		$content = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from site");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$content[] = self::withRow($row);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve site content list.");
		}
		return $content;
	}

	protected function insert() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO site (pid, description, contents) VALUES (NULL, :description, :contents)");
			$stmt->bindParam(":description", $this->description);
			$stmt->bindParam(":contents", $this->contents);
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
			$stmt = $pdo->prepare("UPDATE site SET description = :description, contents = :contents WHERE pid = :pid");
			$stmt->bindParam(":description", $this->description);
			$stmt->bindParam(":contents", $this->contents);
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
			$stmt = $pdo->prepare("DELETE FROM site WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getCategory() {
		return $this->category;
	}

	public function setCategory($label) {
		if (Validate::plainText($label)) {
			$this->category = $label;
		} else {
			throw new InvalidArgumentException("Invalid category label supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($text) {
		if (Validate::plainText($text)) {
			$this->description = $text;
		} else {
			throw new InvalidArgumentException("Invalid description text supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getContents() {
		return $this->contents;
	}

	public function setContents($text) {
		if (Validate::HTML($text, true)) {
			$this->contents = $text;
		} else {
			throw new InvalidArgumentException("Invalid contents supplied as argument.");
		}
	}

	public function __toString() {
		return $this->getContents();
	}

}