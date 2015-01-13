<?php
	require_once(__DIR__ . '/vendor/autoload.php');

	require_once(__DIR__ . '/sql.php');

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

	$klein->respond('GET', '/', function() {
		global $twig;
		return $twig->render('index.twig');
	});

	$klein->respond('GET', '/login', function() {
		global $twig;
		return $twig->render('login.twig');
	});

	$klein->respond('POST', '/login', function() {
		$user = User::Where('UserInlog', $_POST['username']);
		var_dump($user);
	});

	$klein->respond('GET', '/phpinfo', function() {
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();

		return $info;
	});

	$klein->dispatch();