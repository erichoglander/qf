<?php
/**
 * Contains the mail class
 */

/**
 * The mail class
 * @author Eric Höglander
 */
class Mail_Core extends Model {

  /**
   * Sender e-mail address
   * @var string
   */
  public $from;
  
  /**
   * Sender name
   * @var string
   */
  public $from_name;

  /**
   * Receiver e-mail address
   * @var string
   */
  public $to;
  
  /**
   * Receiver name
   * @var string
   */
  public $to_name;

  /**
   * The e-mail subject
   * @var string
   */
  public $subject;

  /**
   * The e-mail body
   * @var string
   */
  public $message;

  /**
   * If true, adds a signature to the end of the message
   * @see signature
   * @var boolean
   */
  public $include_signature = true;

  /**
   * File attachments
   * @var array
   */
  public $attachments = [];

  /**
   * If true, send e-mail as HTML
   * @var bool
   */
  public $html = true;
  
  /**
   * Use an API
   * @var string
   */
  public $api = null;
  
  /**
   * API credentials
   * @var string
   */
  public $api_credentials = [];
  
  /**
   * Mandrill data to merge with message
   * @var array
   */
  public $mandrill_data = [];


  /**
   * Constructor
   */
  public function construct() {
    $this->setDefault();
    $this->setDefaultHeaders();
  }
  
  
  /**
   * Set default values
   */
  public function setDefault() {
    $this->from = "info@".BASE_DOMAIN;
  }

  /**
   * The signature to append to the end of the message
   * @return string
   */
  public function signature() {
    return '<hr><p>'.SITE_URL.'</p>';
  }

  /**
   * Prepare and send e-mail
   * @return bool
   */
  public function send() {

    if (!$this->from || !$this->to || !$this->message || !$this->subject) {
      addlog("mail", "Mail failed: Missing parameters", ["from" => $this->from, "to" => $this->to, "message" => $this->message, "subject" => $this->subject], "error");
      return false;
    }
    
    $message = $this->message;
    if ($this->include_signature)
      $message.= $this->signature();
    
    if ($this->api == "mandrill") {
      
      if (empty($this->api_credentials["key"])) {
        addlog("mail", "Mail failed: Missing Mandrill API-key", ["credentials" => $this->api_credentials], "error");
        return false;
      }
      
      $data = $this->mandrill_data + [
        "subject" => $this->subject,
        "from_email" => $this->from,
        "to" => [[
          "email" => $this->to,
          "type" => "to",
        ]],
        "headers" => [
          "Reply-To" => $this->from,
        ],
      ];
      if ($this->from_name)
        $data["from_name"] = $this->from_name;
      if ($this->to_name)
        $data["to"]["name"] = $this->to_name;
      if ($this->html)
        $data["html"] = $message;
      else
        $data["text"] = $message;
      
      if (!empty($this->attachments)) {
        foreach ($this->attachments as $attachment) {
          $file = $this->attachmentData($attachment);
          if (strlen($file["content"])) {
            $data["attachments"][] = [
              "type" => $file["type"],
              "name" => $file["name"],
              "content" => base64_encode($file["content"]),
            ];
          }
        }
      }
      
      $Mandrill = new Mandrill($this->api_credentials["key"]);
      $success = true;
      try {
        $data["result"] = $Mandrill->messages->send($data);
        foreach ($data["result"] as $result) {
          if (in_array($result["status"], ["rejected", "invalid"])) {
            $success = false;
            break;
          }
        }
      }
      catch (Mandrill_Error $e) {
        $data["error"] = $e->getMessage();
        $success = false;
      }
      
    }
    else {

      if ($this->html)
        $this->setHeader("Content-Type", "text/html; charset=UTF-8");

      $this->setHeader("From", $this->from);
      $this->setHeader("Reply-To", $this->from);
      $this->setHeader("Return-Path", $this->from);

      $data = [
        "headers" => $this->headers,
        "message" => $this->message,
      ];

      $this->prepareAttachments();

      $subject = "=?UTF-8?B?".base64_encode($this->subject)."?=";
      $headers = "";

      foreach ($this->headers as $key => $val)
        $headers.= $key.": ".$val."\r\n";
        
      $success = $this->mail($this->to, $subject, $message, $headers, "-f".$this->from);
      
    }

    if (!$success) {
      addlog("mail", "Mail failed ".$this->to." (".$this->subject.")", $data, "error");
      return false;
    }
    else {
      addlog("mail", "Mail sent to ".$this->to." (".$this->subject.")", $data, "success");
      return true;
    }
  }

  /**
   * Sets default headers
   */
  public function setDefaultHeaders() {
    $this->setHeaders($this->defaultHeaders());
  }

  /**
   * Default headers
   * @return array
   */
  public function defaultHeaders() {
    return [
      "Mime-Version" => "1.0",
      "Date" => date("D, j M Y H:i:s O"),
      "Content-Type" => "text/plain; charset=UTF-8",
      "X-Mailer" => "PHP/".phpversion(),
    ];
  }

  /**
   * Sets an e-mail header
   * @param string $key
   * @param string $val
   */
  public function setHeader($key, $val) {
    $this->headers[$key] = $val;
  }

  /**
   * Setter for $header
   * @param array $arr
   */
  public function setHeaders($arr) {
    foreach ($arr as $key => $val)
      $this->setHeader($key, $val);
  }

  /**
   * Attach a file to e-mail
   * @see    prepareAttachments
   * @param  string             $file Path to file
   */
  public function attach($file) {
    $this->attachments[] = $file;
  }


  /**
   * Send the mail
   * @see mail()
   * @param  string $to
   * @param  string $subject
   * @param  string $message
   * @param  string $headers
   * @return bool
   */
  protected function mail($to, $subject, $message, $headers, $params) {
    return mail($to, $subject, $message, $headers, $params);
  }

  /**
   * Prepare and encode attachments
   */
  protected function prepareAttachments() {
    if (!empty($this->attachments)) {
      $hash = md5(time().microtime());
      $tag = "PHP-mail=".$hash;
      $this->setHeader("Content-Type", 'multipart/mixed; boundary="'.$tag.'"');

      $pre =
      PHP_EOL.'--'.$tag.
      PHP_EOL.'Content-Type: text/'.($this->html ? "html" : "plain").'; charset=UTF-8'.
      PHP_EOL.'Content-Transfer-Encoding: 7bit'.
      PHP_EOL.PHP_EOL;

      $post = PHP_EOL.PHP_EOL.'--'.$tag.'--';

      $str = '';
      foreach ($this->attachments as $i => $attachment) {
        $file = $this->attachmentData($attachment);
        if (strlen($file["content"])) {
          $content = chunk_split(base64_encode($file["content"]));
          $str.=
            PHP_EOL.PHP_EOL.'--'.$tag.
            PHP_EOL.'Content-Type: '.$file["type"].'; name="'.$file["name"].'"'.
            PHP_EOL.'Content-Transfer-Encoding: base64'.
            PHP_EOL.'Content-Disposition: attachment; filename='.$file["name"].
            PHP_EOL.PHP_EOL.$content;
        }
      }
      $this->message = $pre.$this->message.$str.$post;
    }
  }
  
  protected function attachmentData($attachment) {
    $file = [
      "name" => null,
      "type" => null,
      "content" => null,
    ];
    if (is_array($attachment)) {
      if (isset($attachment["path"])) {
        $file["content"] = @file_get_contents($attachment["path"]);
      }
      else if (isset($attachment["content"])) {
        $file["content"] = $attachment["content"];
      }
      if (isset($attachment["name"])) {
        $file["name"] = $attachment["name"];
      }
      else if (isset($attachment["path"])) {
        $info = pathinfo($attachment["path"]);
        $file["name"] = $info["filename"];
      }
      if (isset($attachment["type"])) {
        $file["type"] = $attachment["type"];
      }
      else if (isset($attachment["path"])) {
        $file["type"] = mime_content_type($attachment["path"]);
      }
    }
    else {
      $file["content"] = @file_get_contents($attachment);
      $info = pathinfo($attachment);
      $file["type"] = mime_content_type($attachment);
      $file["name"] = $info["filename"];
    }
    return $file;
  }

}