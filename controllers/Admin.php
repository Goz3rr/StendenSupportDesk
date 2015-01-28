<?php
	class AdminController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/admin/users', 'AdminController::Users');
			$klein->respond('POST', '/admin/users/create', 'AdminController::CreateUser');
		}

		public static function Users($request, $response, $service) {
			Auth::CheckBeheerder();
			
			$q = DB::Query("SELECT UserID, UserNaam, IFNULL(UserTelefoon, BedrijfTelefoon) AS Telefoon, IFNULL(UserEmail, BedrijfEmail) AS Email, BedrijfID, BedrijfNaam, UserFunctie, UserAfdeling FROM user, bedrijf WHERE UserBedrijf = BedrijfID");
			if(!$q) {
				return View::Error('SQL Fout');
			}

			$items = $q->fetchAll();

			$q = DB::Query("SELECT BedrijfID, BedrijfNaam FROM bedrijf");
			$klanten = $q->fetchAll();

			return View::Render('admin/list', array('items' => $items, 'klanten' => $klanten));
		}

		public static function CreateUser($request, $response, $service) {
			$name = trim($_POST['naam']);
			$username = trim($_POST['loginname']);
			$email = trim($_POST['email']);
			$bedrijf = $_POST['bedrijf'];
			$functie = trim($_POST['functie']);
			$phone = trim($_POST['telefoon']);
			$afdeling = trim($_POST['afdeling']);

			if(empty($name) || empty($username) || empty($email) || empty($bedrijf) || empty($functie)) {
				return View::Error('Alle velden moeten worden ingevuld');
			}

			if(User::Where('UserInlog', $username)) {
				return View::Error('Die gebruikersnaam bestaat al');
			}
 
			if(!Auth::CreateUser($username, $name, $bedrijf, $functie, $email, $afdeling, $phone)) {
				return View::Error('Mail kon niet verstuurd worden!');
			}

			$response->redirect('/admin/users')->send();
		}
	}