<?php
	use Aptoma\Twig\Extension\MarkdownExtension;
	use Aptoma\Twig\Extension\MarkdownEngine;

	class View {
		public static $twigLoader;
		public static $twig;

		public static function Init() {
			self::$twigLoader = new Twig_Loader_Filesystem(__DIR__);
			self::$twig = new Twig_Environment(self::$twigLoader, array('debug' => true,));
			self::$twig->addExtension(new Twig_Extension_Debug());

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
				$default['isMedewerker'] = Auth::IsMedewerker($user);
				$default['isTeamLeider'] = Auth::IsTeamLeider($user);
				$default['isBeheerder'] = Auth::IsBeheerder($user);

				if(isset($user->Foto)) {
					$default['fotoEdit'] = filemtime(BASE_PATH . '/public/avatars/' . $user->Foto);
				}
			}

			$args = array_merge($default, $args);
			return self::$twig->render($file, $args);
		}

		public static function Error($message, $return = true) {
			if($return) {
				$message = $message . '<br><br><a href="javascript:history.back()">terug naar vorige pagina</a>';
			}
			return self::Render('error', array('message' => $message));
		}
	}

	View::Init();