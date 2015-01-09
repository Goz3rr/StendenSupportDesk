<?php
	class Reactie extends Model {
		public function GetMap() {
			return array(
				"ID" => "IncReactieID",
				"User" => "IncUser",
				"Reactie" => "IncReactie",
				"Datum" => "IncReactieDatum",
				"Status" => "IncStatus",
				"IncidentID" => "IncID"
			);
		}

		public function GetTable() {
			return "increactie";
		}
	}