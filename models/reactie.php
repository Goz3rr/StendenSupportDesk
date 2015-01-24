<?php
	class Reactie extends Model {
		public static function GetMap() {
			return array(
				'ID' => 'IncReactieID',
				'User' => 'IncUser',
				'Reactie' => 'IncReactie',
				'Datum' => 'IncReactieDatum',
				'Status' => 'IncStatus',
				'IncidentID' => 'IncID'
			);
		}

		public static function GetTable() {
			return 'increactie';
		}
	}