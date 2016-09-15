<?php

/**
 * DB class
 *
 * A static class providing easy access to database functionality.
 * Database credentials and error checking are kept in one place.
 *
 * The class uses PDO and it supports prepared statements and transactions.
 * It is highly recommended to use prepared statements in all cases where user input is used.
 * In cases where the query is entirely hard-coded, prepared statements are not necessary
 * but may still be of interest.
 */
class DB {

	private static $database;
	private static $connection = NULL;

	private function __construct() {}
	private function __clone() {}

	/**
	 * Get a new PDO instance with a connection to the database
	 *
	 * @param string $database
	 *
	 * @return PDO
	 */
	public static function getHandle($database = DB_NAME) {
		if ($database !== self::$database) {
			self::close();
			self::$database = $database;
			self::open($database);
		}
		return self::$connection;
	}

	private static function open($database) {
		try {
			self::$connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $database . ";charset=utf8", DB_USER, DB_PASS);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new RuntimeException("Unable to initiate database connection.");
		}
	}

	private static function close() {
		self::$connection = NULL;
	}
	
}
