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

	$twigLoader = new Twig_Loader_Filesystem(__DIR__ . '/view');
	$twig = new Twig_Environment($twigLoader);

	$klein = new \Klein\Klein();

	$klein->respond('GET', '/', function($request, $response, $service) use(&$twig) {
		if(Auth::IsLoggedIn()) {
			return $twig->render('index.twig', array('gebruikerNaam' => $_SESSION['naam']));
		}

		$response->redirect('/login')->send();
	});

	$klein->respond('GET', '/login', function() use(&$twig) {
		return $twig->render('login.twig');
	});

	$klein->respond('POST', '/login', function($request, $response, $service) use(&$twig) {
		if(!isset($_POST['submit'])) {
			$response->redirect('/login')->send();
			return;
		}

		$username = $_POST['username'];
		$password = $_POST['password'];
		$remember = isset($_POST['remember']) ? $_POST['remember'] : false;

		if(empty($username) || empty($password)) {
			return $twig->render('login.twig', array('errormsg' => "Vul een gebruikersnaam en wachtwoord in"));
		}

		$valid = Auth::LogIn($username, $password, $remember);
		if(!$valid) {
			return $twig->render('login.twig', array('errormsg' => "Incorrecte gebruikersnaam of wachtwoord"));
		}

		$response->redirect('/')->send();
	});

	$klein->respond('GET', '/logout', function($request, $response, $service) use(&$twig) {
		Auth::LogOut();
		$response->redirect('/')->send();
	});

	$klein->respond('GET', '/profile', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return $twig->render('profile.twig', array('gebruikerNaam' => $_SESSION['naam']));
	});
	$klein->respond('POST', '/profile', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}
		$password = $_POST['wachtwoord'];
		$email = $_POST['email'];

		$user=User::Where("UserID",$_SESSION["uid"]);

		$user->wachtwoord= password_hash($password, PASSWORD_DEFAULT);
		$user->Email=$email;

		return $twig->render('profile.twig', array('gebruikerNaam' => $_SESSION['naam']));
	});

	$klein->respond('GET', '/settings', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return $twig->render('settings.twig', array('gebruikerNaam' => $_SESSION['naam']));
	});

	$klein->respond('POST', '/search', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return $twig->render('search.twig', array('search' => $_POST['search']));
	});

	$klein->respond('GET', '/tickets/[create|open|closed|view|new|newreplies:action]?/[i:id]?', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		if($request->action == 'create') {
			return $twig->render('createticket.twig', array('gebruikerNaam' => $_SESSION['naam']));
		} elseif($request->action == 'open') {
			return $twig->render('opentickets.twig', array('gebruikerNaam' => $_SESSION['naam']));
		} elseif($request->action == 'closed') {
			return $twig->render('closedtickets.twig', array('gebruikerNaam' => $_SESSION['naam']));
		} elseif($request->action == 'view') {
			return $twig->render('ticket.twig', array('gebruikerNaam' => $_SESSION['naam']));
		} elseif($request->action == 'new') {
			return $twig->render('newtickets.twig', array('gebruikerNaam' => $_SESSION['naam']));
		} elseif($request->action == 'newreplies') {
			return $twig->render('newticketreplies.twig', array('gebruikerNaam' => $_SESSION['naam']));
		}

		return "wut";
	});

	$klein->respond('GET', '/faq', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		$entries = FAQ::GetAll();
		$canEdit = Auth::IsMedewerker();
		return $twig->render('faq.twig', array('gebruikerNaam' => $_SESSION['naam'], 'entries' => $entries, 'canEdit' => $canEdit));
	});

	$klein->respond('POST', '/faqadd', function($request, $response, $service) use(&$twig) {
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

	$klein->respond('GET', '/stats', function($request, $response, $service) use(&$twig) {
		if(!Auth::IsLoggedIn()) {
			$response->redirect('/login')->send();
			return;
		}

		return $twig->render('stats.twig', array('gebruikerNaam' => $_SESSION['naam']));
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