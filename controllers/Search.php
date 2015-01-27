<?php
	class SearchController extends Controller {
		public static function Routes($klein) {
			$klein->respond('POST', '/search', 'SearchController::Search');
		}

		public static function Search($request, $response, $service) {
			Auth::CheckLoggedIn();

			$s = DB::Query("SELECT * FROM incident WHERE IncidentTitel LIKE '%".$_POST['search']."%'");
			if(!$s) {
				return View::Error('SQL Fout');
			}
			$search = $_POST['search'];
			$items = $s->fetchAll();

			return View::Render('search', array('search' => $search,'items' => $items));
		}
	}