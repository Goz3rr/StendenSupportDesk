<?php
	class CustomerController extends Controller {
		public static function Routes($klein) {
			$klein->respond('POST', '/customers/create', 'CustomerController::Create');
			$klein->respond('GET', '/customers/[list|view:action]?/[i:id]?', 'CustomerController::View');
		}

		public static function Create($request, $response, $service) {
			Auth::CheckLoggedIn();
		}

		public static function View($request, $response, $service) {
			Auth::CheckMedewerker();

			if($request->action == 'list') {
				$q = DB::Query("SELECT BedrijfID, BedrijfNaam, CONCAT(BedrijfAdres, ' ', BedrijfPostcode, ' ', BedrijfPlaats) AS Adres, BedrijfTelefoon, BedrijfEmail FROM bedrijf WHERE BedrijfID > 1");
				if(!$q) {
					return View::render('error', array('message' => 'SQL Fout'));
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

				}
			}
		}
	}