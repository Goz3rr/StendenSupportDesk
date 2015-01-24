<?php
	class FAQController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/faq', 'FAQController::FAQList');
			$klein->respond('POST', '/faq/[add|update:action]', 'FAQController::FAQModify');
			$klein->respond('GET', '/faq/delete/[i:id]', 'FAQController::FAQDelete');
		}

		public static function FAQList($request, $response, $service) {
			Auth::CheckLoggedIn();

			return View::render('faq', array(
				'entries' => FAQ::GetAll(true),
				'canEdit' => Auth::IsMedewerker()
			));
		}

		public static function FAQDelete($request, $response, $service) {
			Auth::CheckLoggedIn();

			if(Auth::IsMedewerker()) {
				$faq = FAQ::Get($request->id);
				$faq->Delete();
				$response->redirect('/faq')->send();
			} else {
				return View::render('error', array('message' => 'Alleen medewerkers van stenden eHelp kunnen dat doen.'));
			}
		}

		public static function FAQModify($request, $response, $service) {
			Auth::CheckLoggedIn();

			if(Auth::IsMedewerker()) {
				if($request->action == 'add') {
					$titel = trim($_POST['titel']);
					$vraag = trim($_POST['vraag']);
					$antwoord = trim($_POST['antwoord']);

					if(!empty($titel) && !empty($vraag) && !empty($antwoord)) {
						$faq = new FAQ();
						$faq->Titel = $titel;
						$faq->Omschrijving = $vraag;
						$faq->Oplossing = $antwoord;
						$faq->Save();
						
						$response->redirect('/faq')->send();
					} else {
						return View::render('error', array('message' => 'Alle velden moeten worden ingevuld'));
					}
				} elseif($request->action == 'update') {
					if(isset($_POST['submit'])) {
						$id = $_POST['id'];
						$vraag = trim($_POST['vraag']);
						$antwoord = trim($_POST['antwoord']);

						if(!empty($vraag) && !empty($antwoord)) {
							$faq = FAQ::Get($id);
							$faq->Omschrijving = $vraag;
							$faq->Oplossing = $antwoord;
							$faq->Save();
						}
					}

					$response->redirect('/faq')->send();
				}
			} else {
				return View::render('error', array('message' => 'Alleen medewerkers van stenden eHelp kunnen dat doen.'));
			}
		}
	}