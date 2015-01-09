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

	$twigLoader = new Twig_Loader_Filesystem(__DIR__ . "/view");
	$twig = new Twig_Environment($twigLoader);

	$klein = new \Klein\Klein();

	$klein->respond(function() {
		global $twig;
		return $twig->render("index.twig");
	});

	$klein->dispatch();

	/*
	$faq = new FAQ();

	$faq->Titel = "how do i PHP";
	$faq->Omschrijving = "nanananna batman";
	$faq->Oplossing = "install gentoo";

	$faq->Save();

	var_dump($faq);

	var_dump(FAQ::Get(3));
	echo "<br><br>";
	*/