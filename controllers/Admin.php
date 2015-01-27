<?php
	class AdminController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/admin/users', 'AdminController::Users');
		}

		public static function Users($request, $response, $service) {
			Auth::CheckBeheerder();
			
			$q = DB::Query("SELECT UserID, UserNaam, IFNULL(UserTelefoon, BedrijfTelefoon) AS Telefoon, IFNULL(UserEmail, BedrijfEmail) AS Email, BedrijfID, BedrijfNaam, UserFunctie, UserAfdeling FROM user, bedrijf WHERE UserBedrijf = BedrijfID");
			if(!$q) {
				return View::Error('SQL Fout');
			}

			$items = $q->fetchAll();

			return View::Render('admin/list', array('items' => $items));
		}
	}