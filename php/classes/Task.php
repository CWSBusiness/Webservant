<?php

class Task implements JsonSerializable {

	private $project_id;
	private $name;
	private $description;
	private $maxAssignees;
	private $assignees = array();
	private $bounty;
	private $completed;

	public function __construct($projectID, $name, $description, Money $bounty, $maxAssignees, $assignees = array(), $completed = false, $paid = false) {

		$this->setProjectID($projectID);
		$this->setName($name);
		$this->setDescription($description);
		$this->setBounty($bounty);
		$this->setMaxAssignees($maxAssignees);
		$this->setAssignees($assignees);
		$this->completed = ($completed) ? true : false;
		if ($bounty->isZero()) {
			$this->paid = true;
		} else {
			$this->paid = ($paid) ? true : false;
		}
	}

	public static function withJSON($string) {
		$array = json_decode($string);
		if (!isset($array["paid"])) {
			$array["paid"] = false;
		}
		return new self($array['project_id'], $array['name'], $array['description'], new Money($array['bounty']), $array["maxAssignees"], $array['assignees'], $array['completed'], $array["paid"]);
	}

	public function jsonSerialize() {
		return array(
			"project_id" => $this->project_id,
			"name" => $this->name,
			"description" => $this->description,
			"maxAssignees" => $this->maxAssignees,
			"assignees" => $this->assignees,
			"bounty" => $this->bounty->__toString(),
			"completed" => $this->completed,
			"paid" => $this->paid,
		);
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
			throw new InvalidArgumentException("Invalid task name supplied as argument.");
		}
	}

	/**
	 * @return int
	 */
	public function getProjectID() {
		return $this->project_id;
	}

	private function setProjectID($id) {
		if (is_int($id)) {
			$this->project_id = $id;
		} else {
			try {
				$id = (int) $id;
				$this->project_id = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for task ID, got " . gettype($id) . " instead.");
			}
		}
	}

	public function getPaidStatus() {
		return $this->paid;
	}

	public function setPaidStatus($bool) {
		$this->paid = ($bool) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isCompleted() {
		return $this->completed;
	}

	public function check() {
		$this->completed = true;
	}

	public function uncheck() {
		$this->completed = false;
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
			throw new InvalidArgumentException("Invalid task description supplied as argument.");
		}
	}

	/**
	 * @return Money
	 */
	public function getBounty() {
		return clone $this->bounty;
	}

	public function setBounty(Money $bounty) {
		if (isset($this->bounty) && $this->bounty->isZero()) {
			$this->paid = false;
		}
		if (is_null($bounty)) {
			throw new InvalidArgumentException("Null reference for bounty supplied as argument.");
		}
		$this->bounty = clone $bounty;
	}

	/**
	 * @return int
	 */
	public function getMaxAssignees() {
		return $this->maxAssignees;
	}

	public function setMaxAssignees($num) {
		if (!is_int($num)) {
			try {
				$num = (int) $num;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Invalid max number of assignees supplied as argument.");
			}
		}
		if ($num < 1) {
			throw new InvalidArgumentException("Max assignees must be at least 1.");
		}
		if ($num < count($this->assignees)) {
			throw new InvalidArgumentException("Please manually remove assignees before reducing the max.");
		}
		$this->maxAssignees = $num;
	}

	/**
	 * @return Employee[]
	 */
	public function getAssignees() {
		$employees = array();
		foreach ($this->assignees as $assignee) {
			$employees[] = Employee::withID($assignee);
		}
		return $employees;
	}

	public function setAssignees($assignees) {
		$ids = array();
		foreach ($assignees as $employee) {
			if (!is_int($employee)) {
				try {
					$employee = (int) $employee;
				} catch (Exception $e) {
					throw new InvalidArgumentException("Invalid employee ID supplied to constructor.");
				}
			}
			$ids[] = $employee;
		}
		if (count($ids) > $this->maxAssignees) {
			throw new InvalidArgumentException("Too many employees assigned to task.");
		}
		$this->assignees = $ids;
	}

	public function __toString() {
		return $this->getName();
	}
}