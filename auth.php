<?php
	class Auth {
		public static function LogIn($username, $password, $remember) {
			if(isset($_SESSION['uid'])) return;

			$user = User::Where('UserInlog', $username);
			if($user == false || !password_verify($password, $user->Wachtwoord)) {
				return false;
			}

			$_SESSION['uid'] = $user->ID;

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
	}