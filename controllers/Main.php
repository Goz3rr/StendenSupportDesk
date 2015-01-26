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
				'nieuw' => 0,
				'opgelost' => 0,
				'openstaande' => 0,
				'onbehandelde' => 0
			);

			try {
				// ?
				$q = DB::Prepare('SELECT COUNT(IncReactieID) FROM incident, increactie WHERE IncidentMedewerker = ? AND IncidentID = IncID AND IncUser != ? AND NOW() > IncReactieDatum');
				if($q->execute(array($_SESSION['uid'], $_SESSION['uid']))) {
					$stats['nieuw'] = $q->fetch(PDO::FETCH_NUM)[0];
				}

				// ?
				$q = DB::Query("SELECT COUNT(IncidentID) FROM incident, increactie WHERE IncidentID = IncID AND IncStatus = 'Afgehandeld' AND MONTH(IncReactieDatum) = MONTH(NOW())");
				if($q) {
					$stats['opgelost'] = $q->fetch(PDO::FETCH_NUM)[0];
				}

				// ?
				$q = DB::Query("SELECT COUNT(IncidentID) FROM incident, increactie WHERE IncidentMedewerker IS NOT NULL AND IncidentID = IncID AND IncStatus != 'Afgehandeld'");
				if($q) {
					$stats['openstaande'] = $q->fetch(PDO::FETCH_NUM)[0];
				}

				$q = DB::Query('SELECT COUNT(IncidentID) FROM incident WHERE IncidentMedewerker IS NULL');
				if($q) {
					$stats['onbehandelde'] = $q->fetch(PDO::FETCH_NUM)[0];
				}
			} catch(PDOException $ex) {
				$message = 'SQL Error: ' . $ex->getMessage();
				die(View::Error($message));
			}

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

				$url = isset($_POST['redirect']) ? urldecode($_POST['redirect']) : '/';
				$response->redirect($url)->send();
			} else {
				if(Auth::IsLoggedIn()) {
					$url = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/';
					$response->redirect($url)->send();
				}

				if(isset($_GET['redirect'])) {
					return View::Render('login', array('redirect' => $_GET['redirect']));
				} else {
					return View::Render('login');
				}
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