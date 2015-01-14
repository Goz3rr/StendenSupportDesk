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

	$klein->respond('GET', '/', function() use(&$twig) {
		return $twig->render('index.twig');
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

	$klein->respond('GET', '/phpinfo', function() {
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();

		return $info;
	});

	/*
	$klein->respond('GET', '/maakadmin', function() {
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