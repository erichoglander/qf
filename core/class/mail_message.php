<?php
/**
 * Contains mail message class
 */

/**
 * Mail message class
 * @author Eric Höglander
 */
class MailMessage {
  
  /**
   * Database object
   * @var \Db_Core
   */
  protected $Db;

  /**
   * Mail object
   * @var \Mail_Core
   */
  protected $Mail;


  /**
   * Constructor
   * @param \Db_Core $Db
   */
  public function __construct($Db) {
    $this->Db = $Db;
    $this->Config = newClass("Config");
    $this->Mail = newClass("Mail", $this->Db);
  }

  /**
   * Send the message
   * @param  string $to   The recipient email address
   * @param  array  $vars
   * @return bool
   */
  public function send($to, $vars = []) {
    $this->Mail->to = $to;
    $this->prepare($vars);
    return $this->Mail->send();
  }
  

  /**
   * Prepare message for sending
   * @param  array $vars
   */
  protected function prepare($vars = []) {
    
  }

}