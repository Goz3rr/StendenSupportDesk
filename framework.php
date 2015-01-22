<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	session_start();

	require_once(__DIR__ . '/vendor/autoload.php');

	require_once(__DIR__ . '/sql.php');
	require_once(__DIR__ . '/auth.php');

	require_once(__DIR__ . '/models/model.php');
	require_once(__DIR__ . '/models/bedrijf.php');
	require_once(__DIR__ . '/models/faq.php');
	require_once(__DIR__ . '/models/incident.php');
	require_once(__DIR__ . '/models/product.php');
	require_once(__DIR__ . '/models/reactie.php');
	require_once(__DIR__ . '/models/user.php');

	require_once(__DIR__ . '/view.php');

	$klein = new \Klein\Klein();

	$klein->respond('GET', '/', function($request, $response, $service) {
		if(Auth::IsLoggedIn()) {

			try {
				//$q = DB::Query("SELECT ");
			} catch(PDOException $ex) {
				echo "SQL Error: " . $ex->getMessage();
			}

			$stats = array(
				"nieuw" => 0,
				"opgelost" => 0,
				"openstaande" => 0,
				"onbehandelde" => 0
			);

			return View::Render('index', array('stats' => $stats));
		}

		$response->redirect('/login')->send();
	});

	$klein->respond('GET', '/login', function() {
		return View::Render('login');
	});

	$klein->respond('POST', '/login', function($request, $response, $service) {
		if(!isset($_POST['submit'])) {
			$response->redirect('/login')->send();
			return;
		}

		$username = $_POST['username'];
		$password = $_POST['password'];
		$remember = isset($_POST['remember']) ? $_POST['remember'] : false;

		if(empty($username) || empty($password)) {
			return View::Render('login', array('errormsg' => "Vul een gebruikersnaam en wachtwoord in"));
		}

		$valid = Auth::LogIn($username, $password, $remember);
		if(!$valid) {
			return View::Render('login', array('errormsg' => "Incorrecte gebruikersnaam of wachtwoord"));
		}

		$response->redirect('/')->send();
	});

	$klein->respond('GET', '/logout', function($request, $response, $service) {
		Auth::LogOut();
		$response->redirect('/')->send();
	});

	$klein->respond('GET', '/profile', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return View::Render('profile');
	});
	$klein->respond('POST', '/profile', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}
		$password = $_POST['wachtwoord'];
		$email = $_POST['email'];

		$user=User::Where("UserID",$_SESSION["uid"]);

		$user->wachtwoord= password_hash($password, PASSWORD_DEFAULT);
		$user->Email=$email;

		$response->redirect('/profile')->send();
	});

	$klein->respond('GET', '/settings', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return View::Render('settings');
	});

	$klein->respond('POST', '/search', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return View::Render('search', array('search' => $_POST['search']));
	});

	$klein->respond('GET', '/tickets/[create|open|closed|view|new|newreplies:action]?/[i:id]?', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		if($request->action == 'create') {
			return View::Render('createticket');
		} elseif($request->action == 'open') {
			return View::Render('opentickets');
		} elseif($request->action == 'closed') {
			return View::Render('closedtickets');
		} elseif($request->action == 'view') {
			return View::Render('ticket');
		} elseif($request->action == 'new') {
			return View::Render('newtickets');
		} elseif($request->action == 'newreplies') {
			return View::Render('newticketreplies');
		}

		return "wut";
	});

	$klein->respond('GET', '/faq', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		$entries = FAQ::GetAll();
		$canEdit = Auth::IsMedewerker();
		return View::render('faq', array('entries' => $entries, 'canEdit' => $canEdit));
	});

	$klein->respond('POST', '/faqadd', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		if(Auth::IsMedewerker()) {
			$titel = trim($_POST['titel']);
			$vraag = trim($_POST['vraag']);
			$antwoord = trim($_POST['antwoord']);

			if(!empty($titel) && !empty($vraag) && !empty($antwoord)) {
				$faq = new FAQ();
				$faq->Titel = $titel;
				$faq->Omschrijving = $vraag;
				$faq->Oplossing = $antwoord;
				$faq->Save();
			}
		}

		$response->redirect('/faq')->send();
	});

	$klein->respond('GET', '/stats', function($request, $response, $service) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return View::render('stats');
	});

	$klein->onHttpError(function($code, $router) {
		$router->response()->body('error ' . $code);
	});

	/*
	$klein->respond('GET', '/maakadmin', function() {
		$bedrijf = new Bedrijf();

		$bedrijf->Naam = "Stenden eHelp";
		$bedrijf->Adres = "Van Schaikweg 94";
		$bedrijf->Postcode = "7811KL";
		$bedrijf->Plaats = "Emmen";
		$bedrijf->Telefoon = "0591853100";
		$bedrijf->Email = "receptie.emmen@stenden.com";

		$bedrijf->Save();

		var_dump($bedrijf);

		$user = new User();

		$user->Inlog = 'admin';
		$user->Wachtwoord = password_hash('banaan', PASSWORD_DEFAULT);
		$user->Naam = 'admin';
		$user->BedrijfID = 1;
		$user->Functie = 'admin';
		$user->Email = 'admin@bedrijf.nl';

		$user->Save();

		var_dump($user);
	});
	*/
	

	$klein->dispatch();