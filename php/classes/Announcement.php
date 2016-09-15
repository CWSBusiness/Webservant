<?php

/**
 * Class Announcement
 *
 * A class representing a dashboard announcement.
 *
 * The announcement consists of a title, text or HTML content, the group it applies to
 * (all users, employee accounts only, or admin accounts only), the starting time of when the
 * announcement will be visible, and the end time when the announcement is no longer visible.
 */
class Announcement extends ActiveRecordAbstract {

	private $title;
	private $content = "";
	private $applies_to = 0;
	private $date_up;
	private $date_down;

	/**
	 * Construct a new announcement
	 *
	 * @param string    $title
	 * @param string    $contents
	 * @param int       $appliesTo
	 * @param DateTime  $upTime
	 * @param DateTime  $downTime
	 */
	public function __construct($title, $contents, $appliesTo = 0, $upTime = NULL, $downTime = NULL) {
		$this->setTitle($title);
		$this->setContents($contents);
		$this->setGroup($appliesTo);
		$this->setUpTime($upTime);
		$this->setDownTime($downTime);
	}

	/**
	 * Construct an announcement by ID
	 *
	 * @param int $id           The announcement ID to look up
	 *
	 * @return Announcement     The requested announcement
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM announcements WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new OutOfBoundsException("Nonexistent announcement ID supplied to constructor.");
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid announcement ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for announcement ID, got " . gettype($id) . " instead.");
	}

	/**
	 * Get all announcements in the database
	 *
	 * @return Announcement[]       All announcements
	 *
	 * @throws RuntimeException     If the announcements list cannot be retrieved
	 */
	public static function getAll() {
		$objs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * FROM announcements ORDER BY date_up DESC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $announcement) {
				$objs[] = Announcement::withRow($announcement);
			}
			return $objs;
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve announcements list.");
		}
	}

	/**
	 * Get all current announcements
	 *
	 * @return Announcement[]       All current announcements
	 *
	 * @throws RuntimeException     If the announcements list cannot be retrieved
	 */
	public static function getCurrent() {
		$current = array();
		$all = self::getAll();
		foreach ($all as $announcement) {
			if ($announcement->isCurrentlyActive()) {
				$current[] = $announcement;
			}
		}
		return $current;
	}

	private static function withRow($row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Announcement primary ID missing from constructor.");
		}
		if (!isset($row["title"])) {
			throw new InvalidArgumentException("Announcement title missing from constructor.");
		}
		if (!isset($row["content"])) {
			throw new InvalidArgumentException("Announcement contents missing from constructor.");
		}
		if (!isset($row["applies_to"])) {
			throw new InvalidArgumentException("Announcement audience group missing from constructor.");
		}
		if (!isset($row["date_up"])) {
			throw new InvalidArgumentException("Up time missing from constructor.");
		}
		if (!isset($row["date_down"])) {
			throw new InvalidArgumentException("Down time missing from constructor.");
		}

		$temp = new self($row["title"], $row["content"], $row["applies_to"], $row["date_up"], $row["date_down"]);
		$temp->setPID($row["pid"]);

		return $temp;

	}

	protected function insert() {
		$dateUp = $this->date_up->format(Format::MYSQL_TIMESTAMP_FORMAT);
		$dateDown = $this->date_down->format(Format::MYSQL_TIMESTAMP_FORMAT);

		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO announcements (pid, title, content, applies_to, date_up, date_down) VALUES (NULL, :title, :content, :appliesto, :dateup, :datedown)");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":content", $this->content);
			$stmt->bindParam(":appliesto", $this->applies_to);
			$stmt->bindParam(":dateup", $dateUp);
			$stmt->bindParam(":datedown", $dateDown);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		$dateUp = $this->date_up->format(Format::MYSQL_TIMESTAMP_FORMAT);
		$dateDown = $this->date_down->format(Format::MYSQL_TIMESTAMP_FORMAT);

		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE announcements SET title = :title, content = :content, applies_to = :appliesto, date_up = :dateup, date_down = :datedown WHERE pid = :pid");
			$stmt->bindParam(":title", $this->title);
			$stmt->bindParam(":content", $this->content);
			$stmt->bindParam(":appliesto", $this->applies_to);
			$stmt->bindParam(":dateup", $dateUp);
			$stmt->bindParam(":datedown", $dateDown);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Delete the current Announcement from the database
	 *
	 * @return bool                     True if the delete succeeded, and false otherwise
	 *
	 * @throws BadMethodCallException   If the object does not yet exist as a database record
	 */
	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM announcements WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Get the announcement title
	 *
	 * @return string           The title of the announcement
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set the announcement title
	 *
	 * @param string $title     The title of the announcement
	 */
	public function setTitle($title) {
		if (Validate::plainText($title)) {
			$this->title = $title;
		} else {
			throw new InvalidArgumentException("Invalid announcement title supplied as argument.");
		}
	}

	/**
	 * Get the announcement contents
	 *
	 * @return string           The contents of the announcement
	 */
	public function getContents() {
		return $this->content;
	}

	/**
	 * Set the announcement contents
	 *
	 * @param string $contents  The contents of the announcement
	 */
	public function setContents($contents) {
		if (Validate::HTML($contents)) {
			$this->content = $contents;
		} else {
			throw new InvalidArgumentException("Invalid announcement contents supplied as argument.");
		}
	}

	/**
	 * Get the short version of the visibility groups list
	 *
	 * @return array    The list of groups
	 */
	public static function getGroupsShort() {
		return array(
			0 => "Everyone",
			1 => "Employees",
			2 => "Admins"
		);
	}

	/**
	 * Get the visibility groups list
	 *
	 * @return array    The list of groups
	 */
	public static function getGroups() {
		return array(
			0 => "Everyone",
			1 => "Employee Accounts",
			2 => "Admin Accounts"
		);
	}

	/**
	 * Get the visibility group code of the current Announcement
	 *
	 * @return int      The group code
	 */
	public function getGroup() {
		return $this->applies_to;
	}

	/**
	 * Get the visibility group text of the current Announcement
	 *
	 * @return string   The group name
	 */
	public function getGroupText() {
		$groups = self::getGroups();
		if (array_key_exists($this->applies_to, $groups)) {
			return $groups[$this->applies_to];
		}
		return "Unknown";
	}

	/**
	 * Get the short version of the visibility group text of the current Announcement
	 *
	 * @return string   The short group name
	 */
	public function getGroupTextShort() {
		$groups = self::getGroupsShort();
		if (array_key_exists($this->applies_to, $groups)) {
			return $groups[$this->applies_to];
		}
		return "Unknown";
	}

	/**
	 * Set the visibility group of the current Announcement
	 *
	 * @param int $code     The visibility group to apply the announcement to
	 */
	public function setGroup($code) {
		if (is_int($code)) {
			$this->applies_to = $code;
		} else {
			try {
				$code = (int) $code;
				$this->applies_to = $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for announcement group code, got " . gettype($code) . " instead.");
			}
		}
	}

	/**
	 * Get the time when the announcement becomes visible
	 *
	 * @return DateTime     The up date/time of the announcement
	 */
	public function getUpTime() {
		return clone $this->date_up;
	}

	/**
	 * Set the time when the announcement becomes visible
	 *
	 * @param DateTime|string $time     The up date/time of the announcement
	 */
	public function setUpTime($time) {
		if ($time instanceof DateTime) {
			$this->date_up = clone $time;
		} else if ($time == false) {
			$this->date_up = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, time(Format::MYSQL_TIMESTAMP_FORMAT));
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $time);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid up time supplied as argument.");
				}
				$this->date_up = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid up time supplied as argument.");
			}
		}
		$this->date_up->setTime($this->date_up->format("G"), 0, 0);         // round to the nearest hour
	}

	/**
	 * Get the time when the announcement stops being visible
	 *
	 * @return DateTime     The down date/time of the announcement
	 */
	public function getDownTime() {
		return clone $this->date_down;
	}

	/**
	 * Set the time when the announcement stops being visible
	 *
	 * @param DateTime|string $time     The down date/time of the announcement
	 */
	public function setDownTime($time) {
		if ($time instanceof DateTime) {
			$this->date_down = clone $time;
		} else if ($time == false) {
			$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, time(Format::MYSQL_TIMESTAMP_FORMAT));
			if ($temp === false) {
				throw new InvalidArgumentException("Invalid down time supplied as argument.");
			}
			$temp->add(DateInterval::createFromDateString("2 weeks"));
			$this->date_down = clone $temp;
		} else {
			try {
				$this->date_down = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $time);
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid down time supplied as argument.");
			}
		}
		$this->date_down->setTime($this->date_down->format("G"), 0, 0);     // round to the nearest hour
	}

	/**
	 * Check whether the announcement is currently visible, based on its up/down times
	 *
	 * @return bool     True if the announcement is visible, and false if not
	 */
	public function isCurrentlyActive() {
		$now = new DateTime();
		return ($this->date_up <= $now && $now < $this->date_down);
	}

	/**
	 * Get a string representation of the announcement
	 *
	 * @return string   The title of the announcement
	 */
	public function __toString() {
		return $this->getTitle();
	}

}