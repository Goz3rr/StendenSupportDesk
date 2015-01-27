<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	ini_set('session.gc_maxlifetime', 86400); // 24 uur
	session_set_cookie_params(86400);
	session_start();

	define('BASE_PATH', __DIR__ . '/..');

	require_once(BASE_PATH . '/vendor/autoload.php');

	require_once(BASE_PATH . '/views/view.php');
	
	require_once(BASE_PATH . '/core/sql.php');
	require_once(BASE_PATH . '/core/auth.php');
	require_once(BASE_PATH . '/core/validate.php');
	require_once(BASE_PATH . '/core/mail.php');

	require_once(BASE_PATH . '/models/model.php');
	require_once(BASE_PATH . '/models/bedrijf.php');
	require_once(BASE_PATH . '/models/faq.php');
	require_once(BASE_PATH . '/models/incident.php');
	require_once(BASE_PATH . '/models/product.php');
	require_once(BASE_PATH . '/models/reactie.php');
	require_once(BASE_PATH . '/models/user.php');

	$klein = new \Klein\Klein();

	require_once(BASE_PATH . '/controllers/controller.php');
	foreach(scandir(BASE_PATH . '/controllers') as $ent) {
		if($ent == '.' || $ent == '..' || $ent == 'controller.php') continue;

		if(substr_compare($ent, '.php', -4, 4) === 0) {
			include_once(BASE_PATH . '/controllers/' . $ent);

			$c = substr($ent, 0, -4) . 'Controller';
			$c::Routes($klein);
		}
	}

	$klein->onHttpError(function($code, $router) {
		$router->response()->body(View::Error('error ' . $code . '<br><a href="/">back to home</a>', false));
	});

	$klein->respond('GET', '/test', function() {
		/*
		$arr = array(
			array('Marloes', 'van Dam'),
			array('Sieuwke', 'Blankenstijn'),
			array('Seren', 'Koole'),
			array('Silva', 'Koppers'),
			array('Bob', 'de Jager'),
			array('Milos', 'van der Waardt'),
			array('Wesley', 'Schenk'),
			array('Justine', 'Duindam')
		);

		foreach ($arr as $p) {
			$tmp = str_replace(' ', '', strtolower($p[0][0] . $p[1]));

			$user = new User();
			$user->Inlog = $tmp;
			$user->Wachtwoord = password_hash('banaan', PASSWORD_DEFAULT);
			$user->BedrijfID = 1;
			$user->Naam = $p[0] . ' ' . $p[1];
			$user->Functie = 'Medewerker';
			$user->Email = $tmp . '@bedrijf.nl';
			$user->Save();
		}
		*/
		/*
		$q = DB::Query('SELECT * FROM bedrijf WHERE BedrijfID > 1 AND BedrijfID NOT IN (SELECT DISTINCT UserBedrijf FROM user)');
		if($q->execute()) {
			while($row = $q->fetch()) {
				$user = new User();
				$user->Inlog = $row['BedrijfEmail'];
				$user->Wachtwoord = password_hash('banaan', PASSWORD_DEFAULT);
				$user->BedrijfID = $row['BedrijfID'];
				$user->Naam = $row['BedrijfNaam'];
				$user->Functie = 'Medewerker';
				$user->Email = $row['BedrijfEmail'];
				$user->Save();
			}
		}
		*/
	});
	
	$klein->dispatch();