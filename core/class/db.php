<?php

class Db {

	public $errors = Array();

	private $database, $user, $pass;
	private $db;

	public function __construct($user, $pass, $db) {
		try {
			$this->db = new PDO("mysql:host=localhost;dbname=".$db.";charset=utf8", $user, $pass);
			$this->db->query("SET COLLATION_CONNECTION=UTF8_SWEDISH_CI");
		}
		catch (PDOException $e) {
			die("Oj då, nu är det lite många som är inne på sidan och vi klarar inte trycket. Försök gärna igen om en stund.");
		}
		$this->database = $db;
		$this->user = $user;
		$this->pass = $pass;
		$this->db->query("SET NAMES UTF8");
		$this->db->query("SET COLLATION_CONNECTION=UTF8_SWEDISH_CI");
	}
	
	public function dump($file) {
		exec("mysqldump ".$this->database." --password=".$this->pass." --user=".$this->user." --single-transaction > ".$file);
		return true;
	}
	
	public function error($e = null) {
		die("Ett fel uppstod");
	}
	
	private function where(&$sql, &$vars, $conditions = Array()) {
		if (!empty($conditions)) {
			$sql.= " WHERE ";
			$condarr = Array();
			$n = 0;
			foreach ($conditions as $i => $cond) {
				$n++;
				if (is_array($cond)) {
					$pred = (isset($cond[2]) ? $cond[2] : null);
					$key = $cond[0];
					$val = $cond[1];
				}
				else {
					$pred = "=";
					$key = $i;
					$val = $cond;
				}
				if (is_array($val)) {
					if (!$pred || $pred == "=")
						$pred = " IN ";
					if ($pred == "!=")
						$pred = " NOT IN ";
					$or = Array();
					foreach ($val as $j => $v) {
						$varkey = ":".$key.$n."_".$j;
						$vars[$varkey] = $v;
						$or[] = $varkey;
					}
					$condarr[] = $key.$pred."(".implode(",", $or).")";
				}
				else {
					if (!$pred)
						$pred = "=";
					$varkey = ":".$key.$n;
					$vars[$varkey] = $val;
					$condarr[] = $key.$pred.$varkey;
				}
			}
			$sql.= implode(" && ", $condarr);
		}
	}
	
	public function query($sql, $param = Array()) {
		try  {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);
		}
		catch(PDOException $e) {
			die("Ett fel uppstod med en fråga till databasen.");
		}
		$err = $stmt->errorInfo();
		if ($err[0] != 00000) {
			$this->errors[] = $err;
			$debug = print_r(debug_backtrace(), true);
			$debug.= print_r($err, true);
			addlog("database", "error ".$err[0], $debug);
			die("Ett fel uppstod med en fråga till databasen. (mysql error)");
		}
		return $stmt;
	}
	
	public function numRows($sql, $param = Array()) {
		$stmt = $this->query($sql, $param);
		return $stmt->rowCount();
	}
	
	public function insert($table, $data) {
		if (!is_array($data))
			$data = (array) $data;
		$keys = array_keys($data);
		$vars = Array();
		$holders = Array();
		foreach ($data as $key => $val) {
			$vars[":".$key] = $val;
			$holders[] = ":".$key;
		}
		$sql = "INSERT INTO `".$table."`(`";
		$sql.= implode("`,`", $keys);
		$sql.= "`) VALUES(";
		$sql.= implode(",", $holders);
		$sql.= ")";
		$stmt = $this->query($sql, $vars);
		return (int) $this->db->lastInsertId();
	}
	
	public function update($table, $data, $conditions = Array()) {
		if (!is_array($data))
			$data = (array) $data;
		$vars = Array();
		$sql = "UPDATE `".$table."` SET ";
		foreach ($data as $key => $val) {
			$vars[":".$key] = $val;
			$sql.= "`".$key."`=:".$key.", ";
		}
		$sql = substr($sql, 0, -2);
		$this->where($sql, $vars, $conditions);
		$this->query($sql, $vars);
		return true;
	}
	
	public function delete($table, $conditions) {
		$vars = Array();
		$sql = "DELETE FROM `".$table."`";
		$this->where($sql, $vars, $conditions);
		$this->query($sql, $vars);
		return true;
	}
	
	public function getRow($sql, $param = Array()) {
		$stmt = $this->query($sql, $param);
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	public function getRows($sql, $param = Array()) {
		$stmt = $this->query($sql, $param);
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}

}
