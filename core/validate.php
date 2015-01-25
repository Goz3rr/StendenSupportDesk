<?php
	class Validate {
		public static function Email($email) {
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}

		public static function Postcode($postcode) {
			return preg_match('~\A[1-9]\d{3}[a-zA-Z]{2}\z~', $postcode);
		}
	}