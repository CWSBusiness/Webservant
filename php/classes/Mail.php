<?php

class Mail {

	private static $initialized = false;

	private function __construct() {}
	private function __clone() {}

	const FROM_NAME = "WebServant";
	const FROM_EMAIL = MAILING_EMAIL;
	const REPLYTO_EMAIL = ADMIN_EMAIL;

	private static function initialize() {
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;
	}

	public static function sendPlain($recipient, $subject, $message, $senderName = self::FROM_NAME, $senderEmail = self::FROM_EMAIL) {
		self::initialize();

		if (!Validate::email($recipient)) {
			throw new InvalidArgumentException("Invalid email address provided for recipient.");
		}

		$headers = "From: " . $senderName . " <" . $senderEmail . ">\r\n";
		$headers .= "Reply-To: " . $senderName . " <" . $senderEmail . ">\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

		return mail($recipient, $subject, $message, $headers);

	}

	public static function sendHTML($recipient, $subject, $message, $senderName = self::FROM_NAME, $senderEmail = self::FROM_EMAIL) {
		self::initialize();

		if (!Validate::email($recipient)) {
			throw new InvalidArgumentException("Invalid email address provided for recipient.");
		}

		$headers = "From: " . $senderName . " <" . $senderEmail . ">\r\n";
		$headers .= "Reply-To: " . $senderName . " <" . $senderEmail . ">\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";

		return mail($recipient, $subject, $message, $headers);
	}

	public static function sendPlainWithAttachment($recipient, $subject, $message, $senderName, $senderEmail, $file) {
		return self::sendWithAttachment(false, $recipient, $subject, $message, $senderName, $senderEmail, $file);
	}

	public static function sendHTMLWithAttachment($recipient, $subject, $message, $senderName, $senderEmail, $file) {
		return self::sendWithAttachment(true, $recipient, $subject, $message, $senderName, $senderEmail, $file);
	}

	private static function sendWithAttachment($sendHTML, $recipient, $subject, $message, $senderName, $senderEmail, $attachment) {
		$fileatt = $attachment["tmp_name"]; // path to the file
		$fileatt_name = $attachment["name"]; // filename that will be used for the attachment

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$fileatt_type = finfo_file($finfo, $fileatt);
		finfo_close($finfo);

		$file = fopen($fileatt,'rb');
		$data = fread($file,filesize($fileatt));
		fclose($file);

		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		$headers="From: $senderName <$senderEmail>";
		$headers .= "\nMIME-Version: 1.0\n" .
		            "Content-Type: multipart/mixed;\n" .
		            " boundary=\"{$mime_boundary}\"";
		$email_message = "This is a multi-part message in MIME format.\n\n" .
		                 "--{$mime_boundary}\n";
		if ($sendHTML) {
			$email_message .= "Content-Type:text/html; charset=\"iso-8859-1\"\n";
		} else {
			$email_message .= "Content-Type:text/plain; charset=\"iso-8859-1\"\n";
		}
		$email_message .= "Content-Transfer-Encoding: 7bit\n\n" . $message;
		$email_message .= "\n\n";
		$data = chunk_split(base64_encode($data));
		$email_message .= "--{$mime_boundary}\n" .
		                  "Content-Type: {$fileatt_type};\n" .
		                  " name=\"{$fileatt_name}\"\n" .
		                  "Content-Transfer-Encoding: base64\n\n" .
		                  $data . "\n\n" .
		                  "--{$mime_boundary}--\n";

		return mail($recipient, $subject, $email_message, $headers);
	}

}