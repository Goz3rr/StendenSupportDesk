<?php
	class TicketController extends Controller {
		public static function Routes($klein) {
			$klein->respond(array('GET', 'POST'), '/tickets/create', 'TicketController::Create');
			$klein->respond('GET', '/tickets/view/[open|closed|new|newreplies:type]?/[i:id]?', 'TicketController::View');
		}

		public static function Create($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$titel = trim($_POST['titel']);
				$omschrijving = trim($_POST['omschrijving']);

				if(empty($titel) || empty($omschrijving)) {
					return View::Error('Alle velden moeten worden ingevuld');
				}
				
				if(in_array($_POST['prioriteit'], array('Vraag', 'Verzoek', 'Incident', 'Functioneel Probleem', 'Technisch Probleem'))) {
					$type = $_POST['type'];
				} else {
					$type = 'Vraag';
				}
				
				if(in_array($_POST['prioriteit'], array('Laag', 'Gemiddeld', 'Hoog'))) {
					$prio = $_POST['prioriteit'];
				} else {
					$prio = 'Laag';
				}

				if(Auth::IsMedewerker()) {

				} else {
					$inc = new Incident();
					$inc->Titel = $titel;
					$inc->Type = $type;
					$inc->Kanaal = 'Ticket';
					$inc->Lijn = 1;
					$inc->Prioriteit = $prio;
					$inc->Save();

					$msg = new Reactie();
					$msg->User = $_SESSION['uid'];
					$msg->Reactie = $omschrijving;
					$msg->Datum = date('Y-m-d H:i:s');
					$msg->Status = 'Open';
					$msg->IncidentID = $inc->ID;
					$msg->Save();
				}
			} else {
				return View::Render('tickets/create');
			}
		}

		public static function View($request, $response, $service) {
			Auth::CheckLoggedIn();

			$type = $request->type;
			$items = array();

			/*
				SELECT * FROM
				incident,
				(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
				(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
				WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID GROUP BY IncidentID
			*/

			if($request->type == 'open') {
				$titel = 'Openstaande Incidenten';

				if(Auth::IsMedewerker()) {
					$q = DB::Query("SELECT *
						FROM
							incident,
							(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
						WHERE IncidentID = LastReactie.IncID GROUP BY IncidentID
					");

					$items = $q->fetchAll();
				} else {
					$q = DB::Prepare("SELECT * 
						FROM
							incident,
							(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
							(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
						WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND FirstReactie.IncUser = ? GROUP BY IncidentID
					", array($_SESSION['uid']));
					$items = $q->fetchAll();
				}
			} elseif($request->type == 'closed') {
				$titel = 'Afgesloten Incidenten';

				if(Auth::IsMedewerker()) {
					$q = DB::Query("SELECT IncidentID, IncidentTitel, IncidentType, IncidentLijn, IncidentPrioriteit, IncidentMedewerker, IncReactieDatum FROM incident, (SELECT * FROM increactie WHERE IncStatus = 'Afgehandeld' ORDER BY IncReactieID DESC) AS reactie WHERE IncidentID = IncID GROUP BY IncidentID");
					$items = $q->fetchAll();
				} else {
					
				}
			} elseif($request->type == 'new') {
				$titel = 'Nieuwe Incidenten';
			} elseif($request->type == 'newreplies') {
				$titel = 'Incidenten met nieuwe reacties';
			}

			return View::Render('tickets/list', array(
				'type' => $type,
				'titel' => $titel,
				'items' => $items
			));
		}
	}