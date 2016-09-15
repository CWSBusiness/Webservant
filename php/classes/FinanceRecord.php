<?php

class FinanceRecord extends ActiveRecordAbstract {

	private $financeType;
	private $projectID;
	private $projectMilestone;
	private $date;
	private $description;
	private $payerID;
	private $payerName;
	private $payeeID;
	private $payeeName;
	private $amount;
	private $amountPaid;
	private $reimbursementStatus;
	private $comments;

	const EXPENSE = 1;
	const INCOMING_PAYMENT = 2;
	const EMPLOYEE_PAYMENT = 3;

	private static $financeTypes = array(
		self::EXPENSE          => "Expense reimbursement",
		self::INCOMING_PAYMENT => "Incoming payment",
		self::EMPLOYEE_PAYMENT => "Employee pay",
	);

	public function __construct($type, $date, $description, $payer, $payee, Money $amount, Money $amountPaid, $payerNeedsReimbursement, $projectID = 0, $comments = "") {
		$this->setTransactionType($type);
		$this->setTransactionDate($date);
		$this->setDescription($description);
		$this->setPayer($payer);
		$this->setPayee($payee);
		$this->setTransactionValue($amount);
		$this->setAmountPaid($amountPaid);
		$this->setReimbursementStatus($payerNeedsReimbursement);
		$this->setProjectID($projectID);
		$this->setComments($comments);
	}

	/**
	 * @param $id
	 *
	 * @return FinanceRecord
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT * FROM finances WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return self::withRow($result);
			} catch (PDOException $e) {
				throw new OutOfBoundsException("Invalid finance record ID supplied to constructor.");
			}
		}
		throw new InvalidArgumentException("Expected int for finance record ID, got " . gettype($id) . " instead.");
	}

	/**
	 * @param array $row
	 *
	 * @return FinanceRecord
	 */
	public static function withRow(array $row) {
		if (!isset($row["pid"])) {
			throw new InvalidArgumentException("Finance record ID missing from constructor.");
		}
		if (!isset($row["finance_type"])) {
			throw new InvalidArgumentException("Finance type code missing from constructor.");
		}
		if (!isset($row["project_id"])) {
			throw new InvalidArgumentException("Project ID code missing from constructor.");
		}
		if (!isset($row["date"])) {
			throw new InvalidArgumentException("Transaction date missing from constructor.");
		}
		if (!isset($row["description"])) {
			throw new InvalidArgumentException("Transaction description missing from constructor.");
		}
		if (isset($row["payer_id"]) && (is_int($row["payer_id"]) || ctype_digit($row["payer_id"])) && (int) $row["payer_id"] > 0) {
			$payer = (int) $row["payer_id"];
		} else if (isset($row["payer_name"])) {
			$payer = $row["payer_name"];
		} else {
			throw new InvalidArgumentException("Payer missing from constructor.");
		}
		if (isset($row["payee_id"]) && (is_int($row["payee_id"]) || ctype_digit($row["payee_id"])) && (int) $row["payee_id"] > 0) {
			$payee = (int) $row["payee_id"];
		} else if (isset($row["payee_name"])) {
			$payee = $row["payee_name"];
		} else {
			throw new InvalidArgumentException("Payee missing from constructor.");
		}
		if (!isset($row["dollar_value"])) {
			throw new InvalidArgumentException("Transaction amount missing from constructor.");
		}
		if (!isset($row["amount_paid"])) {
			$row["amount_paid"] = "0";
		}
		if (!isset($row["reimbursement_status"])) {
			throw new InvalidArgumentException("Reimbursement status missing from constructor.");
		}
		if (!isset($row["comments"])) {
			$row["comments"] = "";
		}

		$temp = new self($row["finance_type"], $row["date"], $row["description"], $payer, $payee, new Money($row["dollar_value"]), new Money($row["amount_paid"]), $row["reimbursement_status"], $row["project_id"], $row["comments"]);
		$temp->setPID($row["pid"]);
		return $temp;
	}

	/**
	 * @return FinanceRecord[]
	 * @throws Exception
	 */
	public static function getAllExpenses() {
		return self::getExpensesPrivate();
	}

	/**
	 * @return FinanceRecord[]
	 * @throws Exception
	 */
	public static function getOutstandingExpenses() {
		return self::getExpensesPrivate(true);
	}

	/**
	 * @param DateTime      $start
	 * @param DateTime|NULL $end
	 *
	 * @return FinanceRecord[]
	 * @throws Exception
	 */
	public static function getExpensesInRange(DateTime $start, DateTime $end = NULL) {
		return self::getExpensesPrivate(false, $start, $end);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function getOldestExpenseDate() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT MIN(date) FROM finances");
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return $result[0];
		} catch (PDOException $e) {
			throw new Exception("Unable to determine the oldest expense.");
		}
	}

	private static function getExpensesPrivate($outstandingOnly = false, DateTime $rangeStart = NULL, DateTime $rangeEnd = NULL) {
		try {
			$pdo = DB::getHandle();
			$query = "SELECT * FROM finances WHERE finance_type = :financetype";
			if ($outstandingOnly) {
				$query .= " AND reimbursement_status = 1";
			}
			if ($rangeStart) {
				$query .= " AND date >= :rangestart";
			}
			if ($rangeEnd) {
				$query .= " AND date <= :rangeend";
			}
			if ($rangeStart) {
				$query .= " ORDER BY date DESC";
			} else {
				$query .= " ORDER BY date ASC";
			}
			$stmt = $pdo->prepare($query);
			$code = self::EXPENSE;
			$stmt->bindParam(":financetype", $code);
			if ($rangeStart) {
				$rangeStart = $rangeStart->format(Format::MYSQL_DATE_FORMAT);
				$stmt->bindParam(":rangestart", $rangeStart);
			}
			if ($rangeEnd) {
				$rangeEnd = $rangeEnd->format(Format::MYSQL_DATE_FORMAT);
				$stmt->bindParam(":rangeend", $rangeEnd);
			}
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			$recordObjs = array();
			foreach ($results as $row) {
				$recordObjs[] = self::withRow($row);
			}
			return $recordObjs;
		} catch (PDOException $e) {
			if ($outstandingOnly) {
				throw new Exception("Unable to retrieve outstanding expenses list.");
			} else {
				throw new Exception("Unable to retrieve expenses list.");
			}
		}
	}

	/**
	 * @return FinanceRecord[]
	 */
	public static function getAllIncomes() {
		return self::getIncomesPrivate();
	}

	/**
	 * @return FinanceRecord[]
	 */
	public static function getOutstandingIncomes() {
		return self::getIncomesPrivate(true);
	}

	/**
	 * @param DateTime      $start
	 * @param DateTime|NULL $end
	 *
	 * @return FinanceRecord[]
	 */
	public static function getIncomesInRange(DateTime $start, DateTime $end = NULL) {
		return self::getIncomesPrivate(false, $start, $end);
	}

	private static function getIncomesPrivate($outstandingOnly = false, DateTime $rangeStart = NULL, DateTime $rangeEnd = NULL) {
		if ($outstandingOnly) {
			$projects = Project::getActive();
		} else {
			$projects = Project::getAll();
		}
		$recordObjs = array();
		foreach ($projects as $project) {
			foreach ($project->getMilestones() as $milestone) {
				$includeEntry = true;
				if ($rangeStart) {
					$dueDate = $milestone->getDueDate();
					if ($rangeEnd) {
						if ($dueDate < $rangeStart || $dueDate > $rangeEnd) {
							$includeEntry = false;
						}
					} else {
						if ($dueDate < $rangeStart) {
							$includeEntry = false;
						}
					}
				}
				if ($milestone->getRevenue() == new Money("0")) {
					$includeEntry = false;
				} else if ($outstandingOnly && $milestone->paidInFull()) {
					$includeEntry = false;
				}

				if ($includeEntry) {
					$temp = new self(self::INCOMING_PAYMENT, $milestone->getDueDate(), $project->getName() . " – " . $milestone->getName(), $project->getCompanyID(), COMPANY_NAME, $milestone->getRevenue(), $milestone->getAmountPaid(), 0, $project->getPID(), "");
					$temp->setProjectMilestone($milestone->getNameForParameter());
					$recordObjs[] = $temp;
				}
			}
		}
		if (!$outstandingOnly) {
			try {
				$pdo   = DB::getHandle();
				$query = "SELECT * FROM finances WHERE finance_type = :financetype";
				if ($rangeStart) {
					$query .= " AND date >= :rangestart";
				}
				if ($rangeEnd) {
					$query .= " AND date <= :rangeend";
				}
				if ($rangeStart) {
					$query .= " ORDER BY date DESC";
				} else {
					$query .= " ORDER BY date ASC";
				}
				$stmt = $pdo->prepare($query);
				$code = self::INCOMING_PAYMENT;
				$stmt->bindParam(":financetype", $code);
				if ($rangeStart) {
					$rangeStart = $rangeStart->format(Format::MYSQL_DATE_FORMAT);
					$stmt->bindParam(":rangestart", $rangeStart);
				}
				if ($rangeEnd) {
					$rangeEnd = $rangeEnd->format(Format::MYSQL_DATE_FORMAT);
					$stmt->bindParam(":rangeend", $rangeEnd);
				}
				$stmt->execute();
				$results = $stmt->fetchAll();
				if ($results === false) {
					throw new PDOException();
				}
				foreach ($results as $row) {
					$recordObjs[] = self::withRow($row);
				}
			} catch (PDOException $e) {
				throw new Exception("Unable to retrieve manual income entries.");
			}
		}
		usort($recordObjs, "self::sortByTransactionDate");
		return $recordObjs;
	}

	/**
	 * @return FinanceRecord[]
	 */
	public static function getAllEmployeePayments() {
		return self::getEmployeePaymentsPrivate();
	}

	/**
	 * @return FinanceRecord[]
	 */
	public static function getOutstandingEmployeePayments() {
		return self::getEmployeePaymentsPrivate(true);
	}

	/**
	 * @param DateTime      $start
	 * @param DateTime|NULL $end
	 *
	 * @return FinanceRecord[]
	 */
	public static function getEmployeePaymentsInRange(DateTime $start, DateTime $end = NULL) {
		return self::getEmployeePaymentsPrivate(false, $start, $end);
	}

	private static function getEmployeePaymentsPrivate($outstandingOnly = false, DateTime $rangeStart = NULL, DateTime $rangeEnd = NULL) {
		$recordObjs = array();
		foreach (Project::getAll() as $project) {
			foreach ($project->getMilestones() as $milestone) {
				$includeMilestone = true;
				if ($rangeStart) {
					$dueDate = $milestone->getDueDate();
					if ($rangeEnd) {
						if ($dueDate < $rangeStart || $dueDate > $rangeEnd) {
							$includeMilestone = false;
						}
					} else {
						if ($dueDate < $rangeStart) {
							$includeMilestone = false;
						}
					}
				}
				if ($includeMilestone) {
					$teamLeadPay = $milestone->getTeamLeadPay();
					if (!$teamLeadPay->getTransactionValue()->isZero()) {
						if ($outstandingOnly) {
							if (!$teamLeadPay->paidInFull()) {
								$recordObjs[] = $teamLeadPay;
							}
						} else {
							$recordObjs[] = $teamLeadPay;
						}
					}
					foreach ($milestone->getTasks() as $task) {
						$includeTask = true;
						if ($outstandingOnly && $task->getPaidStatus()) {
							$includeTask = false;
						}
						$bounty = $task->getBounty();
						if ($bounty->isZero()) {
							$includeTask = false;
						}
						if ($includeTask) {
							$assignees  = $task->getAssignees();
							$payForEach = clone $bounty;
							$payForEach->divide(count($assignees));
							foreach ($assignees as $assignee) {
								$temp = new self(self::EMPLOYEE_PAYMENT, $milestone->getDueDate(), $project->getName() . " – " . $milestone->getName(), COMPANY_NAME, $assignee, $payForEach, ($task->getPaidStatus()) ? $payForEach : new Money("0"), false, $project->getPID(), "");
								$temp->setProjectMilestone($milestone->getNameForParameter());
								$recordObjs[] = $temp;
							}
						}
					}
				}
			}
		}
		try {
			$pdo   = DB::getHandle();
			$query = "SELECT * FROM finances WHERE finance_type = :financetype";
			if ($rangeStart) {
				$query .= " AND date >= :rangestart";
			}
			if ($rangeEnd) {
				$query .= " AND date <= :rangeend";
			}
			if ($rangeStart) {
				$query .= " ORDER BY date DESC";
			} else {
				$query .= " ORDER BY date ASC";
			}
			$stmt = $pdo->prepare($query);
			$code = self::EMPLOYEE_PAYMENT;
			$stmt->bindParam(":financetype", $code);
			if ($rangeStart) {
				$rangeStart = $rangeStart->format(Format::MYSQL_DATE_FORMAT);
				$stmt->bindParam(":rangestart", $rangeStart);
			}
			if ($rangeEnd) {
				$rangeEnd = $rangeEnd->format(Format::MYSQL_DATE_FORMAT);
				$stmt->bindParam(":rangeend", $rangeEnd);
			}
			$stmt->execute();
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $row) {
				$temp = self::withRow($row);
				if ($outstandingOnly) {
					if (!$temp->paidInFull()) {
						$recordObjs[] = $temp;
					}
				} else {
					$recordObjs[] = $temp;
				}
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve manual income entries.");
		}
		usort($recordObjs, "self::sortByTransactionDate");
		return $recordObjs;
	}

	/**
	 * @param $employeeID
	 *
	 * @return FinanceRecord[]
	 */
	public static function getAllExpensesByEmployee($employeeID) {
		return self::getExpensesByEmployeePrivate($employeeID);
	}

	/**
	 * @param $employeeID
	 *
	 * @return FinanceRecord[]
	 */
	public static function getOutstandingExpensesByEmployee($employeeID) {
		return self::getExpensesByEmployeePrivate($employeeID, true);
	}

	/**
	 * @param               $employeeID
	 * @param DateTime      $start
	 * @param DateTime|NULL $end
	 *
	 * @return FinanceRecord[]
	 */
	public static function getExpensesByEmployeeInRange($employeeID, DateTime $start, DateTime $end = NULL) {
		return self::getExpensesByEmployeePrivate($employeeID, false, $start, $end);
	}

	private static function getExpensesByEmployeePrivate($employeeID, $outstandingOnly = false, DateTime $rangeStart = NULL, DateTime $rangeEnd = NULL) {
		$results = self::getExpensesPrivate($outstandingOnly, $rangeStart, $rangeEnd);
		$filter = array();
		foreach ($results as $result) {
			/** @var FinanceRecord $result */
			if ($result->payerID == $employeeID) {
				$filter[] = $result;
			}
		}
		return $filter;
	}

	public static function getAllPaymentsForEmployee($employeeID) {
		return self::getPaymentsByEmployeePrivate($employeeID);
	}

	public static function getOutstandingPaymentsForEmployee($employeeID) {
		return self::getPaymentsForEmployeePrivate($employeeID, true);
	}

	public static function getPaymentsForEmployeeInRange($employeeID, DateTime $start, DateTime $end = NULL) {
		return self::getPaymentsForEmployeePrivate($employeeID, false, $start, $end);
	}

	private static function getPaymentsForEmployeePrivate($employeeID, $outstandingOnly = false, DateTime $rangeStart = NULL, DateTime $rangeEnd = NULL) {
		$results = self::getEmployeePaymentsPrivate($outstandingOnly, $rangeStart, $rangeEnd);
		$filter = array();
		foreach ($results as $result) {
			/** @var FinanceRecord $result */
			if ($result->payeeID == $employeeID) {
				$filter[] = $result;
			}
		}
		return $filter;
	}

	public static function sortByTransactionDate($a, $b) {
		/** @var FinanceRecord $a */
		/** @var FinanceRecord $b */
		// first priority for sorting is transaction date
		if ($a->getTransactionDate() > $b->getTransactionDate()) {
			return 1;
		}
		if ($a->getTransactionDate() < $b->getTransactionDate()) {
			return -1;
		}
		// if the dates are the same, then sort by transaction type
		if ($a->getTransactionType() > $b->getTransactionType()) {
			return 1;
		}
		if ($a->getTransactionType() < $b->getTransactionType()) {
			return -1;
		}
		// if all criteria are equal, consider the entries equal
		return 0;
	}




	protected function insert() {
		$date = Format::date($this->date, Format::MYSQL_DATE_FORMAT);
		$reimbursementStatus = ($this->reimbursementStatus) ? 1 : 0;
		$amountTotal = $this->amount->__toString();
		$amountPaid = $this->amountPaid->__toString();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO finances (pid, finance_type, project_id, date, description, payer_id, payer_name, payee_id, payee_name, dollar_value, amount_paid, reimbursement_status, comments) VALUES (NULL, :financetype, :projectid, :date, :description, :payerid, :payername, :payeeid, :payeename, :dollarvalue, :amountpaid, :reimbursementstatus, :comments)");
			$stmt->bindParam(":financetype", $this->financeType);
			$stmt->bindParam(":projectid", $this->projectID);
			$stmt->bindParam(":date", $date);
			$stmt->bindParam(":description", $this->description);
			$stmt->bindParam(":payerid", $this->payerID);
			$stmt->bindParam(":payername", $this->payerName);
			$stmt->bindParam(":payeeid", $this->payeeID);
			$stmt->bindParam(":payeename", $this->payeeName);
			$stmt->bindParam(":dollarvalue", $amountTotal);
			$stmt->bindParam(":amountpaid", $amountPaid);
			$stmt->bindParam(":reimbursementstatus", $reimbursementStatus);
			$stmt->bindParam(":comments", $this->comments);
			$stmt->execute();
			$this->pid = $pdo->lastInsertId();
			return $this->pid;
		} catch (PDOException $e) {
			return 0;
		}
	}

	protected function update() {
		$date = Format::date($this->date, Format::MYSQL_DATE_FORMAT);
		$reimbursementStatus = ($this->reimbursementStatus) ? 1 : 0;
		$amountTotal = $this->amount->__toString();
		$amountPaid = $this->amountPaid->__toString();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE finances SET finance_type = :financetype, project_id = :projectid, date = :date, description = :description, payer_id = :payerid, payer_name = :payername, payee_id = :payeeid, payee_name = :payeename, dollar_value = :dollarvalue, amount_paid = :amountpaid, reimbursement_status = :reimbursementstatus, comments = :comments WHERE pid = :pid");
			$stmt->bindParam(":financetype", $this->financeType);
			$stmt->bindParam(":projectid", $this->projectID);
			$stmt->bindParam(":date", $date);
			$stmt->bindParam(":description", $this->description);
			$stmt->bindParam(":payerid", $this->payerID);
			$stmt->bindParam(":payername", $this->payerName);
			$stmt->bindParam(":payeeid", $this->payeeID);
			$stmt->bindParam(":payeename", $this->payeeName);
			$stmt->bindParam(":dollarvalue", $amountTotal);
			$stmt->bindParam(":amountpaid", $amountPaid);
			$stmt->bindParam(":reimbursementstatus", $reimbursementStatus);
			$stmt->bindParam(":comments", $this->comments);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return false;
		}
	}

	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		$this->setExpenseReceipt(false);
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("DELETE FROM finances WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getTransactionType() {
		return $this->financeType;
	}

	public function getTransactionTypeText() {
		if (array_key_exists($this->financeType, self::$financeTypes)) {
			return self::$financeTypes[$this->financeType];
		}
		return "Unknown";
	}

	public function setTransactionType($code) {
		if (is_int($code)) {
			$this->financeType = $code;
		} else {
			try {
				$code = (int) $code;
				$this->financeType = $code;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for finance type code, got " . gettype($code) . " instead.");
			}
		}
	}

	public function getProjectID() {
		return $this->projectID;
	}

	public function setProjectID($id) {
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

	public function getProjectMilestone() {
		return $this->projectMilestone;
	}

	public function setProjectMilestone($name) {
		if (Validate::plainText($name)) {
			$this->projectMilestone = $name;
		} else {
			throw new InvalidArgumentException("Invalid project milestone name supplied as argument.");
		}
	}

	public function getTransactionDate() {
		return clone $this->date;
	}

	public function setTransactionDate($date) {
		if ($date instanceof DateTime) {
			$this->date = clone $date;
		} else {
			try {
				$temp = DateTime::createFromFormat(Format::MYSQL_DATE_FORMAT, $date);
				if ($temp === false) {
					throw new InvalidArgumentException("Invalid transaction date supplied as argument.");
				}
				$this->date = clone $temp;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid transaction date supplied as argument.");
			}
		}
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($text) {
		if (Validate::plainText($text)) {
			$this->description = $text;
		} else {
			throw new InvalidArgumentException("Invalid transaction description supplied as argument.");
		}
	}

	public function getPayer() {
		if ($this->payerID > 0) {
			if ($this->financeType == self::EXPENSE) {
				return Employee::withID($this->payerID);
			} else if ($this->financeType == self::INCOMING_PAYMENT) {
				return $this->payerID;
			}
		}
		return $this->payerName;
	}

	public function setPayer($payer) {
		if (is_int($payer) && $payer >= 0) {
			if ($payer == 0) {
				$this->payerID = 0;
				$this->payerName = COMPANY_NAME;
			} else if ($this->financeType == self::EXPENSE) {
				$this->payerID   = $payer;
				$payer           = Employee::withID($payer);
				$this->payerName = $payer->__toString();
			} else if ($this->financeType == self::INCOMING_PAYMENT) {
				$this->payerID   = $payer;
				$this->payerName = Client::getCompanyNameByID($payer);
			}
		} else if ($payer instanceof Employee) {
			$this->payerID = $payer->getPID();
			$this->payerName = $payer->__toString();
		} else if (is_string($payer) && Validate::plainText($payer)) {
			$this->payerID = 0;
			$this->payerName = $payer;
		} else {
			throw new InvalidArgumentException("Invalid payer supplied as argument.");
		}
	}

	public function getPayee() {
		if ($this->payeeID > 0) {
			return Employee::withID($this->payeeID);
		}
		return $this->payeeName;
	}

	public function setPayee($payee) {
		if (is_int($payee) && $payee >= 0) {
			$this->payeeID = $payee;
			if ($payee == 0) {
				$this->payeeName = COMPANY_NAME;
			} else {
				$payee = Employee::withID($payee);
				$this->payeeName = $payee->__toString();
			}
		} else if ($payee instanceof Employee) {
			$this->payeeID = $payee->getPID();
			$this->payeeName = $payee->__toString();
		} else if (is_string($payee) && Validate::plainText($payee)) {
			$this->payeeID = 0;
			$this->payeeName = $payee;
		} else {
			throw new InvalidArgumentException("Invalid payee supplied as argument.");
		}
	}

	/**
	 * @return Money
	 */
	public function getTransactionValue() {
		return clone $this->amount;
	}

	public function setTransactionValue(Money $amount) {
		$this->amount = clone $amount;
	}

	/**
	 * @return Money
	 */
	public function getAmountPaid() {
		return clone $this->amountPaid;
	}

	public function setAmountPaid(Money $amount) {
		$this->amountPaid = clone $amount;
	}

	/**
	 * @return Money
	 */
	public function getAmountOutstanding() {
		$difference = $this->getTransactionValue();
		$difference->subtract($this->getAmountPaid());
		return $difference;
	}

	/**
	 * @return bool
	 */
	public function paidInFull() {
		return ($this->getAmountOutstanding()->isZero()) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function payerNeedsReimbursement() {
		return ($this->reimbursementStatus) ? true : false;
	}

	public function setReimbursementStatus($bool) {
		$this->reimbursementStatus = ($bool) ? true : false;
	}

	/**
	 * @return string
	 */
	public function getComments() {
		return $this->comments;
	}

	public function setComments($text) {
		if (Validate::HTML($text, true)) {
			$this->comments = $text;
		} else {
			throw new InvalidArgumentException("Invalid comments supplied as argument.");
		}
	}

	private static $expenseReceiptDirectory = "files/receipts";

	public function getExpenseReceipt() {
		// if not a stored record or not an expense, return false
		if (!$this->pid || $this->financeType != FinanceRecord::EXPENSE) {
			return false;
		}
		$filenameWithoutExtension = $this->pid;
		return FileReadWrite::readPDF(self::$expenseReceiptDirectory, $filenameWithoutExtension);
	}

	public function setExpenseReceipt($file) {
		// if not a stored record or not an expense, return false
		if (!$this->pid || $this->financeType != FinanceRecord::EXPENSE) {
			return false;
		}
		$filenameWithoutExtension = $this->pid;
		return FileReadWrite::writePDF($file, self::$expenseReceiptDirectory, $filenameWithoutExtension);
	}

}