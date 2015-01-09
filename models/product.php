<?php
	class Product extends Model {
		public function GetMap() {
			return array(
				"ID" => "ProductID",
				"Product" => "Product",
				"Aanschaf" => "ProductAanschaf",
				"LicentieTot" => "ProductLicentieTot",
				"KlantID" => "ProductKlantID"
			);
		}

		public function GetTable() {
			return "product";
		}
	}