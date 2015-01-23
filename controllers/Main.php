<?php
	class MainController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/', 'MainController::Index');
			$klein->respond(array('GET', 'POST'), '/login', 'MainController::Login');
			$klein->respond('GET', '/logout', 'MainController::Logout');

			$klein->respond('GET', '/stats', 'MainController::Stats');
		}

		public static function Index($request, $response, $service) {
			Auth::CheckLoggedIn();

			$stats = array(
				"nieuw" => 0,
				"opgelost" => 0,
				"openstaande" => 0,
				"onbehandelde" => 0
			);

			/*
			try {
				$q = DB::Query("SELECT ");
			} catch(PDOException $ex) {
				echo 'SQL Error: ' . $ex->getMessage();
			}
			*/

			return View::Render('index', array('stats' => $stats));
		}

		public static function Login($request, $response, $service) {
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(!isset($_POST['submit'])) {
					return $response->redirect('/login')->send();
				}

				$username = $_POST['username'];
				$password = $_POST['password'];
				$remember = isset($_POST['remember']) ? $_POST['remember'] : false;

				if(empty($username) || empty($password)) {
					return View::Render('login', array('errormsg' => 'Vul een gebruikersnaam en wachtwoord in'));
				}

				$valid = Auth::LogIn($username, $password, $remember);
				if(!$valid) {
					return View::Render('login', array('errormsg' => 'Incorrecte gebruikersnaam of wachtwoord'));
				}

				$response->redirect('/')->send();
			} else {
				return View::Render('login');
			}
		}

		public static function Logout($request, $response, $service) {
			Auth::LogOut();
			$response->redirect('/')->send();
		}

		public static function Stats($request, $response, $service) {
			Auth::CheckLoggedIn();
			
			return View::render('stats');
		}
	}