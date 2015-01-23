<?php
	class ProfileController extends Controller {
		public static function Routes($klein) {
			$klein->respond(array('GET', 'POST'), '/profile', 'ProfileController::Profile');
			$klein->respond(array('GET', 'POST'), '/settings', 'ProfileController::Settings');
		}

		public static function Profile($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				/*
				$password = $_POST['wachtwoord'];
				$email = $_POST['email'];

				$user=User::Where("UserID",$_SESSION["uid"]);

				$user->wachtwoord= password_hash($password, PASSWORD_DEFAULT);
				$user->Email=$email;

				$response->redirect('/profile')->send();
				*/
			} else {
				return View::Render('profile');
			}
		}

		public static function Settings($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

			} else {
				return View::Render('settings');
			}
		}
	}