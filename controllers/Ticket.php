<?php
	class TicketController extends Controller {
		public static function Routes($klein) {
			$klein->respond(array('GET', 'POST'), '/tickets/create', 'TicketController::Create');
			$klein->respond('POST', '/tickets/reply/[i:id]', 'TicketController::Reply');
			$klein->respond('GET', '/tickets/view/[open|closed|new|newreplies:type]?/[i:id]?', 'TicketController::View');
		}

		public static function Reply($request, $response, $service) {
			Auth::CheckLoggedIn();

			$user = User::Get($_SESSION['uid']);

			$id = $request->id;
			if(empty($id)) {
				return $response->redirect('/tickets/view/open')->send();
			}

			$reactie = trim($_POST['reactie']);
			if(empty($reactie)) {
				return View::Error('Alle velden moeten worden ingevuld');
			}

			$q = DB::Prepare("SELECT IncStatus FROM increactie WHERE IncID = ? ORDER BY IncReactieID DESC LIMIT 1", array($id));
			$curStatus = $q->fetch()['IncStatus'];

			if(in_array($_POST['newstatus'], array('Open', 'In behandeling', 'Afgehandeld'))) {
				$status = $_POST['newstatus'];
			} else {
				$status = $curStatus;
			}

			if($status != $curStatus) {
				$reactie = $reactie . "  \n\nStatus aangepast naar: **" . $status . '**';
			}

			$q = DB::Prepare("SELECT UserBedrijf FROM increactie, user WHERE IncUser = UserID AND IncID = ? GROUP BY IncID", array($request->id));
			$r = $q->fetch();

			if(Auth::IsMedewerker() || $user->Bedrijf == $r['UserBedrijf']) {
				$reply = new Reactie();
				$reply->User = $_SESSION['uid'];
				$reply->Reactie = $reactie;
				$reply->Datum = date('Y-m-d H:i:s');
				$reply->Status = $status;
				$reply->IncidentID = $request->id;
				$reply->Save();

				return $response->redirect('/tickets/view/' . $id)->send();
			} else {
				return View::Error('geen toegang tot dat incident');
			}
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

					$response->redirect('/tickets/view/' . $inc->ID)->send();
				}
			} else {
				return View::Render('tickets/create');
			}
		}

		public static function View($request, $response, $service) {
			Auth::CheckLoggedIn();

			$colors = array(
				'Prio' => array(
					'Laag' => 'label-success',
					'Gemiddeld' => 'label-warning',
					'Hoog' => 'label-danger'
				),
				'Type' => array(
					'Vraag' => 'label-primary',
					'Verzoek' => 'label-success',
					'Incident' => 'label-danger',
					'Functioneel Probleem' => 'label-warning',
					'Technisch Probleem' => 'label-warning'
				)
			);

			if(empty($request->type)) {
				if(empty($request->id)) {
					$response->redirect('/tickets/view/open')->send();
				}

				$incident = Incident::Get($request->id);
				if(!$incident) {
					return View::Error('Dat incident bestaat niet');
				}

				$q = DB::Prepare("SELECT IncReactie, IncReactieDatum, IncStatus, UserNaam, UserBedrijf, UserFoto FROM increactie, user WHERE IncUser = UserID AND IncID = ?", array($request->id));
				$replies = $q->fetchAll();

				return View::Render('tickets/view', array(
					'incident' => $incident,
					'replies' => $replies,
					'colors' => $colors
				));
			} else { // godverdomme sql
				$items = array();

				if($request->type == 'open') {
					$titel = 'Openstaande Incidenten';

					if(Auth::IsMedewerker()) {
						$q = DB::Query("SELECT I.*,
(SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS Status,
(SELECT IncReactieDatum FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS LastDatum,
(SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS StartUser,
(SELECT UserNaam FROM user WHERE UserID = StartUser) AS KlantNaam,
(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
FROM incident I
WHERE (SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) != 'Afgehandeld'
						");

						$items = $q->fetchAll();
					} else {
						$q = DB::Prepare("SELECT I.*,
(SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS Status,
(SELECT IncReactieDatum FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS LastDatum,
(SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS StartUser,
(SELECT UserNaam FROM user WHERE UserID = StartUser) AS KlantNaam,
(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
FROM incident I
WHERE (SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) != 'Afgehandeld' AND (SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) = ?
						", array($_SESSION['uid']));

						$items = $q->fetchAll();
					}
				} elseif($request->type == 'closed') {
					$titel = 'Afgesloten Incidenten';

					if(Auth::IsMedewerker()) {
						$q = DB::Query("SELECT I.*,
(SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS Status,
(SELECT IncReactieDatum FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS LastDatum,
(SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS StartUser,
(SELECT UserNaam FROM user WHERE UserID = StartUser) AS KlantNaam,
(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
FROM incident I
WHERE (SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) = 'Afgehandeld'
						");

						$items = $q->fetchAll();
					} else {
						$q = DB::Prepare("SELECT I.*,
(SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS Status,
(SELECT IncReactieDatum FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS LastDatum,
(SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) AS StartUser,
(SELECT UserNaam FROM user WHERE UserID = StartUser) AS KlantNaam,
(SELECT UserNaam FROM user WHERE UserID = I.IncidentMedewerker) AS MedewerkerNaam
FROM incident I
WHERE (SELECT IncStatus FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MAX(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) = 'Afgehandeld' AND (SELECT IncUser FROM increactie WHERE IncID = I.IncidentID AND IncReactieDatum = (SELECT MIN(IncReactieDatum) FROM increactie WHERE IncID = I.IncidentID)) = ?
						", array($_SESSION['uid']));

						$items = $q->fetchAll();
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

					$items = $q->fetchAll();
				} elseif($request->type == 'newreplies') {
					$titel = 'Incidenten met nieuwe reacties';
				}

				if(Auth::IsMedewerker()) {
					$medewerkers = DB::Query("SELECT UserID, UserNaam FROM user WHERE UserBedrijf = 1");

					return View::Render('tickets/list_all', array(
							'type' => $request->type,
							'titel' => $titel,
							'items' => $items,
							'colors' => $colors,
							'medewerkers' => $medewerkers
						));
				} else {
					return View::Render('tickets/list', array(
						'type' => $request->type,
						'titel' => $titel,
						'items' => $items,
						'colors' => $colors
					));
				}
			}
		}
	}