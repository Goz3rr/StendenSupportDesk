<?php
	class FAQ extends Model {
		public function GetMap() {
			return array(
				"ID" => "FAQID",
				"Titel" => "FAQTitel",
				"Omschrijving" => "FAQOmschrijving",
				"Oplossing" => "FAQOplossing"
			);
		}

		public function GetTable() {
			return "faq";
		}
	}