<?php
	abstract class Model {
		public function __construct() {
			foreach (static::GetMap() as $field => $column) {
				$this->$field = null;
			}
		}

		public function GetMap() {
			return array();
		}

		public function GetMapKey($key) {
			$map = static::GetMap();
			return $map[$key];
		}

		public function GetTable() {
			return "";
		}

		public function Load($row) {
			foreach(static::GetMap() as $field => $column) {
				$this->$field = $row[$column];
			}
		}

		public function Save() {
			if($this->ID == null) {
				$columns = array();
				$placeholders = array();
				$values = array();

				foreach(static::GetMap() as $field => $column) {
					if($field == "ID" && $this->ID === null) continue;

					array_push($columns, "`" . $column . "`");
					array_push($placeholders, "?");
					array_push($values, $this->$field);
				}

				$columns = "(" . implode(", ", $columns) . ")";
				$placeholders = "(" . implode(", ", $placeholders) . ")";

				$sql = sprintf("INSERT INTO `%s` %s VALUES %s;", static::GetTable(), $columns, $placeholders);
			} else {
				$fields = array();
				$values = array();

				foreach(static::GetMap() as $field => $column) {
					if($field == "ID" && $this->ID === null) continue;

					array_push($fields, "`" . $column . "` = ?");
					array_push($values, $this->$field);
				}

				$fields = implode(", ", $fields);

				$sql = sprintf("UPDATE %s SET %s WHERE `ID` = %d", static::GetTable(), $fields, $this->ID);
			}

			try {
				return DB::Prepare($sql, $values);
			} catch(PDOException $ex) {
				echo "SQL Error: " . $ex->getMessage();
			}

			return false;
		}

		public function Delete() {
			if($this->ID == null) return false;

			return DB::Prepare(sprintf("DELETE FROM %s WHERE %s = ?;", static::GetTable(), static::GetMapKey('ID'])), $this->ID);
		}

		public static function Get($id) {
			if($id == null) return false;

			$q = DB::Prepare(sprintf("SELECT * FROM %s WHERE %s = ?;", static::GetTable(), static::GetMapKey('ID'])), $id);

			if($q) {
				$ent = new static();
				$ent->Load($q->fetch(PDO::FETCH_ASSOC));
				return $ent;
			}

			return false;
		}

		public static function GetAll() {
			$q = DB::Query("SELECT * FROM " . static::GetTable());

			try {
				if($q->execute()) {
					$out = array();
					while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
						$ent = new static();
						$ent->Load($row);

						array_push($out, $ent);
					}
					return $out;
				}
			} catch(PDOException $ex) {
				echo "SQL Error: " . $ex->getMessage();
			}

			return false;
		}

		public static function Where($column, $value = null) {
			if(is_array($column)) {

			} else {
				if($column == null || $value == null) return false;

				$q = DB::Prepare(sprintf("SELECT * FROM %s WHERE %s = ? LIMIT 1;", static::GetTable(), $column), $value);
				
				if($q && $q->rowCount() > 0) {
					$ent = new static();
					$ent->Load($q->fetch(PDO::FETCH_ASSOC));
					return $ent;
				}
			}

			return false;
 		}
	}