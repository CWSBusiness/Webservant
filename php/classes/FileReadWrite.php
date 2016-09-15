<?php

class FileReadWrite {

	private static function readFileGeneric($directory, $filenameWithoutExtension, array $possibleExtensions) {
		$filePathWithoutExtension = $directory . "/" . $filenameWithoutExtension;
		foreach ($possibleExtensions as $extension) {
			$thisPath = $filePathWithoutExtension . "." . $extension;
			if (file_exists($thisPath)) {
				return $thisPath;
			}
		}
		return false;
	}

	private static $fileExtensionsPDF = array("pdf", "PDF");

	public static function readPDF($directory, $filenameWithoutExtension) {
		return self::readFileGeneric($directory, $filenameWithoutExtension, self::$fileExtensionsPDF);
	}

	public static function writePDF($newFile, $directory, $filenameWithoutExtension, $maxSize = MAX_FILESIZE) {
		$filePathWithoutExtension = $directory . "/" . $filenameWithoutExtension;
		// check for write access to the directory
		if (!is_writable($directory)) {
			throw new RuntimeException("Unable to write to the directory.");
		}
		// if file removal explicitly requested
		if ($newFile === false) {
			foreach (self::$fileExtensionsPDF as $extension) {
				$thisPath = $filePathWithoutExtension . "." . $extension;
				if (file_exists($thisPath) && is_writable($thisPath)) {
					unlink($thisPath);
				}
			}
			return true;
		}
		// if the file is undefined, not a $_FILES file, is multiple files, or is corrupt
		if (!is_array($newFile) || !isset($newFile["error"]) || is_array($newFile["error"])) {
			throw new RuntimeException("Invalid file array passed as argument.");
		}
		// check the $_FILES error value
		switch ($newFile["error"]) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new InvalidArgumentException("No file was uploaded.");
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new InvalidArgumentException("Exceeded filesize limit.");
			default:
				throw new RuntimeException("An unknown error occurred uploading the file.");
		}
		// check the MIME type of the file manually
		// (apparently $_FILES["file"]["type"] can't be trusted!)
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$search = $finfo->file($newFile["tmp_name"]);
		if ($search != "application/pdf" && $search != "application/x-pdf") {
			throw new InvalidArgumentException("Invalid file format uploaded.");
		}
		// ensure the file isn't too large
		if ($newFile["size"] > $maxSize) {
			throw new InvalidArgumentException("Exceeded filesize limit.");
		}
		// delete any old remaining file(s?) before overwriting
		self::writePDF(false, $directory, $filenameWithoutExtension, self::$fileExtensionsPDF);
		// upload the new file and return the result
		$newPath = $filePathWithoutExtension . "." . strtolower(pathinfo($newFile["name"], PATHINFO_EXTENSION));
		if (move_uploaded_file($newFile["tmp_name"], $newPath)) {
			return true;
		} else {
			throw new RuntimeException("An unknown error occurred uploading the file.");
		}
	}

	private static $fileExtensionsImage = array("svg", "SVG", "png", "PNG", "jpg", "JPG", "jpeg", "JPEG");

	public static function readImage($directory, $filenameWithoutExtension) {
		return self::readFileGeneric($directory, $filenameWithoutExtension, self::$fileExtensionsImage);
	}

	public static function writeImage($newFile, $directory, $filenameWithoutExtension, $maxSize = MAX_FILESIZE) {
		$filePathWithoutExtension = $directory . "/" . $filenameWithoutExtension;
		// check for write access to the directory
		if (!is_writable($directory)) {
			throw new RuntimeException("Unable to write to the directory.");
		}
		// if file removal explicitly requested
		if ($newFile === false) {
			foreach (self::$fileExtensionsImage as $extension) {
				$thisPath = $filePathWithoutExtension . "." . $extension;
				if (file_exists($thisPath) && is_writable($thisPath)) {
					unlink($thisPath);
				}
			}
			return true;
		}
		// if the file is undefined, not a $_FILES file, is multiple files, or is corrupt
		if (!is_array($newFile) || !isset($newFile["error"]) || is_array($newFile["error"])) {
			throw new RuntimeException("An error occurred uploading the file.");
		}
		// check the $_FILES error value
		switch ($newFile["error"]) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new InvalidArgumentException("No file was uploaded.");
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new InvalidArgumentException("Exceeded filesize limit.");
			default:
				throw new RuntimeException("An unknown error occurred uploading the file.");
		}
		// check the MIME type of the image manually
		// (apparently $_FILES["file"]["type"] can't be trusted!)
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$needle = explode("/", $finfo->file($newFile["tmp_name"]))[0];
		if ($needle != "image") {
			throw new InvalidArgumentException("Invalid image format uploaded.");
		}
		// ensure the image is in the list of allowed formats
		$extension = end(explode(".", $newFile["name"]));
		if (!in_array($extension, self::$fileExtensionsImage)) {
			throw new InvalidArgumentException("Invalid image format uploaded.");
		}
		// ensure the image isn't too large
		if ($newFile["size"] > $maxSize) {
			throw new InvalidArgumentException("Exceeded filesize limit.");
		}
		// delete any old remaining file(s?) before overwriting
		self::writeImage(false, $directory, $filenameWithoutExtension);
		// upload the new file and return the result
		$newPath = $filePathWithoutExtension . "." . strtolower(pathinfo($newFile["name"], PATHINFO_EXTENSION));
		return move_uploaded_file($newFile["tmp_name"], $newPath);
	}

	public static function getFileSize($path, $formatted = false) {
		if (!file_exists($path) || !is_file($path)) {
			throw new RuntimeException("Invalid file path passed as argument.");
		}
		$size = filesize($path);
		if ($formatted) {
			return Format::bytes($size);
		}
		return $size;
	}

	public static function getFileModificationTime($path) {
		return DateTime::createFromFormat("U", filemtime($path));
	}

}