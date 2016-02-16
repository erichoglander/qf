<?php
/**
 * Contains the log entity
 */
/**
 * Log entity
 *
 * A log entry
 * @see    addlog()
 * @author Eric Höglander
 */
class Log_Entity_Core extends Entity {

  /**
   * User entity
   * @var \User_Entity_Core
   */
  protected $User;


  /**
   * Get user entity connected to log item
   * @return \User_Entity_Code
   */
  public function user() {
    if ($this->User === null)
      $this->User = $this->getEntity("User", $this->get("user_id"));
    return $this->User;
  }
  
  
  /**
   * Database schema
   * @return array
   */
  protected function schema() {
    return [
      "table" => "log",
      "fields" => [
        "user_id" => [
          "type" => "uint",
          "default" => 0,
        ],
        "created" => [
          "type" => "uint",
          "default" => 0,
        ],
        "type" => [
          "type" => "enum",
          "values" => ["info", "success", "warning", "error"],
        ],
        "category" => [
          "type" => "varchar",
        ],
        "text" => [
          "type" => "text",
        ],
        "data" => [
          "type" => "blob",
          "serialize" => true,
        ],
        "ip" => [
          "type" => "varchar",
        ],
      ],
    ];
  }

}