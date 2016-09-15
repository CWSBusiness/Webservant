<?php

class Client extends ActiveRecordAbstract {

	private $client_id;
	private $company_name;
	private $first_name;
	private $last_name;
	private $position;
	private $current_contact;
	private $phone;
	private $email;
	private $notes = "";

	public function __construct($clientID, $companyName, $firstName, $lastName, $position, $currentContact = true, $phone = "", $email = "", $notes = "") {
		$this->setCompanyID($clientID);
		$this->setCompanyName($companyName);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setPosition($position);
		$this->current_contact = ($currentContact) ? true : false;
		$this->setPhone($phone);
		$this->setEmail($email);
		$this->setNotes($notes);
	}

	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM clients WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new OutOfBoundsException("Nonexistent primary ID supplied to constructor.");
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid primary ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for primary ID, got " . gettype($id) . " instead.");
	}

	private static function withRow(array $row) {
		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Client primary ID missing from constructor.");
		}
		if (!isset($row["client_id"])) {
			throw new InvalidArgumentException("Client company ID missing from constructor.");
		}
		if (!isset($row["company_name"])) {
			throw new InvalidArgumentException("Company name missing from constructor.");
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
		if (!isset($row["technical_contact"])) {
			throw new InvalidArgumentException("Technical contact status missing from constructor.");
		}
		if (!isset($row["phone"])) {
			$row["phone"] = "";
		}
		if (!isset($row["email"])) {
			$row["email"] = "";
		}
		if (!isset($row["notes"])) {
			$row["notes"] = "";
		}

		$temp = new self($row["client_id"], $row["company_name"], $row["first_name"], $row["last_name"], $row["position"], $row["technical_contact"], $row["phone"], $row["email"], $row["notes"]);
		$temp->setPID($row["pid"]);

		return $temp;
	}

	/**
	 * @return Client[]
	 */
	public static function getAll() {
		$clientObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT * FROM clients ORDER BY last_name ASC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $client) {
				$clientObjs[] = Client::withRow($client);
			}
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve client list.");
		}
		return $clientObjs;
	}

	protected function insert() {
		$currentContact = ($this->current_contact) ? 1 : 0;
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO clients (pid, client_id, company_name, first_name, last_name, position, technical_contact, phone, email, notes) VALUES (NULL, :clientid, :companyname, :firstname, :lastname, :position, :currentcontact, :phone, :email, :notes)");
			$stmt->bindParam(":clientid", $this->client_id);
			$stmt->bindParam(":companyname", $this->company_name);
			$stmt->bindParam(":firstname", $this->first_name);
			$stmt->bindParam(":lastname", $this->last_name);
			$stmt->bindParam(":position", $this->position);
			$stmt->bindParam(":currentcontact", $currentContact);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":email", $this->email);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		$currentContact = ($this->current_contact) ? 1 : 0;
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE clients SET client_id = :clientid, company_name = :companyname, first_name = :firstname, last_name = :lastname, position = :position, technical_contact = :currentcontact, phone = :phone, email = :email, notes = :notes WHERE pid = :pid");
			$stmt->bindParam(":clientid", $this->client_id);
			$stmt->bindParam(":companyname", $this->company_name);
			$stmt->bindParam(":firstname", $this->first_name);
			$stmt->bindParam(":lastname", $this->last_name);
			$stmt->bindParam(":position", $this->position);
			$stmt->bindParam(":currentcontact", $currentContact);
			$stmt->bindParam(":phone", $this->phone);
			$stmt->bindParam(":email", $this->email);
			$stmt->bindParam(":notes", $this->notes);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function delete() {
		if (!isset($this->pid)) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		} else if ($this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete permanent record.");
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM clients WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * @return string[]
	 */
	public static function getAllCompanies() {
		$results = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT client_id, company_name FROM clients");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			$companies = array();
			foreach ($results as $client) {
				$companies[$client["client_id"]] = $client["company_name"];
			}
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve company list.");
		}
		uasort($companies, function($a, $b) {
			return strcasecmp($a, $b);
		});
		return $companies;
	}

	public static function getCompanyNameByID($id) {
		if (!is_int($id)) {
			try {
				$id = (int) $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for client company ID, got " . gettype($id) . " instead.");
			}
		}
		if ($id === 0) {
			return COMPANY_NAME;
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT company_name FROM clients WHERE client_id = :clientid LIMIT 1");
			$stmt->bindParam(":clientid", $id);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new OutOfBoundsException("Invalid company ID supplied as argument.");
			}
			return $result["company_name"];
		} catch (PDOException $e) {
			throw new OutOfBoundsException("Nonexistent company ID supplied as argument.");
		}
	}

	/**
	 * @return int
	 */
	public function getCompanyID() {
		return $this->client_id;
	}

	public function setCompanyID($id) {
		if (is_int($id)) {
			$this->client_id = $id;
		} else {
			try {
				$id = (int) $id;
				$this->client_id  = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for client company ID, got " . gettype($id) . " instead.");
			}
		}
	}

	// finds out the current highest client company ID, and increments it to produce a "fresh" company ID
	public static function getNewCompanyID() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT MAX(client_id) AS max FROM clients");
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return (int) $result["max"] + 1;
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve company ID list.");
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Client[]
	 */
	public static function getClientsByCompanyID($id) {
		if (!is_int($id)) {
			try {
				$id = (int) $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for client company ID, got " . gettype($id) . " instead.");
			}
		}
		if ($id === 0) {
			return array();
		}
		$clientObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = :clientid ORDER BY last_name ASC");
			$stmt->bindParam(":clientid", $id);
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $client) {
				$clientObjs[] = Client::withRow($client);
			}
		} catch (PDOException $e) {
			throw new OutOfBoundsException("Non-existent company ID supplied as argument.");
		}
		usort($clientObjs, function($a, $b) {
			if ($a->isCurrentContact()) {
				if ($b->isCurrentContact()) {
					return strcasecmp($a->getLastName() . " " . $a->getFirstName(), $b->getLastName() . " " . $b->getFirstName());
				} else {
					return -1;
				}
			} else {
				if ($b->isCurrentContact()) {
					return 1;
				} else {
					return strcasecmp($a->getLastName() . " " . $a->getFirstName(), $b->getLastName() . " " . $b->getFirstName());
				}
			}
		});
		return $clientObjs;
	}

	/**
	 * @return string
	 */
	public function getCompanyName() {
		return $this->company_name;
	}

	// also sets the name for all clients with a matching company ID, and saves instantly
	public static function setCompanyNameForAll($id, $name) {
		if (!is_int($id)) {
			try {
				$id = (int) $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for client company ID, got " . gettype($id) . " instead.");
			}
		}
		if (!Validate::plainText($name)) {
			throw new InvalidArgumentException("Invalid company name supplied as argument.");
		}
		foreach (self::getClientsByCompanyID($id) as $client) {
			$client->setCompanyName($name);
			$client->save();
		}
	}

	public function setCompanyName($name) {
		if (Validate::plainText($name)) {
			$this->company_name = $name;
		} else {
			throw new InvalidArgumentException("Invalid company name supplied as argument.");
		}
	}

	/**
	 * @return string
	 */
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

	/**
	 * @return string
	 */
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

	/**
	 * @return string
	 */
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
	 * @return bool
	 */
	public function isCurrentContact() {
		return ($this->current_contact) ? true : false;
	}

	public function activate() {
		$this->current_contact = true;
	}

	public function deactivate() {
		$this->current_contact = false;
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	public function setPhone($phone) {
		if (!$phone) {
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
	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		if (!$email) {
			$this->email = "";
		} else if (Validate::email($email)) {
			$this->email = $email;
		} else {
			throw new InvalidArgumentException("Invalid email address supplied as argument.");
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

	/**
	 * @return Project[]
	 */
	public function getProjects() {
		$projects = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT pid, technical_contacts FROM projects");
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$contacts = json_decode($row["technical_contacts"]);
				for ($i = 0; $i < count($contacts); $i++) {
					if ($contacts[$i] == $this->pid) {
						$projects[] = Project::withID($row["pid"]);
					}
				}
			}
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to retrieve client project list.");
		}
		return $projects;
	}

	public function __toString() {
		return $this->first_name . " " . $this->last_name;
	}

}