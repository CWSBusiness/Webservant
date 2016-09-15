<?php

class Milestone implements JsonSerializable {

	private $projectID;
	private $name;
	private $due_date;
	private $teamLeadID;
	private $teamLeadPay;
	private $description;
	private $status;
	private $revenue;
	private $amountPaid;
	private $tasks = array();

	const STATUS_CANCELLED = -1;
	const STATUS_COMPLETED = 0;
	const STATUS_IN_PLANNING = 1;
	const STATUS_ACTIVE = 2;

	/**
	 * @return String[]
	 */
	public static function statusValues() {
		return array(
			self::STATUS_CANCELLED => "Cancelled",
			self::STATUS_COMPLETED => "Completed",
			self::STATUS_IN_PLANNING => "In planning",
			self::STATUS_ACTIVE => "Active development",
		);
	}

	public function __construct($projectID, $name, $dueDate, $teamLeadID, $status, Money $revenue, Money $amountPaid, Money $teamLeadPayAmount, Money $teamLeadPaySoFar, array $tasks = array(), $description = "") {
		$this->setProjectID($projectID);
		$this->setName($name);
		$this->setDueDate($dueDate);
		$this->setTeamLeadID($teamLeadID);
		$this->setStatus($status);
		$this->setRevenue($revenue);
		$this->setAmountPaid($amountPaid);
	 $teamLeadPay = new FinanceRecord(FinanceRecord::EMPLOYEE_PAYMENT, $this->getDueDate(), Project::getProjectNameByID($projectID) . " – " . $name, COMPANY_NAME, $teamLeadID, $teamLeadPayAmount, $teamLeadPaySoFar, false, $projectID);
		$teamLeadPay->setProjectMilestone($this->getNameForParameter());
		$this->setTeamLeadPay($teamLeadPay);
		$this->setTasks($tasks);
		$this->setDescription($description);
	}

	public static function withJSON($json) {
		$array = json_decode($json);
		return new self($array["project_id"], $array["name"], $array["due_date"], $array["team_lead_id"], $array["status"], new Money($array["revenue"]), new Money($array["amount_paid"]), new Money($array["team_lead_pay"]), new Money($array["team_lead_pay_running"]), $array["tasks"], $array["description"]);
	}

	public function jsonSerialize() {
		return array(
			"project_id" => $this->projectID,
			"name" => $this->name,
			"due_date" => Format::date($this->due_date, Format::MYSQL_DATE_FORMAT),
			"team_lead_id" => $this->teamLeadID,
			"status" => $this->status,
			"revenue" => $this->revenue->__toString(),
			"amount_paid" => $this->amountPaid->__toString(),
			"team_lead_pay" => $this->teamLeadPay->getTransactionValue()->__toString(),
			"team_lead_pay_running" => $this->teamLeadPay->getAmountPaid()->__toString(),
			"tasks" => $this->tasks,
			"description" => $this->description,
		);
	}

	/**
	 * @return int
	 */
	public function getProjectID() {
		return $this->projectID;
	}

	private function setProjectID($id) {
		if (is_int($id)) {
			$this->projectID = $id;
		} else {
			try {
				$id = (int) $id;
				$this->projectID = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for project ID, got " . gettype($id) . " instead.");
			}
		}
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
	 * Get the name of the milestone as used in URL parameters
	 *
	 * @return string
	 */
	public function getNameForParameter() {
		return urlencode(strtolower($this->name));
	}

	/**
	 * @return DateTime
	 */
	public function getDueDate() {
		return clone $this->due_date;
	}

	public function setDueDate($date) {
		if ($date instanceof DateTime) {
			$this->due_date = clone $date;
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid due date supplied as argument.");
				}
				$this->due_date = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid due date supplied as argument.");
			}
		}
		$this->due_date->setTime(0, 0, 0);
	}

	public function getTeamLeadID() {
		return $this->teamLeadID;
	}

	public function getTeamLead() {
		try {
			return Employee::withID($this->teamLeadID);
		} catch (Exception $e) {
			throw new OutOfBoundsException($e->getMessage());
		}
	}

	public function setTeamLeadID($id) {
		if (is_int($id)) {
			$this->teamLeadID = $id;
		} else {
			try {
				$id = (int) $id;
				$this->teamLeadID = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for team lead ID, got " . gettype($id) . " instead.");
			}
		}
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
	 * @return Task[]
	 */
	public function getTasks() {
		return $this->tasks;
	}

	private function setTasks(array $tasks) {
		$tempTasks = array();
		foreach ($tasks as $key => $task) {
			if ($task instanceof Task) {
				if (is_null($task)) {
					throw new InvalidArgumentException("Null reference for task supplied as argument.");
				} else {
					$tempTasks[$key] = $task;
				}
			} else {
				throw new InvalidArgumentException("Invalid task supplied as argument.");
			}
		}
		$this->tasks = $tempTasks;
	}

	/**
	 * @param int $id
	 *
	 * @return Task
	 */
	public function getTask($id) {
		if (array_key_exists($id, $this->tasks)) {
			return $this->tasks[$id];
		}
		throw new InvalidArgumentException("Invalid task ID supplied as argument.");
	}

	public function addTask(Task $new) {
		if (is_null($new)) {
			throw new InvalidArgumentException("Null reference for task supplied as argument.");
		}
		$this->tasks[] = $new;
	}

	public function deleteTask($id) {
		if (array_key_exists($id, $this->tasks)) {
			unset($this->tasks[$id]);
		} else {
			throw new InvalidArgumentException("Invalid task ID supplied as argument.");
		}
	}

	/**
	 * @return Money
	 */
	public function getRevenue() {
		return clone $this->revenue;
	}

	public function setRevenue(Money $revenue) {
		if (is_null($revenue)) {
			throw new InvalidArgumentException("Null reference for revenue supplied as argument.");
		}
		$this->revenue = clone $revenue;
	}

	/**
	 * @return Money
	 */
	public function getAmountPaid() {
		return clone $this->amountPaid;
	}

	public function setAmountPaid(Money $amount) {
		if (is_null($amount)) {
			throw new InvalidArgumentException("Null reference for amount paid supplied as argument.");
		}
		$this->amountPaid = clone $amount;
	}

	/**
	 * @return Money
	 */
	public function getAmountOutstanding() {
		$difference = $this->getRevenue();
		$difference->subtract($this->getAmountPaid());
		return $difference;
	}

	public function paidInFull() {
		return $this->getAmountOutstanding()->isZero();
	}

	/**
	 * @return FinanceRecord
	 */
	public function getTeamLeadPay() {
		return clone $this->teamLeadPay;
	}

	public function setTeamLeadPay(FinanceRecord $amount) {
		if (is_null($amount)) {
			throw new InvalidArgumentException("Null reference for team lead pay supplied as argument.");
		}
		$this->teamLeadPay = clone $amount;
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
				throw new InvalidArgumentException("Invalid status code supplied as argument.");
			}
		}
	}

	/**
	 * @return string
	 */
	public function getStatusText() {
		$values = self::statusValues();
		if ($this->status == self::STATUS_COMPLETED && !$this->paidInFull()) {
			return "Awaiting payment";
		}
		if (array_key_exists($this->status, $values)) {
			return $values[$this->status];
		}
		return "Unknown";
	}

	public function __toString() {
		return $this->getName();
	}

	private static $contractDirectory = "files/projects/contracts";
	private static $invoiceDirectory = "files/projects/invoices";

	public function getContractFile() {
		$filenameWithoutExtension = $this->projectID . "_" . $this->getNameForParameter();
		return FileReadWrite::readPDF(self::$contractDirectory, $filenameWithoutExtension);
	}

	public function setContractFile($file) {
		$filenameWithoutExtension = $this->projectID . "_" . $this->getNameForParameter();
		return FileReadWrite::writePDF($file, self::$contractDirectory, $filenameWithoutExtension);
	}

	public function getInvoiceFile() {
		$filenameWithoutExtension = $this->projectID . "_" . $this->getNameForParameter();
		return FileReadWrite::readPDF(self::$invoiceDirectory, $filenameWithoutExtension);
	}

	public function setInvoiceFile($file) {
		$filenameWithoutExtension = $this->projectID . "_" . $this->getNameForParameter();
		return FileReadWrite::writePDF($file, self::$invoiceDirectory, $filenameWithoutExtension);
	}

}