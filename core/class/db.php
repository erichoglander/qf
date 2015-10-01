<?php
class Db_Core {

	public $debug = false;
	public $connected = false;

	protected $exception_depth = 0;
	protected $errors = [];

	private $database, $user, $pass;
	private $db;

	public function connect($user, $pass, $db, $host = "localhost") {
		try {
			$this->db = new PDO("mysql:host=".$host.";dbname=".$db.";charset=utf8", $user, $pass);
			$this->db->query("SET COLLATION_CONNECTION=UTF8_SWEDISH_CI");
		}
		catch (PDOException $e) {
			$this->errors[] = $e->getMessage();
			return false;
		}
		$this->db->query("SET NAMES UTF8");
		$this->db->query("SET COLLATION_CONNECTION=UTF8_SWEDISH_CI");
		$this->connected = true;
		return true;
	}
	
	public function dump($file, $database, $user, $pass) {
		exec("mysqldump ".$database." --password=".$pass." --user=".$user." --single-transaction > ".$file);
		return true;
	}

	public function getErrors() {
		return $this->errors;
	}
	
	private function where(&$sql, &$vars, $conditions = []) {
		if (!empty($conditions)) {
			$sql.= " WHERE ";
			$condarr = [];
			$n = 0;
			foreach ($conditions as $i => $cond) {
				$n++;
				if (is_array($cond) && is_int($i)) {
					$pred = (isset($cond[2]) ? $cond[2] : "=");
					$key = $cond[0];
					$val = $cond[1];
				}
				else {
					$pred = "=";
					$key = $i;
					$val = $cond;
				}
				if (is_array($val)) {
					if ($pred == "=")
						$pred = " IN ";
					if ($pred == "!=")
						$pred = " NOT IN ";
					$or = [];
					foreach ($val as $j => $v) {
						$varkey = ":".$key.$n."_".$j;
						$vars[$varkey] = $v;
						$or[] = $varkey;
					}
					$condarr[] = $key.$pred."(".implode(",", $or).")";
				}
				else {
					if ($val === null) {
						if ($pred == "=")
							$condarr[] = $key." IS NULL";
						else if ($pred == "!=")
							$condarr[] = $key." IS NOT NULL";
					}
					else {
						$varkey = ":".$key.$n;
						$vars[$varkey] = $val;
						$condarr[] = $key.$pred.$varkey;
					}
				}
			}
			$sql.= implode(" && ", $condarr);
		}
	}
	
	public function query($sql, $param = []) {
		try  {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);
		}
		catch(PDOException $e) {
			$debug = [
				"exception" => $e->getMessage(),
				"backtrace" => debug_backtrace(),
			];
			if ($this->debug) 
				pr($debug);
			if ($this->exception_depth == 0) {
				$this->exception_depth++;
				addlog("database", "Exception", $debug, "error");
			}
			die("Ett fel uppstod med en fråga till databasen.");
		}
		$err = $stmt->errorInfo();
		if ($err[0] != 00000) {
			$this->errors[] = $err;
			$debug = [
				"backtrace" => debug_backtrace(),
				"errorInfo" => $err,
			];
			addlog("database", "error ".$err[0], $debug, "error");
			if ($this->debug)
				pr($debug);
			die("Ett fel uppstod med en fråga till databasen. (mysql error)");
		}
		return $stmt;
	}
	
	public function numRows($sql, $param = []) {
		$stmt = $this->query($sql, $param);
		return $stmt->rowCount();
	}
	
	public function insert($table, $data, $ignore = false) {
		if (!is_array($data))
			$data = (array) $data;
		$keys = array_keys($data);
		$vars = [];
		$holders = [];
		foreach ($data as $key => $val) {
			$vars[":".$key] = $val;
			$holders[] = ":".$key;
		}
		$sql = "INSERT ";
		if ($ignore)
			$sql.= "IGNORE ";
		$sql.= "INTO `".$table."`(`";
		$sql.= implode("`,`", $keys);
		$sql.= "`) VALUES(";
		$sql.= implode(",", $holders);
		$sql.= ")";
		$stmt = $this->query($sql, $vars);
		return (int) $this->db->lastInsertId();
	}
	
	public function insertIgnore($table, $data) {
		return $this->insert($table, $data, true);
	}
	
	public function update($table, $data, $conditions = []) {
		if (!is_array($data))
			$data = (array) $data;
		$vars = [];
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
	
	public function delete($table, $conditions = []) {
		$vars = [];
		$sql = "DELETE FROM `".$table."`";
		if (!empty($conditions))
			$this->where($sql, $vars, $conditions);
		$this->query($sql, $vars);
		return true;
	}
	
	public function getRow($sql, $param = []) {
		$stmt = $this->query($sql, $param);
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	public function getRows($sql, $param = []) {
		$stmt = $this->query($sql, $param);
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}

}
