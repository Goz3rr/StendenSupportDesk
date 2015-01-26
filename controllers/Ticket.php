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

					$response->redirect('/login')->send();
				}
			} else {
				return View::Render('tickets/create');
			}
		}

		public static function View($request, $response, $service) {
			Auth::CheckLoggedIn();

			if(empty($request->type)) {
				return 'hi';
			} else {
				$items = array(
					'PrioColors' => array(
						'Laag' => 'label-success',
						'Gemiddeld' => 'label-warning',
						'Hoog' => 'label-danger'
					),
					'TypeColors' => array(
						'Vraag' => 'label-primary',
						'Verzoek' => 'label-success',
						'Incident' => 'label-danger',
						'Functioneel Probleem' => 'label-warning',
						'Technisch Probleem' => 'label-warning'
					),
					'Values' => array()
				);

				if($request->type == 'open') {
					$titel = 'Openstaande Incidenten';

					if(Auth::IsMedewerker()) {
						$q = DB::Query("SELECT I.*, FirstReactie.IncUser AS StartUser, FirstReactie.IncReactieDatum AS StartDatum,
							LastReactie.IncUser AS LastUser, LastReactie.IncReactieDatum AS LastDatum, LastReactie.IncStatus AS Status,
							(SELECT UserNaam FROM user WHERE UserID = FirstReactie.IncUser) AS KlantNaam,
							(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
							FROM
								incident I,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
							WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND LastReactie.IncStatus != 'Afgehandeld' GROUP BY IncidentID
						");

						$items['Values'] = $q->fetchAll();
					} else {
						$q = DB::Prepare("SELECT I.*, FirstReactie.IncUser AS StartUser, FirstReactie.IncReactieDatum AS StartDatum,
							LastReactie.IncUser AS LastUser, LastReactie.IncReactieDatum AS LastDatum, LastReactie.IncStatus AS Status,
							(SELECT UserNaam FROM user WHERE UserID = FirstReactie.IncUser) AS KlantNaam,
							(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
							FROM
								incident I,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
							WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND LastReactie.IncStatus != 'Afgehandeld' AND FirstReactie.IncUser = ? GROUP BY IncidentID
						", array($_SESSION['uid']));

						$items['Values'] = $q->fetchAll();
					}
				} elseif($request->type == 'closed') {
					$titel = 'Afgesloten Incidenten';

					if(Auth::IsMedewerker()) {
						$q = DB::Query("SELECT I.*, FirstReactie.IncUser AS StartUser, FirstReactie.IncReactieDatum AS StartDatum,
							LastReactie.IncUser AS LastUser, LastReactie.IncReactieDatum AS LastDatum, LastReactie.IncStatus AS Status,
							(SELECT UserNaam FROM user WHERE UserID = FirstReactie.IncUser) AS KlantNaam,
							(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
							FROM
								incident I,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
							WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND LastReactie.IncStatus = 'Afgehandeld' GROUP BY IncidentID
						");

						$items['Values'] = $q->fetchAll();
					} else {
						$q = DB::Prepare("SELECT I.*, FirstReactie.IncUser AS StartUser, FirstReactie.IncReactieDatum AS StartDatum,
							LastReactie.IncUser AS LastUser, LastReactie.IncReactieDatum AS LastDatum, LastReactie.IncStatus AS Status,
							(SELECT UserNaam FROM user WHERE UserID = FirstReactie.IncUser) AS KlantNaam,
							(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
							FROM
								incident I,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
								(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
							WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND LastReactie.IncStatus = 'Afgehandeld' AND FirstReactie.IncUser = ? GROUP BY IncidentID
						", array($_SESSION['uid']));

						$items['Values'] = $q->fetchAll();
					}
				} elseif($request->type == 'new') {
					Auth::CheckMedewerker();

					$titel = 'Nieuwe Incidenten';
					$q = DB::Query("SELECT I.*, FirstReactie.IncUser AS StartUser, FirstReactie.IncReactieDatum AS StartDatum,
							LastReactie.IncUser AS LastUser, LastReactie.IncReactieDatum AS LastDatum, LastReactie.IncStatus AS Status,
							(SELECT UserNaam FROM user WHERE UserID = FirstReactie.IncUser) AS KlantNaam,
							(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
							FROM
							incident I,
							(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID ASC) AS FirstReactie,
							(SELECT * FROM increactie WHERE IncStatus = 'Open' ORDER BY IncReactieID DESC) AS LastReactie
						WHERE IncidentID = LastReactie.IncID AND IncidentID = FirstReactie.IncID AND IncidentMedewerker IS NULL GROUP BY IncidentID
					");

					$items['Values'] = $q->fetchAll();
				} elseif($request->type == 'newreplies') {
					$titel = 'Incidenten met nieuwe reacties';
				}

				if(Auth::IsMedewerker()) {
					return View::Render('tickets/list_all', array(
							'type' => $request->type,
							'titel' => $titel,
							'items' => $items
						));
				} else {
					return View::Render('tickets/list', array(
						'type' => $request->type,
						'titel' => $titel,
						'items' => $items
					));
				}
			}
		}
	}