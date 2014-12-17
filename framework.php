<?php
	require_once(__DIR__ . '/vendor/autoload.php'); // laad composer

	$klein = new \Klein\Klein(); // router om requests aftehandelen

	$klein->respond(function () {
		return 'Hello World!';
	});

	$klein->dispatch();