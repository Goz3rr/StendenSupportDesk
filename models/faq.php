<?php
	class FAQ extends Model {
		public static function GetMap() {
			return array(
				'ID' => 'FAQID',
				'Titel' => 'FAQTitel',
				'Omschrijving' => 'FAQOmschrijving',
				'Oplossing' => 'FAQOplossing'
			);
		}

		public static function GetTable() {
			return 'faq';
		}
	}