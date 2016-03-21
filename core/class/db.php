<?php
/**
 * Contains database class
 */
/**
 * Database class
 * @author Eric Höglander
 */
class Db_Core {

  /**
   * If true, debugging information will be printed when an error occurs
   * @var boolean
   */
  public $debug = false;

  /**
   * True if connected to database
   * @var boolean
   */
  public $connected = false;

  /**
   * Used to prevent endless recursion when logging
   * @var int
   */
  protected $exception_depth = 0;

  /**
   * Contains error messages
   * @var array
   */
  protected $errors = [];

  /**
   * The native PDO object
   * @var PDO
   */
  private $db;


  /**
   * Connects to a database
   * @param  string $user
   * @param  string $pass
   * @param  string $db
   * @param  string $host
   * @return bool
   */
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
  
  /**
   * Dumps the database to a file
   * @param  string $file
   * @param  string $database
   * @param  string $user
   * @param  string $pass
   * @return bool             Always returns true
   */
  public function dump($file, $database, $user, $pass) {
    exec("mysqldump ".$database." --password=".$pass." --user=".$user." --single-transaction > ".$file);
    return true;
  }

  /**
   * Getter for $errors
   * @return array
   */
  public function getErrors() {
    return $this->errors;
  }
  
  /**
   * Is called when a database error occurs
   * @param  array $debug
   */
  public function error($debug) {
    if ($this->debug) {
      pr($debug);
      exit;
    }
    else {
      ob_flush();
      include filePath("view/default/500.php");
      exit;
    }
  }
  

  /**
   * Creates a WHERE clause from parameters
   *
   * Ex:
   * <code>
   * $this->where("SELECT * FROM mytable", [], ["foo" => "bar", ["mykeys", [2,3], "!="]]);
   * $sql: SELECT * FROM mytable WHERE foo = :foo0 && mykeys NOT IN (:mykeys1_0, :mykeys1_1)
   * $vars: [":foo0" => "bar", ":mykeys1_0" => 2, ":mykeys1_1" => 3]
   * </code>
   *
   * @see    insert
   * @see    update
   * @see    delete
   * @param  string $sql
   * @param  array  $vars
   * @param  array  $conditions
   * @return string
   */
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
  
  /**
   * Executes a query
   * @param  string|array $sql
   * @param  array        $param
   * @return PDOStatement
   */
  public function query($sql, $param = []) {
    if (is_array($sql))
      $sql = $this->compileQuery($sql);
    // Replace every array param with multiple primitive params
    foreach ($param as $key => $val) {
      if (is_array($val)) {
        $keys = [];
        foreach ($val as $i => $v) {
          $keys[] = $key.$i;
          $param[$key.$i] = $v;
        }
        $replace = "(".implode(", ", $keys).")";
        unset($param[$key]);
        $sql = str_replace($key, $replace, $sql);
      }
    }
    try  {
      $stmt = $this->db->prepare($sql);
      $stmt->execute($param);
    }
    catch(PDOException $e) {
      $debug = [
        "exception" => $e->getMessage(),
        "backtrace" => debug_backtrace(),
      ];
      if ($this->exception_depth == 0) {
        $this->exception_depth++;
        addlog("database", "Exception", $debug, "error");
      }
      $this->error($debug);
    }
    $err = $stmt->errorInfo();
    if ($err[0] != 00000) {
      $this->errors[] = $err;
      $debug = [
        "backtrace" => debug_backtrace(),
        "errorInfo" => $err,
      ];
      addlog("database", "error ".$err[0], $debug, "error");
      $this->error($debug);
    }
    return $stmt;
  }
  
  /**
   * Number of rows matching a query
   * @param  string $sql
   * @param  array  $param
   * @return int
   */
  public function numRows($sql, $param = []) {
    $stmt = $this->query($sql, $param);
    return $stmt->rowCount();
  }
  
  /**
   * Inserts data into the database
   * @param  string  $table
   * @param  array   $data
   * @param  boolean $ignore If true, adds IGNORE to the query
   * @return int             The id of the inserted row
   */
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
  
  /**
   * Insert data into the database if a row with the same primary keys doesnt exist
   * @see    insert
   * @param  string $table
   * @param  array $data
   * @return int           The id of the inserted row
   */
  public function insertIgnore($table, $data) {
    return $this->insert($table, $data, true);
  }
  
  /**
   * Updates a database row
   * @see    where
   * @param  string $table
   * @param  array  $data
   * @param  array  $conditions Conditions to be parsed
   * @return bool               Always returns true
   */
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
  
  /**
   * Deletes a row from the database
   * @param  string $table
   * @param  array  $conditions
   * @return bool               Always returns true
   */
  public function delete($table, $conditions = []) {
    $vars = [];
    $sql = "DELETE FROM `".$table."`";
    if (!empty($conditions))
      $this->where($sql, $vars, $conditions);
    $this->query($sql, $vars);
    return true;
  }
  
  /**
   * Fetches a single result
   * @param  string $sql
   * @param  array  $param
   * @return object
   */
  public function getRow($sql, $param = []) {
    $stmt = $this->query($sql, $param);
    return $stmt->fetch(PDO::FETCH_OBJ);
  }
  
  /**
   * Fetches all results
   * @param  string $sql
   * @param  array  $param
   * @return array
   */
  public function getRows($sql, $param = []) {
    $stmt = $this->query($sql, $param);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }
  
  /**
   * Compiles expressions into a query string
   * @param  array  $ex Expressions
   * @return string
   */
  public function compileQuery($ex) {
    
    // Possible expressions
    $ex+= [
      "from" => null,
      "options" => [],
      "cols" => ["*"],
      "joins" => [],
      "where" => [],
      "group" => [],
      "having" => [],
      "order" => [],
      "limit" => [],
    ];
    
    if (empty($ex["from"]))
      throw new Exception("Cannot compile query, missing FROM statement");
    if (empty($ex["cols"]))
      throw new Exception("Cannot compile query, missing columns");
    
    // Compile query
    $sql = "SELECT";
    if (!empty($ex["options"]))
      $sql.= " ".implode(" ", $ex["options"]);
    $sql.= " ".implode(", ", $ex["cols"])." FROM ".$ex["from"]; 
    if (!empty($ex["joins"]))
      $sql.= "\n".implode("\n", $ex["joins"]);
    if (!empty($ex["where"]))
      $sql.= "\nWHERE ".implode(" && ", $ex["where"]);
    if (!empty($ex["group"]))
      $sql.= "\nGROUP BY ".implode(", ", $ex["group"]);
    if (!empty($ex["having"]))
      $sql.= "\nHAVING ".implode(" && ", $ex["having"]);
    if (!empty($ex["order"]))
      $sql.= "\nORDER BY ".implode(", ", $ex["order"]);
    if (!empty($ex["limit"]))
      $sql.= "\nLIMIT ".implode(", ", $ex["limit"]);
    
    return $sql;
  }

}
