<?php

class Project extends ActiveRecordAbstract {

	private $name;
	private $client;
	private $url;
	private $technicalContacts = array();
	private $milestones = array();
	private $description;
	private $notes;

	public function __construct($name, $companyID, $clientIDs = array(), $milestones = array(), $url = "", $description = "", $notes = "") {
		$this->setName($name);
		$this->setCompanyID($companyID);
		$this->setTechnicalContacts($clientIDs);
		$this->setMilestones($milestones);
		$this->setURL($url);
		$this->setDescription($description);
		$this->setNotes($notes);
	}

	protected function insert() {
		$contacts = json_encode($this->technicalContacts);
		$milestones = $this->getMilestonesJSON();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO projects (pid, name, url, client_id, technical_contacts, milestones, description, notes) VALUES (NULL, :name, :url, :client, :contacts, :milestones, :description, :notes)");
			$stmt->bindParam(":name", $this->name);
			$stmt->bindParam(":url", $this->url);
			$stmt->bindParam(":client", $this->client);
			$stmt->bindParam(":contacts", $contacts);
			$stmt->bindParam(":milestones", $milestones);
			$stmt->bindParam(":description", $this->description);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->execute();
			$result = $pdo->lastInsertId();
			$this->pid = $result;
			return $result;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		$contacts = json_encode($this->technicalContacts);
		$milestones = $this->getMilestonesJSON();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE projects SET name = :name, url = :url, client_id = :client, technical_contacts = :contacts, milestones = :milestones, description = :description, notes = :notes WHERE pid = :pid");
			$stmt->bindParam(":name", $this->name);
			$stmt->bindParam(":url", $this->url);
			$stmt->bindParam(":client", $this->client);
			$stmt->bindParam(":contacts", $contacts);
			$stmt->bindParam(":milestones", $milestones);
			$stmt->bindParam(":description", $this->description);
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
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM projects WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	private static function withRow(array $row) {

		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Project primary ID missing from constructor.");
		}
		if (!isset($row["name"])) {
			throw new InvalidArgumentException("Project name missing from constructor.");
		}
		if (!isset($row["client_id"])) {
			throw new InvalidArgumentException("Client company ID missing from constructor.");
		}
		if (!isset($row["technical_contacts"])) {
			throw new InvalidArgumentException("Technical contacts missing from constructor.");
		}
		if (!isset($row["milestones"])) {
			throw new InvalidArgumentException("Milestones missing from constructor.");
		}
		if (!isset($row["description"])) {
			$row["description"] = "";
		}
		if (!isset($row["url"])) {
			$row["url"] = "";
		}
		if (!isset($row["notes"])) {
			$row["notes"] = "";
		}

		$temp = new self($row["name"], $row["client_id"], $row["technical_contacts"], $row["milestones"], $row["url"], $row["description"], $row["notes"]);
		$temp->setPID($row["pid"]);

		return $temp;
	}

	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM projects WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid project ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for project ID, got " . gettype($id) . " instead.");
	}

	/**
	 * @return Project[]
	 */
	public static function getActive() {
		return self::getEntriesPrivate(true);
	}

	/**
	 * @return Project[]
	 */
	public static function getAll() {
		return self::getEntriesPrivate();
	}

	private static function getEntriesPrivate($activeOnly = false) {
		$projectObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * FROM projects");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			if ($activeOnly) {
				foreach ($results as $project) {
					$temp = self::withRow($project);
					$active = false;
					foreach ($temp->getMilestones() as $milestone) {
						if ($milestone->getStatus() > 0 || !$milestone->paidInFull()) {
							$active = true;
							break;
						}
					}
					if ($active) {
						$projectObjs[] = $temp;
					} else {
						unset($temp);
					}
				}
			} else {
				foreach ($results as $project) {
					$projectObjs[] = self::withRow($project);
				}
			}
		} catch (PDOException $e) {
			if ($activeOnly) {
				throw new Exception("Unable to retrieve active projects list.");
			} else {
				throw new Exception("Unable to retrieve projects list.");
			}
		}
		usort($projectObjs, function($a, $b) {
			return strcasecmp($a->getName(), $b->getName());
		});
		return $projectObjs;
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getProjectNameByID($id) {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT name FROM projects WHERE pid = :pid");
			$stmt->bindParam(":pid", $id);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return $result["name"];
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve company name by ID.");
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Project[]
	 * @throws Exception
	 */
	public static function getProjectsByCompanyID($id) {
		$projects = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT * FROM projects WHERE client_id = :clientid");
			$stmt->bindParam(":clientid", $id);
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $project) {
				$projects[] = self::withRow($project);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve company projects list.");
		}
		return $projects;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		if (Validate::plainText($name)) {
			$this->name = $name;
		} else {
			throw new InvalidArgumentException("Invalid project name supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	public function setURL($url) {
		if (!isset($url) || !$url) {
			$this->url = "";
		} else if (Validate::url($url)) {
			$this->url = $url;
		} else {
			throw new InvalidArgumentException("Invalid project URL supplied as argument.");
		}
	}

	/**
	 * @return int
	 */
	public function getCompanyID() {
		return $this->client;
	}

	public function setCompanyID($id) {
		if (is_int($id)) {
			$this->client = $id;
		} else {
			try {
				$id= (int) $id;
				$this->client = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for client ID, got " . gettype($id) . " instead.");
			}
		}
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getCompanyName() {
		if ($this->client == 0) {
			return COMPANY_NAME;
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT company_name FROM clients WHERE client_id = :client LIMIT 1");
			$stmt->bindParam(":client", $this->client);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return $result["company_name"];
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve company name.");
		}
	}

	/**
	 * @return Client[]
	 */
	public function getTechnicalContacts() {
		$temp = array();
		foreach ($this->technicalContacts as $person) {
			$temp[$person] = Client::withID($person);
		}
		return $temp;
	}

	public function setTechnicalContacts($array) {
		if (!is_array($array)) {
			$array = json_decode($array);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new InvalidArgumentException("Expected array or JSON data as argument for technical contacts.");
			}
		}
		if (is_null($array)) {
			throw new InvalidArgumentException("Invalid technical contacts array supplied to constructor.");
		} else {
			$temp = array();
			foreach ($array as $key => $value) {
				if ($value instanceof Client) {
					array_push($temp, $key);
				} else {
					array_push($temp, $value);
				}
			}
			$this->technicalContacts = $temp;
		}
	}

	/**
	 * @return bool
	 */
	public function isActive() {
		foreach ($this->getMilestones() as $milestone) {
			if ($milestone->getStatus() > 0 || !$milestone->paidInFull()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return Milestone[]
	 */
	public function getMilestones() {
		return $this->milestones;
	}

	/**
	 * @return Milestone[]
	 */
	public function getActiveMilestones() {
		$activeMilestones = array();
		foreach ($this->getMilestones() as $key => $milestone) {
			if ($milestone->getStatus() > 0 || !$milestone->paidInFull()) {
				$activeMilestones[$key] = $milestone;
			}
		}
		return $activeMilestones;
	}

	private function setMilestones($milestones) {
		if (!$milestones) {
			$this->milestones = array();
		} else {
			if (is_array($milestones)) {
				foreach ($milestones as $key => $milestone) {
					$this->milestones[$key] = $milestones[$key];
				}
			} else {
				$milestones = json_decode($milestones, true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					throw new InvalidArgumentException("Expected array or JSON data as argument for milestones.");
				}
				if (count($milestones) > 0) {
					foreach ($milestones as $key => $milestone) {
						$tasks = array();
						foreach ($milestone["tasks"] as $task) {
							$tasks[] = new Task($task["project_id"], $task["name"], $task["description"], new Money($task["bounty"]), $task["maxAssignees"], $task["assignees"], $task["completed"], $task["paid"]);
						}
						$this->milestones[$key] = new Milestone($milestone["project_id"], $milestone["name"], $milestone["due_date"], $milestone["team_lead_id"], $milestone["status"], new Money($milestone["revenue"]), new Money($milestone["amount_paid"]), new Money($milestone["team_lead_pay"]), new Money($milestone["team_lead_pay_running"]), $tasks, $milestone["description"]);
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getMilestonesJSON() {
		$milestonesJSON = array();
		foreach ($this->milestones as $key => $milestone) {
			$milestonesJSON[$key] = json_decode(json_encode($milestone), true);
		}
		return json_encode($milestonesJSON, JSON_PRETTY_PRINT);
	}

	/**
	 * @param string $name
	 *
	 * @return Milestone    the specified milestone
	 */
	public function getMilestone($name) {
		if (array_key_exists($name, $this->milestones)) {
			return $this->milestones[$name];
		}
		throw new OutOfBoundsException("Attempt to retrieve non-existent milestone.");
	}

	public function addMilestone(Milestone $milestone) {
		if (is_null($milestone) || !$milestone) {
			throw new InvalidArgumentException("Attempt to add invalid milestone.");
		}
		if (array_key_exists($milestone->getName(), $this->milestones)) {
			throw new InvalidArgumentException("Attempt to add an existing milestone.");
		}
		$this->milestones[strtolower($milestone->getName())] = $milestone;
	}

	public function deleteMilestone($name) {
		$name = strtolower($name);
		if (array_key_exists($name, $this->milestones)) {
			unset($this->milestones[$name]);
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($text) {
		if (Validate::plainText($text, true)) {
			$this->description = $text;
		} else {
			throw new InvalidArgumentException("Invalid description supplied as argument.");
		}
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
		return $this->getName();
	}

	private static $assetsDirectory = "files/projects/assets";

	public function getAssetsDirectoryList() {
		if (!$this->pid) {
			throw new BadMethodCallException("Attempt to access files for nonexistent record.");
		}
		$dir = self::$assetsDirectory . "/" . $this->pid;
		if (file_exists($dir) && is_dir($dir)) {
			return new FileDirectory($dir);
		} else {
			if (file_exists(self::$assetsDirectory) && is_dir(self::$assetsDirectory) && is_writable(self::$assetsDirectory)) {
				$success = mkdir($dir, 0775);
				if (!$success) {
					throw new InvalidArgumentException("Assets directory cannot be accessed.");
				} else {
					return new FileDirectory($dir);
				}
			} else {
				throw new InvalidArgumentException("Assets directory cannot be accessed.");
			}

		}
	}

}