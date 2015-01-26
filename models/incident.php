<?php
	class Incident extends Model {
		public static function GetMap() {
			return array(
				'ID' => 'IncidentID',
				'Titel' => 'IncidentTitel',
				'Type' => 'IncidentType',
				'Kanaal' => 'IncidentKanaal',
				'Lijn' => 'IncidentLijn',
				'Prioriteit' => 'IncidentPrioriteit',
				'MedewerkerID' => 'IncidentMedewerker',
				'LaatstBekeken' => 'IncidentLaatstBekeken',
			);
		}

		public static function GetTable() {
			return 'incident';
		}
	}