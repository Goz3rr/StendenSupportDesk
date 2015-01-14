<?php
	class Incident extends Model {
		public static function GetMap() {
			return array(
				"ID" => "IncidentID",
				"Type" => "IncidentType",
				"Kanaal" => "IncidentKanaal",
				"Lijn" => "IncidentLijn",
				"Prioriteit" => "IncidentPrioriteit"
			);
		}

		public static function GetTable() {
			return "incident";
		}
	}