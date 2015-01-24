<?php
	class ProfileController extends Controller {
		public static function Routes($klein) {
			$klein->respond(array('GET', 'POST'), '/profile', 'ProfileController::Profile');
			$klein->respond(array('GET', 'POST'), '/settings', 'ProfileController::Settings');
		}

		public static function Profile($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$wachtwoord1 = $_POST['wachtwoord1'];
				$wachtwoord2 = $_POST['wachtwoord2'];

				$user = User::Get($_SESSION['uid']);

				if(!empty($wachtwoord1)) {
					if($wachtwoord1 == $wachtwoord2) {
						if(Auth::ValidPassword($wachtwoord1)) {
							$user->Wachtwoord = password_hash($wachtwoord1, PASSWORD_DEFAULT);
							$user->Save();
						} else {
							return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Wachtwoord moet uit ten minste 5 tekens bestaan'));
						}
					} else {
						return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Nieuwe wachtwoorden komen niet overeen'));
					}
				}

				if($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
					$check = getimagesize($_FILES['avatar']['tmp_name']);
					$ext = pathinfo(basename($_FILES['avatar']['name']), PATHINFO_EXTENSION);
					if($check !== false && in_array(strtolower($ext), array('jpg', 'png', 'jpeg', 'gif'))) {
						if ($_FILES['avatar']['size'] < 2000000) {
							$file = $_SESSION['uid'] . '.' . $ext;
							if(move_uploaded_file($_FILES['avatar']['tmp_name'], BASE_PATH . '/public/avatars/' . $file)) {
								$user->Foto = $file;
								$user->Save();
							} else {
								return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Avatar kon niet geupload worden'));
							}
						} else {
							return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Afbeelding is te groot'));
						}
					} else {
						return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Bestand is geen afbeelding'));
					}
				} elseif($_FILES['avatar']['error'] != UPLOAD_ERR_NO_FILE) {
					return View::Render('profile', array('profiel' => $user, 'errormsg' => 'Avatar kon niet geupload worden'));
				}

				$response->redirect('/')->send();
			} else {
				$user = User::Get($_SESSION['uid']);
				return View::Render('profile', array('profiel' => $user));
			}
		}

		public static function Settings($request, $response, $service) {
			Auth::CheckLoggedIn();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

			} else {
				return View::Render('settings');
			}
		}
	}