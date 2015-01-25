<?php
	class CustomerController extends Controller {
		public static function Routes($klein) {
			$klein->respond('POST', '/customers/create', 'CustomerController::Create');
			$klein->respond('GET', '/customers/[list|view:action]?/[i:id]?', 'CustomerController::View');
		}

		public static function Create($request, $response, $service) {
			Auth::CheckBeheerder();

			$naam = trim($_POST['naam']);
			$adres = trim($_POST['adres']);
			$postcode = str_replace(' ', '', $_POST['postcode']);
			$plaats = trim($_POST['plaats']);
			$telefoon = trim($_POST['telefoon']);
			$email = trim($_POST['email']);

			if(empty($naam) || empty($adres) || empty($postcode) || empty($plaats) || empty($telefoon) || empty($email)) {
				return View::Error('Alle velden moeten worden ingevuld');
			} else {
				if(!Validate::Email($email)) {
					return View::Error('Ongeldig email adres');
				}

				if(!Validate::Postcode($postcode)) {
					return View::Error('Ongeldige postcode');
				}

				$bedrijf = new Bedrijf();
				$bedrijf->Naam = $naam;
				$bedrijf->Adres = $adres;
				$bedrijf->Postcode = $postcode;
				$bedrijf->Plaats = $plaats;
				$bedrijf->Telefoon = $telefoon;
				$bedrijf->Email = $email;
				$bedrijf->Save();

				$pass = Auth::MakePassword();

				$user = new User();
				$user->Inlog = $email;
				$user->Wachtwoord = password_hash($pass, PASSWORD_DEFAULT);
				$user->Naam = $naam;
				$user->BedrijfID = $bedrijf->ID;
				$user->Functie = 'Medewerker';
				$user->Email = $email;
				$user->Save();

				$body = sprintf('<html><body>Hierbij uw inlog gegevens:<br><p><strong>Naam:</strong> %s<br><strong>Wachtwoord:</strong> %s</p></body></html>', $email, $pass);

				$ok = Mail::Send($email, 'Uw account is aangemaakt', $body);
				if(!$ok) {
					return View::Error('Mail kon niet verstuurd worden!');
				}

				$response->redirect('/customers/list')->send();
			}
		}

		public static function View($request, $response, $service) {
			Auth::CheckMedewerker();

			if($request->action == 'list') {
				$q = DB::Query("SELECT BedrijfID, BedrijfNaam, BedrijfAdres, BedrijfPostcode, BedrijfPlaats, BedrijfTelefoon, BedrijfEmail FROM bedrijf WHERE BedrijfID > 1");
				if(!$q) {
					return View::Error('SQL Fout');
				}

				$items = $q->fetchAll();
				$q = DB::Prepare("SELECT ProductLicentieTot FROM product WHERE ProductKlantID = ? AND Product = 'Helpdesk' ORDER BY ProductLicentieTot DESC LIMIT 1");

				foreach ($items as $k => $item) {
					$items[$k]['Licensie'] = '-';

					if($q->execute(array($item['BedrijfID']))) {
						if($row = $q->fetch()) {
							if(strtotime($row['ProductLicentieTot']) > time()) {
								$items[$k]['Licensie'] = 'geldig tot ' . $row['ProductLicentieTot'];
							} else {
								$items[$k]['Licensie'] = '<span class="text-danger">verlopen op ' . $row['ProductLicentieTot'] . '</span>';
							}
						}
					}
				}

				return View::Render('customers/list', array('items' => $items));
			} else {
				if($request->id == null) {
					$response->redirect('/customers/list')->send();
				} else {
					// specifieke klant
				}
			}
		}
	}