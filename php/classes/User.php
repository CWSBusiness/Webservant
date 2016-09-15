<?php

class User {
	
	private $pid;
	private $username;
	private $email;
	private $employee;
	private $admin_status;      // 0 for regular account, 1 for admin, 2 for superadmin
	private $last_login;

	private static $currentUser = NULL;

	private function __construct(array $row) {

		if (isset($row["pid"])) {
			$this->setPID($row["pid"]);
		} else {
			throw new InvalidArgumentException("User ID missing from constructor.");
		}

		if (isset($row["username"])) {
			$this->setUsernamePrivate($row["username"]);
		} else {
			throw new InvalidArgumentException("Username missing from constructor.");
		}

		if (isset($row["email"])) {
			$this->setEmail($row["email"]);
		} else {
			throw new InvalidArgumentException("Email address missing from constructor.");
		}

		if (isset($row["employee_id"])) {
			if ($row["employee_id"] == 0) {
				$this->setEmployeeID(0);
			} else {
				try {
					$temp = Employee::withID($row["employee_id"]);
					$this->setEmployeeID($row["employee_id"]);
				} catch (OutOfBoundsException $e) {
					$this->setEmployeeID(0);
				}
			}
		} else {
			throw new InvalidArgumentException("Employee ID or equivalent missing from constructor.");
		}

		if (isset($row["admin_status"])) {
			$this->setAdminStatus($row["admin_status"]);
		} else {
			throw new InvalidArgumentException("Admin status missing from constructor.");
		}
			
	}

	/**
	 * Get a User object by username
	 *
	 * @param string $username              the username to look up
	 *
	 * @throws InvalidArgumentException     if an invalid username is supplied
	 * @throws OutOfBoundsException         if a valid but nonexistent user is supplied
	 *
	 * @return User                         the User, as requested by username
	 */
	public static function withUsername($username) {
		if (!Validate::username($username)) {
			throw new InvalidArgumentException("Invalid username supplied to constructor.");
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT pid, username, email, employee_id, admin_status FROM users WHERE username = :username");
			$stmt->bindParam(":username", $username);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return new self($result);
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve user by username.");
		}
	}

	/**
	 * Get a User object by user ID
	 *
	 * @param int $id                       the user's PID to look up
	 *
	 * @throws InvalidArgumentException     if an invalid user ID is supplied
	 *
	 * @return User                         the User, as requested by ID
	 */
	public static function withID($id) {
		if (is_int($id) || ctype_digit($id)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT pid, username, email, employee_id, admin_status FROM users WHERE pid = :pid");
				$stmt->bindParam(":pid", $id);
				$stmt->execute();
				$result = $stmt->fetch();
				if ($result === false) {
					throw new PDOException();
				}
				return new self($result);
			} catch (PDOException $e) {
				throw new Exception("Unable to retrieve user by user ID.");
			}
		}
		throw new InvalidArgumentException("Expected int for user ID, got " . gettype($id) . " instead.");
	}

	/**
	 *
	 * Get a User object with a row of SQL data
	 *
	 * @param array $row    the row of data to use
	 *
	 * @return User         the User, as specified by the data provided
	 */
	private static function withRow(array $row) {
		return new self($row);
	}

	/**
	 * Get a User object by email address
	 *
	 * @param string $email                 the email address to look up
	 *
	 * @throws InvalidArgumentException     if an invalid email address is supplied
	 * @throws OutOfBoundsException         if a valid but nonexistent email address is supplied
	 *
	 * @return User                         the User, as requested by email address
	 */
	public static function withEmail($email) {
		if (!Validate::email($email)) {
			throw new InvalidArgumentException("Invalid email address supplied to constructor.");
		}
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT pid, username, email, employee_id, admin_status FROM users WHERE email = :email");
			$stmt->bindParam(":email", $email);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return new self($result);
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve user by email address.");
		}
	}

	/**
	 * Get the current (logged-in) user as a User object
	 *
	 * @return null|User    the current User, or NULL if not currently logged in
	 */
	public static function current() {
		if (is_null(self::$currentUser)) {
			if (isset($_SESSION['user_id'])) {
				self::$currentUser = self::withID($_SESSION['user_id']);
			}
		}
		return self::$currentUser;
	}

	/**
	 * Get all site users as an array of User objects
	 *
	 * @return User[]        the array of Users
	 */
	public static function getAll() {
		$userObjs = array();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->query("SELECT pid, username, email, employee_id, admin_status FROM users ORDER BY username ASC");
			$results = $stmt->fetchAll();
			if ($results === false) {
				throw new PDOException();
			}
			foreach ($results as $user) {
				$userObjs[] = User::withRow($user);
			}
			return $userObjs;
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve user list.");
		}
	}

	/**
	 * Create a new User and a corresponding database record
	 *
	 * @param string $username              the account username
	 * @param string $email                 the account email address
	 * @param string $password              the account password
	 * @param int $admin (optional)         the account admin status (0 if omitted)
	 *
	 * @throws InvalidArgumentException     if any of the above parameters are invalid
	 *
	 * @return User                         the newly created User
	 */
	public static function create($username, $email, $password, $admin = 0) {
		$reset_key = Auth::getNewResetKey();
		if (!Validate::username($username)) {
			throw new InvalidArgumentException("Invalid username supplied as argument.");
		}
		if (!Validate::email($email)) {
			throw new InvalidArgumentException("Invalid email address supplied as argument.");
		}
		if (!Validate::password($password)) {
			throw new InvalidArgumentException("Invalid password supplied as argument.");
		}
		if (!is_int($admin)) {
			throw new InvalidArgumentException("Expected int for admin status, got " . gettype($admin) . " instead.");
		}
		$hashPass = Auth::hash($password);

		try {
			$pdo  = DB::getHandle();
			$stmt = $pdo->prepare("INSERT INTO users (pid, username, email, employee_id, admin_status, pass, reset_key) VALUES (NULL, :username, :email, 0, :admin, :pass, :resetkey)");
			$stmt->bindParam(":username", $username);
			$stmt->bindParam(":email", $email);
			$stmt->bindParam(":admin", $admin);
			$stmt->bindParam(":pass", $hashPass);
			$stmt->bindParam(":resetkey", $reset_key);
			$stmt->execute();
			return self::withID($pdo->lastInsertId());
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Save changes made to the User back into the database
	 *
	 * @return bool         true on success, false on failure
	 */
	public function save() {
		$reset_key = Auth::getNewResetKey();
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, employee_id = :employee, admin_status = :admin, reset_key = :resetkey WHERE pid = :pid");
			$stmt->bindParam(":username", $this->username);
			$stmt->bindParam(":email", $this->email);
			$stmt->bindParam(":employee", $this->employee);
			$stmt->bindParam(":admin", $this->admin_status);
			$stmt->bindParam(":resetkey", $reset_key);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Delete the User from the database
	 *
	 * @return bool         true on success, false on failure
	 */
	public function delete() {
		if (!isset($this->pid) || $this->pid == 0) {
			throw new BadMethodCallException("Attempt to delete nonexistent record.");
		}
		try {
			$pdo = DB::getHandle();
			$pdo->beginTransaction();
			$stmt = $pdo->prepare("DELETE FROM notes WHERE created_by = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$stmt = $pdo->prepare("DELETE FROM users WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$pdo->commit();
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Get the User's password reset key
	 *
	 * @return string the alphanumeric password reset key
	 * @throws Exception
	 */
	public function getResetKey() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT reset_key FROM users WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return $result["reset_key"];
		} catch (PDOException $e) {
			throw new Exception("Unable to retrieve reset key.");
		}
	}

	/**
	 * Get the User's primary ID
	 *
	 * @return int      The user ID of the User
	 */
	public function getPID() {
		return $this->pid;
	}

	private function setPID($pid) {
		if (is_int($pid)) {
			$this->pid = $pid;
		} else {
			try {
				$pid = (int) $pid;
				$this->pid  = $pid;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for user ID, got " . gettype($pid) . " instead.");
			}
		}
	}

	/**
	 * Get the User's username
	 *
	 * @return string       The username of the User
	 */
	public function getUsername() {
		return $this->username;
	}

	private function setUsernamePrivate($username) {
		if (Validate::username($username)) {
			$this->username = $username;
		} else {
			throw new InvalidArgumentException("Please supply a valid username.");
		}
	}

	/**
	 * Set the User's username, performing checks to make sure it has changed and is not already in use
	 *
	 * @param string $username              The username to be used
	 *
	 * @throws InvalidArgumentException     if the username chosen is already taken
	 */
	public function setUsername($username) {
		if (Validate::username($username)) {
			if ($this->username == $username) {
				throw new InvalidArgumentException("Please choose a new username.");
			}
			if (!User::usernameAvailable($username)) {
				throw new InvalidArgumentException("The username you have chosen is already taken.");
			}
			$this->username = $username;
		} else {
			throw new InvalidArgumentException("Please supply a valid username.");
		}
	}

	/**
	 * Determine if a username is currently in use by another user
	 *
	 * @param string $username              the username to check for availability
	 *
	 * @throws InvalidArgumentException     if the username provided is invalid
	 *
	 * @return bool                         true if available, or false if taken
	 */
	public static function usernameAvailable($username) {
		if (!Validate::username($username)) {
			throw new InvalidArgumentException("Invalid username supplied as argument.");
		}

		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = :username");
			$stmt->bindParam(":username", $username);
			$stmt->execute();
			$result = $stmt->fetch();
			return ($result === false);
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Get the User's password
	 *
	 * @return string               the user's current password
	 */
	public function getPassword() {
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("SELECT pass FROM users WHERE pid = :pid");
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
			$result = $stmt->fetch();
			if ($result === false) {
				throw new PDOException();
			}
			return $result["pass"];
		} catch (PDOException $e) {
			return false;
		}
	}

	/**
	 * Set the User's password
	 *
	 * @param string $pass          The password to use
	 *
	 * @throws InvalidArgumentException     If the password provided is invalid
	 * @throws Exception                    If a connection error occurred
	 */
	public function setPassword($pass) {
		if (Validate::password($pass)) {
			$reset_key = Auth::getNewResetKey();
			$pass = Auth::hash($pass);
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("UPDATE users SET pass = :pass, reset_key = :resetkey WHERE pid = :pid");
				$stmt->bindParam(":pass", $pass);
				$stmt->bindParam(":resetkey", $reset_key);
				$stmt->bindParam(":pid", $this->pid);
				$stmt->execute();
			} catch (PDOException $e) {
				throw new Exception("Unable to save password.");
			}
		} else {
			throw new InvalidArgumentException("Invalid password supplied as argument.");
		}
	}

	/**
	 * Get the User's email address
	 *
	 * @return string       The User's email address
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Get the User's email address
	 *
	 * @param string $email         The email address to use
	 *
	 * @throws InvalidArgumentException     if the email address provided is invalid
	 */
	public function setEmail($email) {
		if (Validate::email($email)) {
			$this->email = $email;
		} else {
			throw new InvalidArgumentException("Invalid email address supplied as argument.");
		}
	}

	/**
	 * Check if the User is linked to an employee record
	 *
	 * @return bool         true if the User is an employee, or false otherwise
	 */
	public function isEmployee() {
		return ($this->employee) ? true : false;
	}

	/**
	 * Get the current User's employee ID
	 *
	 * @return int          the employee ID if the user is an employee, or 0 if not
	 */
	public function getEmployeeID() {
		return ($this->employee) ? $this->employee : 0;
	}

	/**
	 * Set the current User's employee ID
	 *
	 * @param int $id       the employee ID to be used
	 *
	 * @throws InvalidArgumentException     if the employee ID provided is invalid
	 */
	public function setEmployeeID($id) {
		if (is_int($id)) {
			$this->employee = $id;
		} else {
			try {
				$id = (int) $id;
				if ($id == false) {
					$this->employee = 0;
				} else {
					$this->employee = $id;
				}
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for employee ID, got " . gettype($id) . " instead.");
			}
		}
	}

	/**
	 * Determine whether the User is at admin status or higher
	 *
	 * @return bool         true if the User is at least at admin status, or false otherwise
	 */
	public function isAdmin() {
		if ($this->admin_status > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Determine whether the User is at superadmin status or higher
	 *
	 * @return bool         true if the User is at least at superadmin status, or false otherwise
	 */
	public function isSuperAdmin() {
		if ($this->admin_status > 1) {
			return true;
		}
		return false;
	}

	/**
	 * Get the User's admin status as an integer
	 *
	 * @return int      the User's admin status code
	 */
	public function getAdminStatus() {
		return $this->admin_status;
	}

	/**
	 * Set the User's admin status as an integer
	 *
	 * @param int $val      the admin status code to use
	 */
	public function setAdminStatus($val) {
		if (is_int($val)) {
			if ($val < 0) {
				$val = 0;
			}
			$this->admin_status = $val;
		} else {
			try {
				$val = (int) $val;
				if ($val < 0) {
					$val = 0;
				}
				$this->admin_status  = $val;
			} catch (Exception $e) {
				throw new InvalidArgumentException("Expected int for admin status, got " . gettype($val) . " instead.");
			}
		}
	}

	public static function adminStatusValues() {
		return array(
			0 => "Regular",
			1 => "Admin",
			2 => "Superadmin"
		);
	}

	/**
	 * Get the User's admin status as a string representation of the integer code
	 *
	 * @return string       the admin status string
	 */
	public function getAdminStatusText() {
		$values = self::adminStatusValues();
		if (array_key_exists($this->admin_status, $values)) {
			return $values[$this->admin_status];
		}
		return "Unknown";
	}

	public function getLastLogin() {
		if (!isset($this->last_login)) {
			try {
				$pdo = DB::getHandle();
				$stmt = $pdo->prepare("SELECT last_login FROM users WHERE pid = :pid");
				$stmt->bindParam(":pid", $this->pid);
				$stmt->execute();
				$result = $stmt->fetch();
				if (!$result) {
					return false;
				}
			} catch (PDOException $e) {
				return false;
			}
			$lastLogin = $result["last_login"];
			if ($lastLogin == 0) {
				$this->last_login = false;
				return false;
			} else {
				$this->last_login = DateTime::createFromFormat(Format::MYSQL_TIMESTAMP_FORMAT, $lastLogin);
			}
		}
		return ($this->last_login) ? clone $this->last_login : false;
	}

	public function updateLoginTimestamp() {
		$this->last_login = new DateTime();
		$now = $this->last_login->format(Format::MYSQL_TIMESTAMP_FORMAT);
		try {
			$pdo = DB::getHandle();
			$stmt = $pdo->prepare("UPDATE users SET last_login = :lastlogin WHERE pid = :pid");
			$stmt->bindParam(":lastlogin", $now);
			$stmt->bindParam(":pid", $this->pid);
			$stmt->execute();
		} catch (PDOException $e) {
			return false;
		}
		return true;
	}

	public function __toString() {
		return $this->getUsername();
	}
	
}