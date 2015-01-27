<?php
	class MainController extends Controller {
		public static function Routes($klein) {
			$klein->respond('GET', '/', 'MainController::Index');
			$klein->respond(array('GET', 'POST'), '/login', 'MainController::Login');
			$klein->respond('GET', '/logout', 'MainController::Logout');
			$klein->respond(array('GET', 'POST'), '/forgotpass', 'MainController::ForgotPass');
			$klein->respond('POST', '/changepass', 'MainController::ChangePass');

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

			return View::Render('main/index', array('stats' => $stats));
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
					return View::Render('main/login', array('errormsg' => 'Vul een gebruikersnaam en wachtwoord in'));
				}

				$valid = Auth::LogIn($username, $password, $remember);
				if(!$valid) {
					return View::Render('main/login', array('errormsg' => 'Incorrecte gebruikersnaam of wachtwoord'));
				}

				$url = isset($_POST['redirect']) ? urldecode($_POST['redirect']) : '/';
				$response->redirect($url)->send();
			} else {
				if(Auth::IsLoggedIn()) {
					$url = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/';
					$response->redirect($url)->send();
				}

				if(isset($_GET['redirect'])) {
					return View::Render('main/login', array('redirect' => $_GET['redirect']));
				} else {
					return View::Render('main/login');
				}
			}
		}

		public static function ChangePass($request, $response, $service) {
			$key = $_POST['key'];
			$pass1 = $_POST['pass1'];
			$pass2 = $_POST['pass2'];

			$user = User::Where('UserWw', $key);
			if(!$user) {
				return $response->redirect('/')->send();
			}

			if(empty($pass1) || empty($pass2)) {
				return View::Render('main/forgotpass_new', array('errormsg' => 'Vul beide velden in', 'key' => $key));
			}

			if($pass1 != $pass2) {
				return View::Render('main/forgotpass_new', array('errormsg' => 'Wachtwoorden komen niet overeen', 'key' => $key));
			}

			if(!Auth::ValidPassword($pass1)) {
				return View::Render('main/forgotpass_new', array('errormsg' => 'Wachtwoord moet minstens 5 tekens zijn', 'key' => $key));	
			}

			$user->Wachtwoord = password_hash($pass1, PASSWORD_DEFAULT);
			$user->Save();

			return $response->redirect('/')->send();
		}

		public static function ForgotPass($request, $response, $service) {
			if(Auth::IsLoggedIn()) {
				$response->redirect('/')->send();
				return;
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$username = $_POST['username'];

				if(empty($username)) {
					return View::Render('main/forgotpass', array('errormsg' => 'Vul een gebruikersnaam in'));
				}

				$user = User::Where('UserInlog', $username);
				if(!$user) {
					return View::Render('main/forgotpass', array('errormsg' => 'Onbekende gebruiker ' . $username));
				}

				$key = urlencode(base64_encode($user->Wachtwoord));
				$body = sprintf('<html><body>Om uw wachtwoord voor de Stenden Support Desk te resetten klik <a href="http://stendensupportdesk.tk/forgotpass?key=%s">hier</a></body></html>', $key);

				$ok = Mail::Send($user->Email, 'Vergeten wachtwoord', $body);
				if(!$ok) {
					return View::Render('main/forgotpass', array('errormsg' => 'Mail kon niet verstuurd worden'));
				}

				return View::Render('main/forgotpass', array('errormsg' => 'Email is verstuurd naar ' . $user->Email));
			} else {
				if(isset($_GET['key'])) {
					$oldpass = base64_decode($_GET['key']);

					$user = User::Where('UserWw', $oldpass);
					if($user) {
						return View::Render('main/forgotpass_new', array('key' => $oldpass));
					} else {
						$response->redirect('/')->send();
					}
				} else {
					return View::Render('main/forgotpass');
				}
			}
		}

		public static function Logout($request, $response, $service) {
			Auth::LogOut();
			$response->redirect('/')->send();
		}

		public static function Stats($request, $response, $service) {
			Auth::CheckLoggedIn();
			
			return View::Render('main/stats');
		}
	}