<?php

class JobApp extends ActiveRecordAbstract {

	private $dateOfSubmission;
	private $firstName;
	private $lastName;
	private $birthday;
	private $phone;
	private $netID;
	private $alternateEmail;
	private $faculty;
	private $major;
	private $year;
	private $positionsApplied = array();
	private $status = 0;
	private $questions = array();
	private $website;
	private $github;
	private $resume;
	private $notes;

	const PENDING = 0;
	const REJECTED = 1;
	const APPROVED = 2;

	/**
	 * @return String[]
	 */
	public static function statusValues() {
		return array(
			self::PENDING => "Pending",
			self::REJECTED => "Rejected",
			self::APPROVED => "Approved",
		);
	}

	public function __construct($firstName, $lastName, $birthday, $phone, $netID, $alternateEmail, $faculty, $major, $year, $positionsApplied = array(), $status = 0, array $questions = array(), $website = "", $github = "", $submissionDate = NULL, $notes = "") {
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setBirthday($birthday);
		$this->setPhone($phone);
		$this->setNetID($netID);
		$this->setEmail($alternateEmail);
		$this->setFaculty($faculty);
		$this->setMajor($major);
		$this->setYear($year);
		$this->setPositionsAppliedFor($positionsApplied);
		$this->setStatus($status);
		$this->setQuestions($questions);
		$this->setWebsiteURL($website);
		$this->setGitHubURL($github);
		if (!$submissionDate) {
			$this->setSubmissionDate(new DateTime());
		} else {
			$this->setSubmissionDate($submissionDate);
		}
		$this->setNotes($notes);
	}

	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM applications WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid application ID provided.");
			}
		}
		throw new InvalidArgumentException("Expected int for application ID, got " . gettype($id));
	}

	private static function withRow(array $row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Application primary ID missing from constructor.");
		}
		if (!isset($row["date"])) {
			throw new InvalidArgumentException("Submission date missing from constructor.");
		}
		if (!isset($row["first_name"])) {
			throw new InvalidArgumentException("First name missing from constructor.");
		}
		if (!isset($row["last_name"])) {
			throw new InvalidArgumentException("Last name missing from constructor.");
		}
		if (!isset($row["birthday"])) {
			throw new InvalidArgumentException("Birthday missing from constructor.");
		}
		if (!isset($row["phone"])) {
			$row["phone"] = "";
		}
		if (!isset($row["netID"])) {
			throw new InvalidArgumentException("netID missing from constructor.");
		}
		if (!isset($row["alternate_email"])) {
			throw new InvalidArgumentException("Email address missing from constructor.");
		}
		if (!isset($row["faculty"])) {
			throw new InvalidArgumentException("Faculty name missing from constructor.");
		}
		if (!isset($row["major"])) {
			throw new InvalidArgumentException("Major name missing from constructor.");
		}
		if (!isset($row["year"])) {
			throw new InvalidArgumentException("Year missing from constructor.");
		}
		if (!isset($row["positions_applied_for"])) {
			throw new InvalidArgumentException("Position choices missing from constructor.");
		}
		if (!isset($row["status"])) {
			throw new InvalidArgumentException("Application status code missing from constructor.");
		}
		if (!isset($row["website"])) {
			$row["website"] = "";
		}
		if (!isset($row["github"])) {
			$row["github"] = "";
		}
		if (!isset($row["notes"])) {
			$row["notes"] = "";
		}

		$questions = array();
		if (isset($row["questions"]) && ($row["questions"] != "")) {
			foreach ($row["questions"] as $number => $single) {
				$questions[$number]             = array();
				$questions[$number]["question"] = $single["question"];
				$questions[$number]["answer"]   = $single["answer"];
			}
		} else {
			for ($i = 1; $i < 6; $i ++) {
				if (isset($row["question_" . $i]) && ($row["question_" . $i] != "")) {
					$questions[$i]             = array();
					$questions[$i]["question"] = $row["question_" . $i];
					$questions[$i]["answer"]   = $row["answer_" . $i];
				}
			}
		}

		$temp = new self($row["first_name"], $row["last_name"], $row["birthday"], $row["phone"], $row["netID"], $row["alternate_email"], $row["faculty"], $row["major"], $row["year"], $row["positions_applied_for"], $row["status"], $questions, urldecode($row["website"]), urldecode($row["github"]), $row["date"], $row["notes"]);
		$temp->setPID($row["pid"]);

		if (isset($row["resume_link"])) {
			if (substr($row["resume_link"], 0, 6) == "files/") {
				$temp->resume = $row["resume_link"];
			} else if (substr($row["resume_link"], 0, 4) == "http") {
				$temp->resume = $row["resume_link"];
			}
		}

		return $temp;
	}

	protected function insert() {
		$submissionDate = $this->dateOfSubmission->format(Format::MYSQL_TIMESTAMP_FORMAT);
		$birthday = $this->birthday->format(Format::MYSQL_DATE_FORMAT);
		if (is_array($this->positionsApplied)) {
			$positions = array();
			foreach ($this->positionsApplied as $code => $position) {
				if (is_array($position)) {
					array_push($positions, $code);
				} else {
					array_push($positions, $position);
				}
			}
			$positions = json_encode($positions);
		} else {
			$positions = $this->positionsApplied;
		}
		$question1 = "";
		$question2 = "";
		$question3 = "";
		$question4 = "";
		$question5 = "";
		$answer1 = "";
		$answer2 = "";
		$answer3 = "";
		$answer4 = "";
		$answer5 = "";
		for ($i = 1; $i < 6; $i++) {
			if (isset($this->questions[$i])) {
				${"question" . $i} = $this->questions[$i]["question"];
				${"answer" . $i}   = $this->questions[$i]["answer"];
			}
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO applications (pid, date, first_name, last_name, birthday, phone, netID, alternate_email, faculty, major, year, positions_applied_for, status, question_1, answer_1, question_2, answer_2, question_3, answer_3, question_4, answer_4, question_5, answer_5, website, github, resume_link, notes) VALUES (NULL, :submissiondate, :firstname, :lastname, :birthday, :phone, :netid, :alternateemail, :faculty, :major, :year, :positions, :status, :question1, :answer1, :question2, :answer2, :question3, :answer3, :question4, :answer4, :question5, :answer5, :website, :github, :resume, :notes)");
			$stmt->bindParam(":submissiondate", $submissionDate);
			$stmt->bindParam(":firstname", $this->firstName);
			$stmt->bindParam(":lastname", $this->lastName);
			$stmt->bindParam(":birthday", $birthday);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":netid", $this->netID);
			$stmt->bindParam(":alternateemail", $this->alternateEmail);
			$stmt->bindParam(":faculty", $this->faculty);
			$stmt->bindParam(":major", $this->major);
			$stmt->bindParam(":year", $this->year);
			$stmt->bindParam(":positions", $positions);
			$stmt->bindParam(":status", $this->status);
			$stmt->bindParam(":question1", $question1);
			$stmt->bindParam(":answer1", $answer1);
			$stmt->bindParam(":question2", $question2);
			$stmt->bindParam(":answer2", $answer2);
			$stmt->bindParam(":question3", $question3);
			$stmt->bindParam(":answer3", $answer3);
			$stmt->bindParam(":question4", $question4);
			$stmt->bindParam(":answer4", $answer4);
			$stmt->bindParam(":question5", $question5);
			$stmt->bindParam(":answer5", $answer5);
			$stmt->bindParam(":website", $this->website);
			$stmt->bindParam(":github", $this->github);
			$stmt->bindParam(":resume", $this->resume);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			var_dump($e->getMessage());
			return 0;
		}

	}

	protected function update() {
		$submissionDate = $this->dateOfSubmission->format(Format::MYSQL_TIMESTAMP_FORMAT);
		$birthday = $this->birthday->format(Format::MYSQL_DATE_FORMAT);
		if (is_array($this->positionsApplied)) {
			$positions = array();
			foreach ($this->positionsApplied as $code => $position) {
				if (is_array($position)) {
					array_push($positions, $code);
				} else {
					array_push($positions, $position);
				}
			}
			$positions = json_encode($positions);
		} else {
			$positions = $this->positionsApplied;
		}
		$question1 = "";
		$question2 = "";
		$question3 = "";
		$question4 = "";
		$question5 = "";
		$answer1 = "";
		$answer2 = "";
		$answer3 = "";
		$answer4 = "";
		$answer5 = "";
		for ($i = 1; $i < 6; $i++) {
			if (isset($this->questions[$i])) {
				${"question" . $i} = $this->questions[$i]["question"];
				${"answer" . $i}   = $this->questions[$i]["answer"];
			}
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE applications SET date = :submissiondate, first_name = :firstname, last_name = :lastname, birthday = :birthday, phone = :phone, netID = :netid, alternate_email = :alternateemail, faculty = :faculty, major = :major, year = :year, positions_applied_for = :positions, status = :status, question_1 = :question1, answer_1 = :answer1, question_2 = :question2, answer_2 = :answer2, question_3 = :question3, answer_3 = :answer3, question_4 = :question4, answer_4 = :answer4, question_5 = :question5, answer_5 = :answer5, website = :website, github = :github, resume_link = :resume, notes = :notes WHERE pid = :pid");
			$stmt->bindParam(":submissiondate", $submissionDate);
			$stmt->bindParam(":firstname", $this->firstName);
			$stmt->bindParam(":lastname", $this->lastName);
			$stmt->bindParam(":birthday", $birthday);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":netid", $this->netID);
			$stmt->bindParam(":alternateemail", $this->alternateEmail);
			$stmt->bindParam(":faculty", $this->faculty);
			$stmt->bindParam(":major", $this->major);
			$stmt->bindParam(":year", $this->year);
			$stmt->bindParam(":positions", $positions);
			$stmt->bindParam(":status", $this->status);
			$stmt->bindParam(":question1", $question1);
			$stmt->bindParam(":answer1", $answer1);
			$stmt->bindParam(":question2", $question2);
			$stmt->bindParam(":answer2", $answer2);
			$stmt->bindParam(":question3", $question3);
			$stmt->bindParam(":answer3", $answer3);
			$stmt->bindParam(":question4", $question4);
			$stmt->bindParam(":answer4", $answer4);
			$stmt->bindParam(":question5", $question5);
			$stmt->bindParam(":answer5", $answer5);
			$stmt->bindParam(":website", $this->website);
			$stmt->bindParam(":github", $this->github);
			$stmt->bindParam(":resume", $this->resume);
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
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		$this->setResumeWithFile(false);
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE from applications WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * @return JobApp[]
	 */
	public static function getAll() {
		$jobApps = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from applications ORDER BY date DESC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$jobApps[] = JobApp::withRow($row);
			}
			return $jobApps;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve application list.");
		}
	}

	/**
	 * @return JobApp[]
	 */
	public static function getPast() {
		$jobApps = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from applications WHERE status > " . self::PENDING . " ORDER BY date DESC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$jobApps[] = JobApp::withRow($row);
			}
			return $jobApps;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve past applications.");
		}
	}

	/**
	 * @return JobApp[]
	 */
	public static function getPending() {
		$jobApps = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * from applications WHERE status = " . self::PENDING . " ORDER BY date DESC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$jobApps[] = JobApp::withRow($row);
			}
			return $jobApps;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve pending applications.");
		}
	}

	/**
	 * @return DateTime
	 */
	public function getSubmissionDate() {
		return clone $this->dateOfSubmission;
	}

	public function setSubmissionDate($timestamp) {
		if ($timestamp instanceof DateTime) {
			$this->dateOfSubmission = clone $timestamp;
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $timestamp);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid submission date supplied as argument.");
				}
				$this->dateOfSubmission = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid submission date supplied as argument.");
			}
		}
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	public function setFirstName($name) {
		if (Validate::name($name)) {
			$this->firstName = $name;
		} else {
			throw new InvalidArgumentException("Invalid first name supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	public function setLastName($name) {
		if (Validate::name($name)) {
			$this->lastName = $name;
		} else {
			throw new InvalidArgumentException("Invalid last name supplied as argument.");
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
		} else if (Validate::date($date)) {
			$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
			if ($temp === false) {
				throw new InvalidArgumentException("Invalid birthday supplied as argument.");
			}
			$this->birthday = clone $temp;
		} else {
			throw new InvalidArgumentException("Invalid birthday supplied as argument.");
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
		if (Validate::phone($phone)) {
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
		if (!isset($this->alternateEmail) || $this->alternateEmail == "") {
			return $this->netID . "@queensu.ca";
		}
		return $this->alternateEmail;
	}

	public function setEmail($email) {
		if ($email == $this->netID . "@queensu.ca") {
			$this->alternateEmail = "";
		} else if (!$email) {
			$this->alternateEmail = "";
		} else if (Validate::email($email)) {
			$this->alternateEmail = $email;
		} else {
			throw new InvalidArgumentException("Invalid email address supplied as argument.");
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
	 * @return int
	 */
	public function getYear() {
		return $this->year;
	}

	public function setYear($year) {
		if (!is_int($year)) {
			try {
				$year = (int) $year;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for year, got " . gettype($year) . " instead.");
			}
		}
		if ($year <= 0) {
			throw new InvalidArgumentException("Expected non-negative, non-zero value for year.");
		}
		$this->year = $year;
	}

	/**
	 * @return array
	 */
	public function getPositionsAppliedFor() {
		return $this->positionsApplied;
	}

	public function setPositionsAppliedFor($jobs) {
		$temp = array();
		if (!empty($jobs) && !is_null($jobs)) {
			if (is_array($jobs)) {
				foreach ($jobs as $code => $position) {
					if (is_array($position)) {
						$temp[$code] = $position;
					} else {
						$temp[$position] = Employee::getPositionByCode($position);
					}
				}
			} else {
				$jobs = json_decode($jobs);
				foreach ($jobs as $code) {
					$temp[$code] = Employee::getPositionByCode($code);
				}
			}
		}
		$this->positionsApplied = $temp;
	}

	public function addPosition($code) {
		if (!is_int($code)) {
			try {
				$code = (int) $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for job position code, got " . gettype($code) . " instead.");
			}
		}
		if (array_key_exists($code, $this->positionsApplied)) {
			return;
		}
		if (array_key_exists($code, Employee::positions())) {
			$position = Employee::getPositionByCode($code);
			$this->positionsApplied[$code] = $position;
		} else {
			throw new OutOfBoundsException("Non-existent job position code supplied as argument.");
		}
	}

	public function removePosition($code) {
		if (!is_int($code)) {
			try {
				$code = (int) $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for job position code, got " . gettype($code) . " instead.");
			}
		}
		if (array_key_exists($code, $this->positionsApplied)) {
			unset($this->positionsApplied[$code]);
		} else {
			throw new OutOfBoundsException("Non-existent job position code supplied as argument.");
		}
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	public function setStatus($code) {
		if (is_int($code)) {
			$this->status = $code;
		} else {
			try {
				$code = (int) $code;
				$this->status = $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for status code, got " . gettype($code) . " instead.");
			}
		}
	}

	public function reject() {
		$this->status = self::REJECTED;
		$this->save();
	}

	public function approve($position) {
		$this->status = self::APPROVED;
		$this->save();

		if (is_int($position) || ctype_digit($position)) {
			$position = Employee::getPositionByCode($position)["title"];
		}

		$employee = new Employee($this->firstName, $this->lastName, $position, $this->birthday, $this->netID, $this->phone, $this->alternateEmail, $this->faculty, $this->major, $this->website, $this->github);
		$employee->setApplicationID($this->pid);
		$employee->save();

		return $employee;
	}

	/**
	 * @return string
	 */
	public function getStatusText() {
		$values = self::statusValues();
		if (array_key_exists($this->status, $values)) {
			return $values[$this->status];
		}
		return "Unknown";
	}

	/**
	 * @return array
	 */
	public function getQuestions() {
		$temp = $this->questions;
		foreach ($temp as $key => $row) {
			$temp[$key] = array();
			$temp[$key]["question"] = $row["question"];
			$temp[$key]["answer"] = $row["answer"];
		}
		return $temp;
	}

	public function setQuestions(array $questions) {
		$temp = $questions;
		foreach ($temp as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				$temp[$key1][$key2] = $value2;
			}
		}
		$this->questions = $temp;
	}

	/**
	 * @return array
	 */
	public static function currentQuestions() {
		$questions = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * FROM site WHERE category = 'applicationquestion'");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				if (trim($row["contents"]) != "") {
					$questions[$row["pid"]] = $row["contents"];
				}
			}
			return $questions;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve current application questions.");
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

	private static $resumeDirectory = "files/applications";

	public function resumeIsFile() {
		return (substr($this->resume, 0, 6) == "files/") ? true : false;
	}

	public function resumeIsURL() {
		return (substr($this->resume, 0, 7) == "http://" || substr($this->resume, 0, 8) == "https://") ? true : false;
	}

	public function getResumeFile() {
		// if not a stored record, return false
		if (!$this->pid) {
			return false;
		}
		$filenameWithoutExtension = "resume_" . $this->pid;
		return FileReadWrite::readPDF(self::$resumeDirectory, $filenameWithoutExtension);
	}

	public function getResumeURL() {
		return $this->resume;
	}

	public function setResumeWithURL($url) {
		if (!isset($url) || !$url) {
			$this->setResumeWithFile(false);
			$this->resume = "";
		} else if (Validate::url($url)) {
			$this->setResumeWithFile(false);
			$this->resume = $url;
		} else {
			throw new InvalidArgumentException("Invalid URL for resumé supplied as argument.");
		}

	}

	public function setResumeWithFile($file) {
		// if not a stored record, return false
		if (!$this->pid) {
			return false;
		}
		$filenameWithoutExtension = "resume_" . $this->pid;
		$success = FileReadWrite::writePDF($file, self::$resumeDirectory, $filenameWithoutExtension);
		if ($success) {
			if ($file === false) {
				unset($this->resume);
			} else {
				$this->resume = $this->getResumeFile();
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
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

	public function __toString() {
		return $this->firstName . " " . $this->lastName;
	}

}