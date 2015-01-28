<?php
	class CustomerController extends Controller {
		public static function Routes($klein) {
			$klein->respond('POST', '/customers/create', 'CustomerController::Create');
			$klein->respond('POST', '/customers/license/[i:id]', 'CustomerController::License');
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

				if(!Auth::CreateUser($email, $naam, $bedrijf->ID, 'Medewerker', $email)) {
					return View::Error('Mail kon niet verstuurd worden!');
				}

				$response->redirect('/customers/list')->send();
			}
		}

		public static function License($request, $response, $service) {
			Auth::CheckMedewerker();

			$id = $request->id;
			$klant = Bedrijf::Where('BedrijfID', $id);
			if(!$klant) {
				return View::Error('Onbekende klant');
			}


			$type = $_POST['type'];
			$van = $_POST['van'];
			$tot = $_POST['tot'];

			$product = new Product();
			$product->Product = $type;
			$product->Aanschaf = $van;
			$product->LicentieTot = $tot;
			$product->KlantID = $klant->ID;
			$product->Save();

			$response->redirect('/customers/view/' . $klant->ID)->send();
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
					$klant = Bedrijf::Where('BedrijfID', $request->id);

					if(!$klant) {
						return View::Error('Onbekende klant');
					}

					$licenties = DB::Prepare("SELECT * FROM product WHERE ProductKlantID = ? ORDER BY ProductLicentieTot DESC", array($request->id))->fetchAll();

					return View::Render('customers/view', array(
						'klant' => $klant,
						'licenties' => $licenties
					));
				}
			}
		}
	}