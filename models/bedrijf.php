<?php
	class Bedrijf extends Model {
		public static function GetMap() {
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

		public static function GetTable() {
			return "bedrijf";
		}
	}