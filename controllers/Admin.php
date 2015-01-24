<?php
	class AdminController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/admin', 'AdminController::Index');
		}

		public static function Index($request, $response, $service) {
			Auth::CheckBeheerder();
			
			return View::Render('admin/index');
		}
	}