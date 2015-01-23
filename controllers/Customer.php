<?php
	class CustomerController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/customers/create', 'CustomerController::Create');
			$klein->respond('GET', '/customers/[list|view:action]?/[i:id]?', 'CustomerController::View');
		}

		public static function Create($request, $response, $service) {
			Auth::CheckLoggedIn();
		}

		public static function View($request, $response, $service) {
			Auth::CheckLoggedIn();
		}
	}