<?php

/**
 * Class Doc
 *
 * A class representing an internal document.
 *
 * Documents can be written in HTML or plain text, and are parsed by Markdown.
 *
 * Revisions are tracked, with their author and the timestamp of modification.
 * The number of most recent revisions to save is specified by MAX_REVISION_ENTRIES.
 */
class Doc extends ActiveRecordAbstract {

	private $title;
	private $contents;
	private $lastEditorID;
	private $lastModifiedTimestamp;

	const MAX_REVISION_ENTRIES = 5;

	/**
	 * Construct a new document
	 *
	 * @param            $title
	 * @param            $editor
	 * @param string     $contents
	 * @param bool|false $lastModifiedTimestamp
	 */
	public function __construct($title, $editor, $contents = "", $lastModifiedTimestamp = false) {
		$this->setTitle($title);
		$this->setLastEditorID($editor);
		$this->setContents($contents);
		if ($lastModifiedTimestamp) {
			$this->setModificationTime($lastModifiedTimestamp);
		}
	}

	/**
	 * @param $id
	 *
	 * @return Doc
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT pid, title, last_edited_by, last_modified, contents from docs WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid document ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for document ID, got " . gettype($id) . " instead.");
	}

	/**
	 * @param array $row
	 *
	 * @return Doc
	 */
	private static function withRow(array $row) {
		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Document primary ID missing from constructor.");
		}
		if (!isset($row["title"])) {
			throw new InvalidArgumentException("Document title missing from constructor.");
		}
		if (!isset($row["last_edited_by"])) {
			throw new InvalidArgumentException("Last document editor missing from constructor.");
		}
		if (!isset($row["last_modified"])) {
			throw new InvalidArgumentException("Document modification date missing from constructor.");
		}
		if (!isset($row["contents"])) {
			throw new InvalidArgumentException("Document contents missing from constructor.");
		}
		$temp = new self($row["title"], $row["last_edited_by"], $row["contents"]);
		$temp->setPID($row["pid"]);
		$temp->setModificationTime($row["last_modified"]);
		return $temp;
	}

	/**
	 * @return Doc[]
	 */
	public static function getAllWithoutContents() {
		$docObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT pid, title, last_edited_by, last_modified from docs ORDER BY last_modified DESC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $doc) {
				$doc["contents"] = "";
				$docObjs[] = self::withRow($doc);
			}
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve documents list.");
		}
		return $docObjs;
	}

	protected function insert() {
		try {
			$pdo = DB::getHandle();
			$me = Employee::current()->getPID();
			$this->setLastEditorID($me);
			$now = new DateTime();
			$timestamp = Format::date($now, Format::MYSQL_TIMESTAMP_FORMAT);
			$stmt = $pdo->prepare("INSERT INTO docs (pid, title, last_edited_by, last_modified, contents) VALUES (NULL, :title, :editor, :modificationtime, :contents)");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":editor", $me);
			$stmt->bindParam(":modificationtime", $timestamp);
			$stmt->bindParam(":contents", $this->contents);
			$stmt->execute();
			$result = $pdo->lastInsertId();
			$this->pid = $result;
			$this->setModificationTime($now);
			$this->setRevisionHistory(array());
			return $result;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		try {
			$pdo = DB::getHandle();
			$me = Employee::current()->getPID();
			$this->setLastEditorID($me);
			$now = new DateTime();
			$timestamp = Format::date($now, Format::MYSQL_TIMESTAMP_FORMAT);
			$stmt = $pdo->prepare("UPDATE docs SET title = :title, last_edited_by = :editor, last_modified = :modificationtime, contents = :contents WHERE pid = :pid");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":editor", $me);
			$stmt->bindParam(":modificationtime", $timestamp);
			$stmt->bindParam(":contents", $this->contents);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$this->setModificationTime($now);
			$revisions = $this->getRevisionHistory();
			while (count($revisions) >= self::MAX_REVISION_ENTRIES) {
				array_pop($revisions);
			}
			array_unshift($revisions, $this);
			$this->setRevisionHistory($revisions);
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
			$stmt = $pdo->prepare("DELETE FROM docs WHERE pid = :pid");
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
			throw new InvalidArgumentException("Invalid title supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getContents() {
		return $this->contents;
	}

	public function setContents($contents) {
		if (is_string($contents)) {
			$this->contents = $contents;
		} else {
			throw new InvalidArgumentException("Invalid contents supplied as argument.");
		}
	}

	public function getLastEditor() {
		return Employee::withID($this->lastEditorID);
	}

	public function getLastEditorID() {
		return $this->lastEditorID;
	}

	public function setLastEditorID($id) {
		if (is_int($id)) {
			$this->lastEditorID = $id;
		} else {
			try {
				$id = (int) $id;
				$this->lastEditorID  = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for last editor ID, got " . gettype($id) . " instead.");
			}
		}
	}

	public function getModificationTime() {
		return clone $this->lastModifiedTimestamp;
	}

	private function setModificationTime($timestamp) {
		if ($timestamp instanceof DateTime) {
			$this->lastModifiedTimestamp = clone $timestamp;
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $timestamp);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid modification date supplied as argument.");
				}
				$this->lastModifiedTimestamp = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid modification date supplied as argument.");
			}
		}
	}

	/**
	 * @return Doc[]
	 */
	public function getRevisionHistory() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT revision_history FROM docs WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			$result = json_decode($result["revision_history"], true);
			foreach ($result as $key => $revision) {
				$result[$key] = new Doc($revision["title"], $revision["last_edited_by"], $revision["contents"], $revision["last_modified"]);
			}
			return $result;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve revision history.");
		}
	}

	private function setRevisionHistory(array $revisions) {
		foreach ($revisions as $index => $revision) {
			$revisions[$index] = array(
				"title" => $revision->title,
				"last_edited_by" => $revision->lastEditorID,
				"last_modified" => Format::date($revision->lastModifiedTimestamp, Format::MYSQL_TIMESTAMP_FORMAT),
				"contents" => $revision->contents,
			);
		}
		$revisions = json_encode($revisions, JSON_PRETTY_PRINT);
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE docs SET revision_history = :revisionhistory WHERE pid = :pid");
			$stmt->bindParam(":revisionhistory", $revisions);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
		} catch (PDOException $e) {
			throw new Exception("Unable to update revision history.");
		}
	}

	public function __toString() {
		return $this->getTitle();
	}

}