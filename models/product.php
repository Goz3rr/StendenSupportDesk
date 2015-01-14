<?php
	class Product extends Model {
		public static function GetMap() {
			return array(
				"ID" => "ProductID",
				"Product" => "Product",
				"Aanschaf" => "ProductAanschaf",
				"LicentieTot" => "ProductLicentieTot",
				"KlantID" => "ProductKlantID"
			);
		}

		public static function GetTable() {
			return "product";
		}
	}