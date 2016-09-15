<?php


/**
 * Class ActiveRecordAbstract
 *
 * A class to be extended when implementing the active record pattern.
 *
 * The child class will be stored in a database indexed by a primary ID, or PID.
 * It must implement the insert(), update(), and delete() methods.
 *
 * insert() and update() are never called directly; save() is called in all cases and will in turn
 * call insert() or update() depending on whether the record's PID has been set yet. Thus, insert()
 * should finish by setting the instance's PID to the record's newly-created value in the database.
 */
abstract class ActiveRecordAbstract {

	protected $pid;


	protected abstract function insert();

	protected abstract function update();

	public function save() {
		if (!isset($this->pid) || $this->pid == 0) {
			return $this->insert();
		}
		return $this->update();
	}

	abstract function delete();

	public function getPID() {
		return $this->pid;
	}

	protected function setPID($id) {
		if (is_int($id)) {
			$this->pid = $id;
		} else {
			try {
				$id = (int) $id;
				$this->pid  = $id;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for primary ID, got " . gettype($id) . " instead.");
			}
		}
	}

}