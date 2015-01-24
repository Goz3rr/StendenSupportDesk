<?php
	use Aptoma\Twig\Extension\MarkdownExtension;
	use Aptoma\Twig\Extension\MarkdownEngine;

	class View {
		public static $twigLoader;
		public static $twig;

		public static function Init() {
			self::$twigLoader = new Twig_Loader_Filesystem(__DIR__);
			self::$twig = new Twig_Environment(self::$twigLoader);

			$engine = new MarkdownEngine\PHPLeagueCommonMarkEngine();
			self::$twig->addExtension(new MarkdownExtension($engine));
		}

		public static function Render($file, $args = array()) {
			if(substr($file, -5) != '.twig') $file = $file . '.twig';

			$default = array(
				'now' => time()
			);

			if(isset($_SESSION['uid'])) {
				$user = User::Get($_SESSION['uid']);

				$default['gebruiker'] = $user;

				if(isset($user->Foto)) {
					$default['fotoEdit'] = filemtime(BASE_PATH . '/public/avatars/' . $user->Foto);
				}
			}

			$args = array_merge($default, $args);
			return self::$twig->render($file, $args);
		}
	}

	View::Init();