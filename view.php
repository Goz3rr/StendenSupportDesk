<?php
	class View {
		public static $twigLoader;
		public static $twig;

		public static function Init() {
			self::$twigLoader = new Twig_Loader_Filesystem(__DIR__ . '/view');
			self::$twig = new Twig_Environment(self::$twigLoader);
		}

		public static function Render($file, $args = array()) {
			if(substr($file, -5) != '.twig') $file = $file . '.twig';

			$default = array();

			if(isset($_SESSION['uid'])) {
				$default['gebruikerUID'] = $_SESSION['uid'];
				$default['gebruikerNaam'] = $_SESSION['naam'];
			}

			$args = array_merge($default, $args);
			return self::$twig->render($file, $args);
		}
	}

	View::Init();