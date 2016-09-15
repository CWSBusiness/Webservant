<?php

class Note extends ActiveRecordAbstract {

	private $title;
	private $contents;
	private $created_date;
	private $modified_date;
	private $creator;

	/**
	 * @param int $authorID
	 * @param string $title
	 * @param string $contents
	 * @param int|DateTime $creationTime
	 * @param int|DateTime $modificationTime
	 */
	public function __construct($authorID, $title, $contents = "", $creationTime = 0, $modificationTime = 0) {
		$this->setAuthorID($authorID);
		$this->setTitle($title);
		$this->setContents($contents);
		if ($creationTime) {
			$this->setCreationTime($creationTime);
		} else {
			$this->setCreationTime(new DateTime());
		}
		if ($modificationTime) {
			$this->setModificationTime($modificationTime);
		} else {
			$this->setModificationTime(new DateTime());
		}
	}

	/**
	 * @param User $user
	 *
	 * @return Note[]
	 */
	public static function getUserNotes(User $user) {
		if (is_null($user)) {
			throw new OutOfBoundsException("Null reference supplied to constructor.");
		}
		$noteObjs = array();
		$pid = $user->getPID();

		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("SELECT * FROM notes WHERE created_by = :pid ORDER BY modified_date DESC");
			$stmt->bindParam(":pid", $pid);
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $note) {
				$noteObjs[] = Note::withRow($note);
			}
			return $noteObjs;
		} catch (PDOException $e) {
			throw new InvalidArgumentException("Unable to retrieve user notes.");
		}
	}

	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo  = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM notes WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Non-existent note ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Invalid note ID supplied to constructor.");
	}

	private static function withRow(array $row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Note primary ID missing from constructor.");
		}
		if (!isset($row["created_by"])) {
			throw new InvalidArgumentException("Author ID missing from constructor.");
		}
		if (!isset($row["title"])) {
			throw new InvalidArgumentException("Note title missing from constructor.");
		}
		if (!isset($row["contents"])) {
			$row["contents"] = "";
		}
		if (!isset($row["created_date"])) {
			throw new InvalidArgumentException("Note creation timestamp missing from constructor.");
		}
		if (!isset($row["modified_date"])) {
			throw new InvalidArgumentException("Note modification timestamp missing from constructor.");
		}

		$temp = new self($row["created_by"], $row["title"], $row["contents"], $row["created_date"], $row["modified_date"]);
		$temp->setPID($row["pid"]);

		return $temp;
	}

	// Insert the note into the database notes table as a new record
	protected function insert() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO notes (pid, title, contents, created_date, modified_date, created_by) VALUES (NULL, :title, :contents, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :creator)");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":contents", $this->contents);
			$stmt->bindParam(":creator", $this->creator);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	// Update the note in the database notes table (must be an existing record)
	protected function update() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE notes SET title = :title, contents = :contents WHERE pid = :pid");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":contents", $this->contents);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Delete the note from the database notes table (must be an existing record)
	 * @return bool     Returns true if successful, or false if the operation fails.
	 */
	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM notes WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		if (Validate::plainText($title)) {
			$this->title = $title;
		} else {
			throw new InvalidArgumentException("Invalid note title supplied as argument.");
		}
	}

	public function getContents() {
		return $this->contents;
	}

	public function setContents($contents) {
		if (Validate::HTML($contents, true)) {
			$this->contents = $contents;
		} else {
			throw new InvalidArgumentException("Invalid note contents supplied as argument.");
		}
	}

	public function getCreationTime() {
		return clone $this->created_date;
	}

	private function setCreationTime($date) {
		if ($date instanceof DateTime) {
			$this->created_date = clone $date;
		} else if ($date == false) {
			$this->created_date = new DateTime();
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid creation date supplied as argument.");
				}
				$this->created_date = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid creation date supplied as argument.");
			}
		}
	}

	public function getModificationTime() {
		return clone $this->modified_date;
	}

	private function setModificationTime($date) {
		if ($date instanceof DateTime) {
			$this->modified_date = clone $date;
		} else if (!isset($date) || !$date) {
			$this->modified_date = $this->created_date;
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid modification date supplied as argument.");
				}
				$this->modified_date = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid modification date supplied as argument.");
			}
		}
	}

	public function getAuthorID() {
		return $this->creator;
	}

	public function setAuthorID($id) {
		if (is_int($id)) {
			$this->creator = $id;
		} else {
			try {
				$id = (int) $id;
				$this->creator = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for author ID, got " . gettype($id) . " instead.");
			}
		}
	}

	public function __toString() {
		return $this->getTitle();
	}
	
}
