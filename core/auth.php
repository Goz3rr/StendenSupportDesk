<?php
	class Auth {
		public static function MakePassword($length = 5) {
			$chars = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
			$max = strlen($chars) - 1;

			$pass = '';
			for ($i = 0; $i < $length; $i++) {
				$pass .= $chars[mt_rand(0, $max)];
			}
			return $pass;
		}

		public static function LogIn($username, $password, $remember) {
			if(isset($_SESSION['uid'])) return;

			$user = User::Where('UserInlog', $username);
			if($user == false || !password_verify($password, $user->Wachtwoord)) {
				return false;
			}

			$_SESSION['uid'] = $user->ID;
			$_SESSION['naam'] = $user->Naam;

			return true;
		}

		public static function LogOut() {
			if(!isset($_SESSION['uid'])) return;

			unset($_SESSION['uid']);
			session_destroy();
		}

		public static function IsLoggedIn() {
			return isset($_SESSION['uid']);
		}

		public static function CheckLoggedIn() {
			if(!Auth::IsLoggedIn()) {
				if($_SERVER['REQUEST_URI'] != '' && $_SERVER['REQUEST_URI'] != '/') {
					header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
				} else {
					header('Location: /login');
				}
				exit;
			}
		}

		public static function ValidPassword($pass) {
			return (strlen($pass) >= 5);
		}

		public static function IsTeamLeider($user = null) {
			if($user == null) {
				if(!Auth::IsLoggedIn()) return false;
				$user = $_SESSION['uid'];
			}

			if(is_numeric($user)) $user = User::Get($user);

			return $user->BedrijfID == 1 && $user->Functie == 'TeamLeider';
		}

		public static function IsMedewerker($user = null) {
			if($user == null) {
				if(!Auth::IsLoggedIn()) return false;
				$user = $_SESSION['uid'];
			}

			if(is_numeric($user)) $user = User::Get($user);

			return $user->BedrijfID == 1;
		}

		public static function IsBeheerder($user = null) {
			if($user == null) {
				if(!Auth::IsLoggedIn()) return false;
				$user = $_SESSION['uid'];
			}

			if(is_numeric($user)) $user = User::Get($user);

			return $user->BedrijfID == 1 && $user->Functie == 'Beheerder';
		}

		public static function CheckMedewerker() {
			Auth::CheckLoggedIn();

			if(!Auth::IsMedewerker()) {
				die(View::Error('Alleen medewerkers van stenden eHelp kunnen dat doen.'));
			}
		}

		public static function CheckBeheerder() {
			Auth::CheckLoggedIn();

			if(!Auth::IsBeheerder()) {
				die(View::Error('Alleen beheerders kunnen dat doen.'));
			}
		}
	}