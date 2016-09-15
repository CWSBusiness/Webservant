<?php

class Employee extends ActiveRecordAbstract {

	private $first_name;
	private $last_name;
	private $position;
	private $birthday;
	private $phone;
	private $netID;
	private $alternate_email;
	private $website;
	private $linkedin;
	private $github;
	private $faculty;
	private $major;
	private $start_date;
	private $current_employee;
	private $end_date;
	private $applicationID;
	private $notes;

	private static $currentEmployee = NULL;

	public function __construct($firstName, $lastName, $position, $birthday, $netID, $phone = "", $alternateEmail = "", $faculty = "", $major = "", $website = "", $github = "", $linkedin = "", $notes = "") {

		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setPosition($position);
		$this->setBirthday($birthday);
		$this->setPhone($phone);
		$this->setNetID($netID);
		$this->setEmail($alternateEmail);
		$this->setFaculty($faculty);
		$this->setMajor($major);
		$this->setStartDate(new DateTime());
		$this->current_employee = true;
		$this->setEndDate(false);
		$this->setWebsiteURL($website);
		$this->setGitHubURL($github);
		$this->setLinkedInURL($linkedin);
		$this->setNotes($notes);
	}

	private static $positionCodes = array(
		1 => array(
			"title" => "Project Manager",
			"available" => false
		),
		2 => array(
			"title" => "Business Manager",
			"available" => false
		),
		3 => array(
			"title" => "Services Director",
			"available" => false
		),
		10 => array(
			"title" => "Team Lead",
			"available" => true
		),
		11 => array(
			"title" => "Web Developer",
			"available" => true
		),
		12 => array(
			"title" => "Designer",
			"available" => true
		),
		13 => array(
			"title" => "iOS Developer",
			"available" => true
		),
		14 => array(
			"title" => "Android Developer",
			"available" => true
		)
	);

	public static function positions() {
		return self::$positionCodes;
	}

	public static function availablePositions() {
		$temp = array();
		foreach (self::$positionCodes as $code => $entry) {
			if ($entry["available"]) {
				$temp[$code] = $entry;
			}
		}
		return $temp;
	}

	public static function getPositionByCode($code) {
		if (!is_int($code)) {
			try {
				$code = (int) $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for employee position code, got " . gettype($code) . " instead.");
			}
		}
		if (array_key_exists($code, self::$positionCodes)) {
			return self::$positionCodes[$code];
		}
		throw new OutOfBoundsException("Invalid employee position code supplied as argument.");
	}
	
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM employees WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid employee ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for employee ID, got " . gettype($id) . " instead.");
	}
	
	private static function withRow(array $row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Employee ID missing from constructor.");
		}

		if (!isset($row["first_name"])) {
			throw new InvalidArgumentException("First name missing from constructor.");
		}

		if (!isset($row["last_name"])) {
			throw new InvalidArgumentException("Last name missing from constructor.");
		}

		if (!isset($row["position"])) {
			throw new InvalidArgumentException("Position title missing from constructor.");
		}

		if (!isset($row["birthday"])) {
			throw new InvalidArgumentException("Birthday missing from constructor.");
		}

		if (!isset($row["faculty"])) {
			$row["faculty"] = "";
		}

		if (!isset($row["major"])) {
			$row["major"] = "";
		}

		if (!isset($row["phone"])) {
			$row["phone"] = "";
		}

		if (!isset($row["alternate_email"])) {
			$row["alternate_email"] = "";
		}

		if (!isset($row["netID"])) {
			throw new InvalidArgumentException("netID missing from constructor.");
		}

		if (!isset($row["website"])) {
			$row["website"] = "";
		}

		if (!isset($row["github"])) {
			$row["github"] = "";
		}

		if (!isset($row["linkedin"])) {
			$row["linkedin"] = "";
		}
		if (!isset($row["notes"])) {
			$row["notes"] = "";
		}
		if (!isset($row["application_id"])) {
			$row["application_id"] = 0;
		}

		$temp = new self($row["first_name"], $row["last_name"], $row["position"], $row["birthday"], $row["netID"], $row["phone"], $row["alternate_email"], $row["faculty"], $row["major"], urldecode($row["website"]), urldecode($row["github"]), urldecode($row["linkedin"]), $row["notes"]);
		$temp->setPID($row["pid"]);
		$temp->setApplicationID($row["application_id"]);

		if (isset($row["start_date"])) {
			$temp->setStartDate($row["start_date"]);
		} else {
			throw new InvalidArgumentException("Start date missing from constructor.");
		}

		if (isset($row["current_employee"])) {
			if ($row["current_employee"]) {
				$temp->current_employee = true;
				$temp->setEndDate(false);
			} else {
				$temp->current_employee = false;
				if (isset($row["end_date"])) {
					$temp->setEndDate($row["end_date"]);
				} else {
					throw new InvalidArgumentException("End date missing from constructor.");
				}
			}
		} else {
			throw new InvalidArgumentException("Current employee status missing from constructor.");
		}

		return $temp;

	}
	
	public static function current() {
		if (is_null(self::$currentEmployee)) {
			if (User::current()->isEmployee()) {
				self::$currentEmployee = self::withID(User::current()->getEmployeeID());
			}
		}
		return self::$currentEmployee;
	}

	/**
	 * @return Employee[]
	 */
	public static function getAll() {
		$empObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from employees ORDER BY last_name ASC");
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $employee) {
				$empObjs[] = Employee::withRow($employee);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve employees list.");
		}
		return $empObjs;
	}

	/**
	 * @return Employee[]
	 */
	public static function getCurrent() {
		$empObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from employees WHERE current_employee = '1' ORDER BY last_name ASC");
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $employee) {
				$empObjs[] = Employee::withRow($employee);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve current employees list.");
		}
		return $empObjs;
	}

	/**
	 * @return Employee[]
	 */
	public static function getPast() {
		$empObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from employees WHERE current_employee = '0' ORDER BY last_name ASC");
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $employee) {
				$empObjs[] = Employee::withRow($employee);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve past employees list.");
		}
		return $empObjs;
	}

	protected function insert() {
		$currentEmployee = ($this->current_employee) ? 1 : 0;
		$birthday = $this->birthday->format(Format::MYSQL_DATE_FORMAT);
		$startDate = $this->start_date->format(Format::MYSQL_DATE_FORMAT);
		$endDate = $this->end_date->format(Format::MYSQL_DATE_FORMAT);
		if (!isset($this->applicationID)) {
			$this->applicationID = 0;
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO employees (pid, first_name, last_name, position, application_id, birthday, phone, netID, alternate_email, website, github, linkedin, faculty, major, start_date, current_employee, end_date, notes) VALUES (NULL, :firstname, :lastname, :position, :applicationid, :birthday, :phone, :netid, :alternateemail, :website, :github, :linkedin, :faculty, :major, :startdate, :currentemployee, :enddate, :notes)");
			$stmt->bindParam(":firstname", $this->first_name);
			$stmt->bindParam(":lastname", $this->last_name);
			$stmt->bindParam(":position", $this->position);
			$stmt->bindParam(":applicationid", $this->applicationID);
			$stmt->bindParam(":birthday", $birthday);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":netid", $this->netID);
			$stmt->bindParam(":alternateemail", $this->alternate_email);
			$stmt->bindParam(":website", $this->website);
			$stmt->bindParam(":github", $this->github);
			$stmt->bindParam(":linkedin", $this->linkedin);
			$stmt->bindParam(":faculty", $this->faculty);
			$stmt->bindParam(":major", $this->major);
			$stmt->bindParam(":startdate", $startDate);
			$stmt->bindParam(":currentemployee", $currentEmployee);
			$stmt->bindParam(":enddate", $endDate);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		$currentEmployee = ($this->current_employee) ? 1 : 0;
		$birthday = $this->birthday->format(Format::MYSQL_DATE_FORMAT);
		$startDate = $this->start_date->format(Format::MYSQL_DATE_FORMAT);
		$endDate = $this->end_date->format(Format::MYSQL_DATE_FORMAT);
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE employees SET first_name = :firstname, last_name = :lastname, position = :position, application_id = :applicationid, birthday = :birthday, phone = :phone, netID = :netid, alternate_email = :alternateemail, website = :website, github = :github, linkedin = :linkedin, faculty = :faculty, major = :major, start_date = :startdate, current_employee = :currentemployee, end_date = :enddate, notes = :notes WHERE pid = :pid");
			$stmt->bindParam(":firstname", $this->first_name);
			$stmt->bindParam(":lastname", $this->last_name);
			$stmt->bindParam(":position", $this->position);
			$stmt->bindParam(":applicationid", $this->applicationID);
			$stmt->bindParam(":birthday", $birthday);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":netid", $this->netID);
			$stmt->bindParam(":alternateemail", $this->alternate_email);
			$stmt->bindParam(":website", $this->website);
			$stmt->bindParam(":github", $this->github);
			$stmt->bindParam(":linkedin", $this->linkedin);
			$stmt->bindParam(":faculty", $this->faculty);
			$stmt->bindParam(":major", $this->major);
			$stmt->bindParam(":startdate", $startDate);
			$stmt->bindParam(":currentemployee", $currentEmployee);
			$stmt->bindParam(":enddate", $endDate);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete non-existent record.");
		}
		$this->setProfilePic(false);    // delete profile pic
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE from employees WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getFirstName() {
		return $this->first_name;
	}

	public function setFirstName($name) {
		if (Validate::name($name)) {
			$this->first_name = $name;
		} else {
			throw new InvalidArgumentException("Invalid first name supplied as argument.");
		}
	}

	public function getLastName() {
		return $this->last_name;
	}

	public function setLastName($name) {
		if (Validate::name($name)) {
			$this->last_name = $name;
		} else {
			throw new InvalidArgumentException("Invalid last name supplied as argument.");
		}
	}

	public function getPosition() {
		return $this->position;
	}
	
	public function setPosition($title) {
		if (Validate::plainText($title)) {
			$this->position = $title;
		} else {
			throw new InvalidArgumentException("Invalid position title supplied as argument.");
		}
	}

	/**
	 * @return DateTime
	 */
	public function getBirthday() {
		return clone $this->birthday;
	}

	public function setBirthday($date) {
		if ($date instanceof DateTime) {
			$this->birthday = clone $date;
		} else {
			try {
				$date = Format::date($date, Format::MYSQL_DATE_FORMAT);
				$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid birthday supplied as argument.");
				}
				$this->birthday = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid birthday supplied as argument.");
			}
		}
		$this->birthday->setTime(0, 0, 0);
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	public function setPhone($phone) {
		if ($phone == "") {
			$this->phone = "";
		} else if (Validate::phone($phone)) {
			$this->phone = $phone;
		} else {
			throw new InvalidArgumentException("Invalid phone number supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getNetID() {
		return $this->netID;
	}

	public function setNetID($netID) {
		if (Validate::netID($netID)) {
			$this->netID = $netID;
		} else {
			throw new InvalidArgumentException("Invalid netID supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		if (!isset($this->alternate_email) || $this->alternate_email == "") {
			return $this->netID . "@queensu.ca";
		}
		return $this->alternate_email;
	}

	public function setEmail($email) {
		if (empty($email) || trim($email) == "") {
			$this->alternate_email = "";
		} else if ($email == $this->netID . "@queensu.ca") {
			$this->alternate_email = "";
		} else {
			if (Validate::email($email)) {
				$this->alternate_email = $email;
			} else {
				throw new InvalidArgumentException("Invalid email address supplied as argument.");
			}
		}
	}

	/**
	 * @return string
	 */
	public function getFaculty() {
		return $this->faculty;
	}

	public function setFaculty($faculty) {
		if (Validate::plainText($faculty)) {
			$this->faculty = $faculty;
		} else {
			throw new InvalidArgumentException("Invalid faculty name supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getMajor() {
		return $this->major;
	}

	public function setMajor($major) {
		if (Validate::plainText($major)) {
			$this->major = $major;
		} else {
			throw new InvalidArgumentException("Invalid major name supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getWebsiteURL() {
		return $this->website;
	}

	public function setWebsiteURL($url) {
		if (!isset($url) || !$url) {
			$this->website = "";
		} else if (Validate::url($url)) {
			$this->website = $url;
		} else {
			throw new InvalidArgumentException("Invalid website URL supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getGitHubURL() {
		return $this->github;
	}

	public function setGitHubURL($url) {
		if (!isset($url) || !$url) {
			$this->github = "";
		} else if (Validate::url($url)) {
			$this->github = $url;
		} else {
			throw new InvalidArgumentException("Invalid GitHub URL supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getLinkedInURL() {
		return $this->linkedin;
	}

	public function setLinkedInURL($url) {
		if (!isset($url) || !$url) {
			$this->linkedin = "";
		} else if (Validate::url($url)) {
			$this->linkedin = $url;
		} else {
			throw new InvalidArgumentException("Invalid LinkedIn URL supplied as argument.");
		}
	}

	/**
	 * @return DateTime
	 */
	public function getStartDate() {
		return clone $this->start_date;
	}

	public function setStartDate($date) {
		if ($date instanceof DateTime) {
			$this->start_date = clone $date;
		} else {
			if (!Validate::date($date)) {
				throw new InvalidArgumentException("Invalid start date supplied as argument.");
			}
			try {
				$date = Format::date($date, Format::MYSQL_DATE_FORMAT);
				$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid start date supplied as argument.");
				} else {
					$this->start_date = clone $temp;
				}
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid start date supplied as argument.");
			}
		}
		$this->start_date->setTime(0, 0, 0);
	}

	/**
	 * @return DateTime
	 */
	public function getEndDate() {
		return ($this->current_employee) ? false : clone $this->end_date;
	}

	public function setEndDate($date) {
		if ($date === false || $date === 0) {
			$this->end_date = DateTime::createFromFormat("U", "0");
			$this->current_employee = true;
		} else if (trim($date) == "") {
			$this->end_date = DateTime::createFromFormat("U", "0");
			$this->current_employee = true;
		} else {
			if ($date instanceof DateTime) {
				$this->end_date = clone $date;
				$this->current_employee = false;
			} else {
				if (!Validate::date($date)) {
					throw new InvalidArgumentException("Invalid end date supplied as argument.");
				}
				try {
					$date = Format::date($date, Format::MYSQL_DATE_FORMAT);
					$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
					if ($temp === false) {
						throw new InvalidArgumentException("Invalid end date supplied as argument.");
					}
					$this->end_date = clone $temp;
					$this->current_employee = false;
				} catch (Exception $e) {
					throw new InvalidArgumentException("Invalid end date supplied as argument.");
				}
			}
		}
		if ($this->end_date instanceof DateTime) {
			$this->end_date->setTime(0, 0, 0);
		}
	}
	
	public function isCurrentEmployee() {
		return ($this->current_employee) ? true : false;
	}

	public function getApplicationID() {
		return $this->applicationID;
	}

	public function setApplicationID($id) {
		if (is_int($id)) {
			$this->applicationID = $id;
		} else {
			try {
				$id = (int) $id;
				$this->applicationID = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for application ID, got " . gettype($id) . " instead.");
			}
		}
	}

	public function getNotes() {
		return $this->notes;
	}

	public function setNotes($text) {
		if (Validate::plainText($text, true)) {
			$this->notes = $text;
		} else {
			throw new InvalidArgumentException("Invalid notes supplied as argument.");
		}
	}

	private static $profilePicMaxSize = MAX_FILESIZE;
	private static $profilePicDirectory = "files/employees/profile_pics";
	private static $profilePicTypesList = array("SVG", "PNG", "JPG");

	public static function profilePicTypesText() {
		$string = "";
		foreach (self::$profilePicTypesList as $type) {
			$string .= $type . ", ";
		}
		return substr($string, 0, -2);
	}

	public static function profilePicMaxSizeText() {
		return Format::bytes(self::$profilePicMaxSize);
	}

	public function getProfilePic() {
		$filenameWithoutExtension = $this->pid;
		return FileReadWrite::readImage(self::$profilePicDirectory, $filenameWithoutExtension);
	}

	public function setProfilePic($file) {
		$filenameWithoutExtension = $this->pid;
		return FileReadWrite::writeImage($file, self::$profilePicDirectory, $filenameWithoutExtension, self::$profilePicMaxSize);
	}

	public function __toString() {
		return $this->first_name . " " . $this->last_name;
	}
	
}