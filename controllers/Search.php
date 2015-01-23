<?php
	class SearchController extends Controller {
		public static function Routes($klein) {
			$klein->respond('POST', '/search', 'SearchController::Search');
		}

		public static function Search($request, $response, $service) {
			Auth::CheckLoggedIn();
			
			return View::Render('search', array('search' => $_POST['search']));
		}
	}