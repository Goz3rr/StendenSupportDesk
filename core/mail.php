<?php
	class Mail {
		public static function Send($to, $subject, $message) {
			$headers = "From: Stenden Support Desk <info@stendensupportdesk.tk>\r\n";
			$headers .= "Reply-To: info@stendensupportdesk.tk\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "X-Mailer: PHP/" . phpversion();

			return mail($to, $subject, $message, $headers);
		}
	}