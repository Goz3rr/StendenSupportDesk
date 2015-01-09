<?php
	class Bedrijf extends Model {
		public function GetMap() {
			return array(
				"ID" => "BedrijfID",
				"Naam" => "BedrijfNaam",
				"Adres" => "BedrijfAdres",
				"Postcode" => "BedrijfPostcode",
				"Plaats" => "BedrijfPlaats",
				"Telefoon" => "BedrijfTelefoon",
				"Email" => "BedrijfEmail"
			);
		}

		public function GetTable() {
			return "bedrijf";
		}
	}