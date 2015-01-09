<?php
	class Incident extends Model {
		public function GetMap() {
			return array(
				"ID" => "IncidentID",
				"Type" => "IncidentType",
				"Kanaal" => "IncidentKanaal",
				"Lijn" => "IncidentLijn",
				"Prioriteit" => "IncidentPrioriteit"
			);
		}

		public function GetTable() {
			return "incident";
		}
	}