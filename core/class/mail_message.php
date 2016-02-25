<?php
/**
 * Contains mail message class
 */

/**
 * Mail message class
 * @author Eric Höglander
 */
class MailMessage extends Model {

  /**
   * Mail object
   * @var \Mail_Core
   */
  protected $Mail;


  /**
   * Constructor
   */
  public function construct() {
    $this->Mail = $this->newClass("Mail");
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