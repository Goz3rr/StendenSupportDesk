<?php
	class User extends Model {
		public function GetMap() {
			return array(
				"ID" => "UserID",
				"Inlog" => "UserInlog",
				"Wachtwoord" => "UserWw",
				"Naam" => "UserNaam",
				"BedrijfID" => "UserBedrijf",
				"Functie" => "UserFunctie",
				"Telefoon" => "UserTelefoon",
				"Email" => "UserEmail",
				"Foto" => "UserFoto",
				"Afdeling" => "UserAfdeling"
			);
		}

		public function GetTable() {
			return "user";
		}
	}