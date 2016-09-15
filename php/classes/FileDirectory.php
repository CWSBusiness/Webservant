<?php

class FileDirectory implements SeekableIterator, Countable {

	private $directory;
	// $files = SplFileObject[]     An array containing the files in $directory
	private $files = array();

	private $seekableIteratorPosition;

	public function __construct($directory) {
		// Make sure $directory exists and is a directory
		if (!file_exists($directory)) {
			throw new RuntimeException("Directory " . $directory . " does not exist.");
		} else if (!is_dir($directory)) {
			throw new RuntimeException($directory . " is not a valid directory.");
		}
		// Read through the directory
		if (substr($directory, -1) != DIRECTORY_SEPARATOR) {
			$directory = $directory . DIRECTORY_SEPARATOR;
		}
		$this->directory = $directory;
		foreach (glob($directory . "*") as $file) {
			$file = basename($file);
			if ($file != "." && $file != "..") {
				$this->files[] = $directory . $file;
			}
		}
		natcasesort($this->files);
		$this->files = array_values($this->files);
	}

	public function getDirectory() {
		return $this->directory;
	}

	public function setDirectory($directory) {
		$new = new FileDirectory($directory);
		$this->directory = $directory;
		$this->files = $new->files;
	}

	public function count() {
		return count($this->files);
	}

	public function current() {
		return $this->files[$this->seekableIteratorPosition];
	}

	public function next() {
		$this->seekableIteratorPosition++;
	}

	public function key() {
		return $this->seekableIteratorPosition;
	}

	public function valid() {
		return isset($this->files[$this->seekableIteratorPosition]);
	}

	public function rewind() {
		$this->seekableIteratorPosition = 0;
	}

	public function seek($position) {
		if (!isset($this->files[$position])) {
			throw new OutOfBoundsException("Invalid seek position (" . $position . ")");
		}
		$this->seekableIteratorPosition = $position;
	}
}