<?php
	class DB {
		public static $Connection;

		private static $Host = '93.191.133.193';
		private static $Database = 'admin_inf1a';
		private static $User = 'admin_inf1a';
		private static $Pass = 'O6vC9TtN';

		public static function Connect() {
			self::$Connection = new PDO('mysql:host=' . self::$Host . ';dbname=' . self::$Database, self::$User, self::$Pass);
			self::$Connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$Connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}

		public static function Query($sql) {
			try {
				return self::$Connection->query($sql);
			} catch(PDOException $ex) {
				$message = 'Kon query niet uitvoeren: ' . $ex->getMessage();
				die(View::Error($message));
			}

			return false;
		}

		public static function Prepare($sql, $values = null) {
			try {
				$sth = self::$Connection->prepare($sql);

				if(func_num_args() > 1) {
					if(is_array($values) && func_num_args() == 2) {
						$arguments = $values;
					} else {
						$arguments = func_get_args();
						array_shift($arguments);
					}

					if(!$sth->execute($arguments)) return false;
				}

				return $sth;
			} catch(PDOException $ex) {
				$message = 'Kon prepare niet uitvoeren: ' . $ex->getMessage();
				die(View::Error($message));
			}

			return false;
		}
	}

	try {
		DB::Connect();
	} catch(PDOException $ex) {
		$message = 'Kan geen verbinding met de database maken: ' . $ex->getMessage();
		die(View::Error($message));
	}