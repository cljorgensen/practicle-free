<?php
/**
 * Universal PHP Mailer
 *
 * @version    4.5 (2018-10-07 06:06:00 UTC)
 * @author     Peter Kahl <https://github.com/peterkahl>
 *             SMTP methods are a fork of
 *             <https://github.com/PHPMailer/PHPMailer/blob/master/class.smtp.php>
 * @copyright  2016-2018 Peter Kahl
 * @license    Apache License, Version 2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace peterkahl\universalPHPmailer;

use \Exception;
use \SplFileObject;
use \DateTime;
use \DateTimeZone;

class universalPHPmailer {

  /**
   * Version
   * @var string
   */
  const VERSION = '4.5';

  /**
   * Method used to send mail
   * @var string
   * Valid values are 'mail', 'smtp'
   */
  public $mailMethod = 'smtp';

  /**
   * Hostname of SMTP server we are connecting to.
   * @var string
   * For local SMTP server, try 'localhost'.
   * For Gmail SMTP server, try 'localhost'.
   */
  public $SMTPserver     = 'localhost';

  /**
   * SMTP port to connect to
   * @var integer
   */
  public $SMTPport       = 25;

  /**
   * SMTP timeout
   * @var integer
   */
  public $SMTPtimeout    = 30;

  /**
   * SMTP time limit
   * @var integer
   */
  public $SMTPtimeLimit  = 30;

  /**
   * SMTP login user name
   * @var string
   */
  public $SMTPusername   = '';

  /**
   * SMTP login password
   * @var string
   */
  public $SMTPpassword   = '';

  /**
   * Authentication Mechanism
   * @var string
   * Valid values are 'PLAIN', 'LOGIN', 'CRAM-MD5'
   */
  public $SMTPauthMech   = 'PLAIN';

  /**
   * Require SMTP connection to be secure.
   * If you're using a remote SMTP server, you should be using secure
   * connection.
   * @var boolean
   */
  public $forceSMTPsecure = false;

  /**
   * Filename (incl. path) of CA certificate
   * @var string
   * You may download and install on your server this Mozilla CA bundle
   * from this page:
   * <https://curl.haxx.se/docs/caextract.html>
   */
  public $CAfile         = '';

  /**
   * Our hostname for SMTP HELO
   * This is how we identify ourselves when connecting to the SMTP server.
   * @var string
   * This should be FQDN, or if you use IP address, it should be in
   * square brackets. If you are connecting to an external SMTP server,
   * it may be necessary to use your actual FQDN that is FcRDNS.
   * May need to be RFC2821 compliant.
   */
  public $SMTPhelo = 'localhost';

  /**
   * X-Mailer header
   * @var mixed possible values
   * true (boolean) ......... default X-Mailer header will be inserted
   * false (boolean) ........ X-Mailer header will not be inserted
   * '' (empty string) ...... X-Mailer header will not be inserted
   * 'Joe Blow\'s Mailer' ... given string will be used as X-Mailer header
   */
  public $Xmailer = true;

  /**
   * Absolute path to the writeable cache directory
   * @var string
   */
  public $CacheDir = '';

  /**
   * File name of debug log, *starting with slash*.
   * @var string
   */
  const LOGFILENAME = '/SMTP.log';

  /**
   * Cache file name of debug log,
   * created by concatenation of CacheDir + LOGFILENAME
   * @var string
   */
  private $CacheFile;

  /**
   * Enable debug
   * @var boolean
   * Consider disabling for better performance.
   */
  public $debugEnable = true;

  /**
   * Method of debug
   * @var string
   * Valid values ... 'echo', 'log'
   */
  public $debugMethod = 'log';

  /**
   * Local server timezone offset from GMT in seconds.
   * Used for timestamp in logs.
   * @var integer
   * Value 0 corresponds to GMT timezone.
   */
  public $serverTZoffset = 0;

  /**
   * Recipent To:
   * Specify one or more To: recipients.
   * @var array
   * Valid format --
   * array(email => name)
   */
  public $RecipientTo;

  /**
   * Sanitised version of Recipient To: array
   * @var array
   */
  private $SanitisedTo;

  /**
   * Recipent Cc:
   * Specify one or more Cc: recipients.
   * @var array
   * Valid format --
   * array(email => name)
   */
  public $RecipientCc;

  /**
   * Sanitised version of Recipient Cc: array
   * @var array
   */
  private $SanitisedCc;

  /**
   * Recipent Bcc:
   * Specify one or more Bcc: recipients.
   * @var array
   * Valid format --
   * array(email => name)
   */
  public $RecipientBcc;

  /**
   * Sanitised version of Recipient Bcc: array
   * @var array
   */
  private $SanitisedBcc;

  /**
   * Sender for header From.
   * Specify one sender.
   * @var array
   * Valid format --
   * array(email => name)
   */
  public $SenderFrom;

  /**
   * Sanitised version of SenderFrom array.
   * @var array
   */
  private $SanitisedFrom;

  /**
   * Sender for header Sender.
   * Specify one sender.
   * @var array
   * Valid format --
   * array(email => name)
   */
  public $SenderSender;

  /**
   * Sanitised version of SenderSender array.
   * @var array
   */
  private $SanitisedSender;

  /**
   * Subject
   * @var string
   */
  public $Subject;

  /**
   * Sanitised version of Subject.
   * @var string
   */
  private $SanitisedSubject;

  /**
   * @var string
   */
  public $textPlain;

  /**
   * @var string
   */
  public $textHtml;

  /**
   * How you want the text encoded?
   * @var string
   * Valid values are 'base64' OR 'quoted-printable'
   */
  public $textEncoding;

  /**
   * Language of text (optional)
   * If set, will create header 'Content-Language: en-gb'
   * @var string (e.g. 'en-gb')
   */
  public $textContentLanguage;

  /**
   * Date string for the Date header (optional).
   * Must conform to RFC5322
   * @var string
   */
  public $DateHeaderStr;

  /**
   * Date string for the Reply-To header (optional).
   * Specify 1 or more pairs.
   * @var mixed
   * Valid format --
   * array(email => name)
   */
  public $ReplyToHeader;

  /**
   * Sanitised version of ReplyToHeader.
   * @var mixed
   */
  private $SanitisedReplyToHeader;

  /**
   * Date header UNIX timezone (optional).
   * If you define this, the Date header will be converted into
   * desired timezone, else GMT will be used.
   * @var string ... Examples: 'Europe/London', 'Asia/Kabul', 'America/Kentucky/Monticello'
   */
  public $DateHeaderZone;

  /**
   * Specify custom headers, if any
   * @var array
   * Required structure:
   *       array(
   *            'In-Reply-To' => '<MESSAGE.ID@domain.tld>',
   *            'References'  => '<MESSAGE.ID@domain.tld>',
   *            'X-License'   => 'LS2P92yoGy4nbeIvWWWpWqjOloZUNO6a',
   *            )
   *
   * This class will econde header value per RFC2047, if multibyte.
   * If you define email address containing header with a display name, make
   * sure you format the display name per RFC5322, or just use the method
   * 'formatDisplayName' for doing so.
   *
   * All email addresses must comply with RFC5322 (only printable ASCII and '@').
   * IDN and Unicode addresses must be converted to ASCII.
   * It it advised that you validate all email adresses prior to using
   * this package.
   */
  public $customHeaders;

  /**
   * These headers cannot be defined in the property customHeaders (above).
   * @var array
   */
  private $ReservedHeaders = array(
    'to',
    'cc',
    'bcc',
    'from',
    'date',
    'sender',
    'reply-to',
    'x-mailer',
    'mime-version',
  );

  /**
   * Hostname for use in message ID and for Content-ID.
   * This could be any value, but it is recommended to use a valid hostname.
   * @var string
   */
  public $hostName;

  /**
   * Email from
   * Automatically generated. Used for SMTP handshake.
   * @var string
   */
  private $fromEmail;

  /**
   * Message ID
   * Automatically generated.
   * @var string
   */
  private $messageId;

  /**
   * Randomly generated string.
   * @var string
   */
  private $RBstring;

  /**
   * Multipart message boundaries
   * @var array
   */
  private $boundary;

  /**
   * Inline images
   * @var array
   */
  private $inlineImage;

  /**
   * The key of the inlineImage array
   * @var integer
   */
  private $inlineImageKey;

  /**
   * Attachment
   * @var array
   */
  private $attachment;

  /**
   * The key of the attachment array
   * @var integer
   */
  private $attachmentKey;

  /**
   * MIME headers
   * @var array
   */
  private $mimeHeaders;

  /**
   * MIME body
   * @var string
   */
  private $mimeBody;

  /**
   * Complete & final string of the message (headers + body)
   * @var string
   */
  private $mailString;

  /**
   * Line wrapping limit per RFC5322
   * https://tools.ietf.org/html/rfc5322.html#section-2.1.1
   * @var integer
   */
  const WRAP_LEN = 76;

  /**
   * Line length limit per RFC5322
   * https://tools.ietf.org/html/rfc5322.html#section-2.1.1
   * @var integer
   */
  const LINE_LEN_MAX = 998;

  /**
   * Maximum length of multibyte segment for use in headers.
   * This is needed for proper line folding.
   * @var integer
   */
  const MB_LEN_MAX = 7;

  /**
   * Line break string
   * @var string
   */
  const CRLF = "\r\n";

  /**
   * Character set of the message
   * @var string
   */
  const CHARSET = 'utf-8';

  /**
   * @var integer
   */
  private $SMTPsocket;

  /**
   * Extensions that are supported by SMTP server
   * @var array('SIZE' => '20971520', 'STARTTLS' => true)
   */
  private $SMTPextensions;

  /**
   * @var integer
   */
  private $EpochConnectionOpened;

  /**
   * Count successful sends
   * @var integer
   */
  private $CounterSuccess;

  /**
   * Count rejections (unsuccessful sends)
   * @var integer
   */
  private $CounterReject;

  /**
   * @var integer
   */
  private $last_reply;

  /**
   * @var integer
   */
  private $mtypes;


  public function __construct() {
    $this->inlineImageKey = 0;
    $this->inlineImage    = array();
    $this->attachmentKey  = 0;
    $this->attachment     = array();
    $this->last_reply     = '';
    $this->CounterSuccess = 0;
    $this->CounterReject  = 0;
    $this->hostName       = '';
  }


  public function __destruct() {
    if ($this->mailMethod == 'smtp' && $this->isConnectionOpen()) {
      $this->SMTPconnectionClose();
    }
  }


  /**
   * Sends email message with given content.
   * @throws \Exception
   * @return mixed
   */
  public function sendMessage() {

    if (empty($this->CacheDir)) {
      throw new Exception('May be you didn\'t define the cache directory?');
    }
    $this->CacheFile = str_replace('\\\\', '\\', $this->CacheDir . self::LOGFILENAME);

    if ($this->mailMethod != 'smtp' && $this->mailMethod != 'mail') {
      throw new Exception('Illegal value of property mailMethod');
    }

    $recipientDefined = false;
    $senderDefined    = false;

    $this->SanitisedTo = array();
    if (!empty($this->RecipientTo)) {
      foreach ($this->RecipientTo as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedTo[$email] = $name;
          $recipientDefined = true;
        }
      }
    }

    $this->SanitisedCc = array();
    if (!empty($this->RecipientCc)) {
      foreach ($this->RecipientCc as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedCc[$email] = $name;
          $recipientDefined = true;
        }
      }
    }

    $this->SanitisedBcc = array();
    if (!empty($this->RecipientBcc)) {
      foreach ($this->RecipientBcc as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedBcc[$email] = $name;
          $recipientDefined = true;
        }
      }
    }

    if (!$recipientDefined) {
      throw new Exception('No valid recipient is defined');
    }

    if (!empty($this->SanitisedCc) && empty($this->SanitisedTo)) {
      throw new Exception('You cannot set Cc: header because you have no To: recipients');
    }

    $this->SanitisedFrom = array();
    if (!empty($this->SenderFrom)) {
      foreach ($this->SenderFrom as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedFrom[$email] = $name;
          $this->fromEmail = $email; # used for SMTP handshake
          $senderDefined = true;
          break; # Only 1 pair
        }
      }
    }

    if (!$senderDefined) {
      throw new Exception('No valid From: or Sender: headers defined');
    }

    $this->SanitisedSender = array();
    if (!empty($this->SenderSender)) {
      foreach ($this->SenderSender as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedSender[$email] = $name;
          $this->fromEmail = $email; # used for SMTP handshake
          $senderDefined = true;
          break; # Only 1 pair
        }
      }
    }

    $this->SanitisedReplyToHeader = array();
    if (!empty($this->ReplyToHeader)) {
      foreach ($this->ReplyToHeader as $email => $name) {
        if ($this->ValidEmail($email)) {
          $name = $this->sanitiseHeader($name);
          $this->SanitisedReplyToHeader[$email] = $name;
        }
      }
    }

    $this->SanitisedSubject = $this->sanitiseHeader($this->Subject);

    /**
     * If host name isn't given, we will create it from the
     * domain part of sender's email address.
     */
    if (empty($this->hostName)) {
      $this->hostName = $this->endExplode('@', $this->fromEmail);
    }


    /**
     * Boundaries must be unique for each message.
     */
    $this->unsetBoundaries();

    $this->composeMessage();

    if ($this->mailMethod == 'smtp' && !$this->isConnectionOpen()) {
      if (!$this->SMTPconnectionOpen()) {
        $this->debug('Failed to establish SMTP connection.');
        return array();
      }
    }

    $this->mailString = implode(self::CRLF, $this->mimeHeaders) . self::CRLF . self::CRLF . $this->mimeBody;

    switch ($this->mailMethod) {
      #########################################
      case 'mail':
        if (!empty($this->SanitisedTo)) {
          $to = preg_replace('/^To:\s/', '', $this->encodeNameHeader('To', $this->SanitisedTo));
        }
        else {
          $to = 'undisclosed-recipients:;';
        }
        $subject = preg_replace('/^Subject:\s/', '', $this->encodeHeader('Subject', $this->SanitisedSubject));
        $headers = implode(self::CRLF, $this->mimeHeaders) . self::CRLF;
        if (mail($to, $subject, $this->mimeBody, $headers, '-f'. $this->fromEmail) !== false) {
          $this->CounterSuccess++;
          return array(
            'messgid' => $this->getMessageId(),
            'mailstr' => $this->mailString,
          );
        }
        $this->CounterReject++;
        return array();
      #########################################
      case 'smtp':
        # Check for excessive length
        if ($this->isCapable('SIZE')) {
          $len = strlen($this->mailString);
          if ($len > $this->SMTPextensions['SIZE']) {
            $this->debug('Message size '. $len .' exceeds server\'s limit of '. $this->SMTPextensions['SIZE'] .' bytes.');
            $this->CounterReject++;
            return array();
          }
        }
        if ($this->SMTPmail()) {
          $this->CounterSuccess++;
          return array(
            'messgid' => $this->getMessageId(),
            'mailstr' => $this->mailString,
          );
        }
        $this->CounterReject++;
        return array();
      #########################################
    }
  }


  /**
   * Validates email address.
   * @param  string  $str
   * @return boolean
   * @throws \Exception
   */
  private function ValidEmail($str) {
    if (preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $str)) {
      return true;
    }
    throw new Exception('Invalid email address');
  }


  /**
   * Handshakes and mail sending with SMTP server.
   * @return boolean
   */
  private function SMTPmail() {

    $this->debug('---------------------------------------------------------');

    if (!$this->sendCommand('MAIL FROM:<'. $this->fromEmail .'>', 250)) {
      return false;
    }

    if (!empty($this->SanitisedTo)) {
      foreach ($this->SanitisedTo as $email => $name) {
        if (!$this->sendCommand('RCPT TO:<'. $email .'>', array(250, 251))) {
          return false;
        }
      }
    }

    if (!empty($this->SanitisedCc)) {
      foreach ($this->SanitisedCc as $email => $name) {
        if (!$this->sendCommand('RCPT TO:<'. $email .'>', array(250, 251))) {
          return false;
        }
      }
    }

    if (!empty($this->SanitisedBcc)) {
      foreach ($this->SanitisedBcc as $email => $name) {
        if (!$this->sendCommand('RCPT TO:<'. $email .'>', array(250, 251))) {
          return false;
        }
      }
    }

    if (!$this->sendCommand('DATA', 354)) {
      return false;
    }

    if (!$this->sendCommand($this->mailString . self::CRLF .'.', 250)) {
      return false;
    }

    return true;
  }


  /**
   * Establishes connection (optionally SSL secure, authenticated)
   * with SMTP server.
   * @return boolean
   */
  private function SMTPconnectionOpen() {
    # Start the stopwatch, counters.
    $this->EpochConnectionOpened = microtime(true);
    $this->CounterSuccess = 0;

    $this->debug('#########################################################');

    $context = stream_context_create();

    if ($this->forceSMTPsecure) {
      if (!empty($this->CAfile)) {
        $this->debug('Using CA certificate file '. $this->CAfile);
        stream_context_set_option($context, 'ssl', 'cafile',            $this->CAfile);
        stream_context_set_option($context, 'ssl', 'verify_host',       true);
        stream_context_set_option($context, 'ssl', 'verify_peer',       true);
        stream_context_set_option($context, 'ssl', 'verify_peer_name',  true);
        stream_context_set_option($context, 'ssl', 'allow_self_signed', false);
      }
      else {
        stream_context_set_option($context, 'ssl', 'verify_host',       false);
        stream_context_set_option($context, 'ssl', 'verify_peer',       false);
        stream_context_set_option($context, 'ssl', 'verify_peer_name',  false);
        stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
      }
    }

    $this->SMTPsocket = stream_socket_client($this->SMTPserver .':'. $this->SMTPport, $errno, $errstr, $this->SMTPtimeout, STREAM_CLIENT_CONNECT, $context);

    $this->debug('Connecting to server '. strtoupper($this->SMTPserver) .' on port '. $this->SMTPport .' ...');

    if (!is_resource($this->SMTPsocket)) {
      $this->debug('ERROR: Failed to connect: '. $errstr .' ('. $errno .')');
      return false;
    }

    $greeting = $this->get_lines();
    $this->debug('<<< '. $greeting);

    if (substr($greeting, 0, 3) != '220') {
      return false;
    }

    if (!$this->sendCommand('EHLO '. $this->SMTPhelo, 250)) {
      return false;
    }
/*
250-mail.domain.example
250-PIPELINING
250-SIZE 20971520
250-ETRN
250-STARTTLS
250-ENHANCEDSTATUSCODES
250-8BITMIME
250-DSN
250 SMTPUTF8
*/
    $this->updateExtensionList();

    if ($this->isCapable('STARTTLS')) {
      if (!$this->forceSMTPsecure && (!empty($this->CAfile) || !empty($this->SMTPusername) || !empty($this->SMTPpassword))) {
        $this->debug('ERROR: SMTPsecure is FALSE (disabled).');
        return false;
      }
      if (!$this->startTLS()) {
        $this->debug('ERROR: Server '. strtoupper($this->SMTPserver) .' gave unexpected reply to STARTTLS command.');
        return false;
      }
    }
    elseif ($this->forceSMTPsecure) {
      $this->debug('ERROR: Server '. strtoupper($this->SMTPserver) .' does not support STARTTLS.');
      return false;
    }

    if ($this->isCapable('AUTH')) {
      if (!$this->authenticate()) {
        $this->debug('ERROR: Authentication failed.');
        return false;
      }
    }

    return true; # Successfully connected to server.
  }


  /**
   * Is SMTP server capable?
   * @param  string  $ext
   * @return boolean
   */
  private function isCapable($ext) {
    if (empty($this->SMTPextensions)) {
      return false;
    }
    return array_key_exists($ext, $this->SMTPextensions);
  }


  /**
   * Gets us authenticated to SMTP server.
   * @return boolean
   */
  private function authenticate() {

    if (empty($this->SMTPusername) || empty($this->SMTPpassword)) {
      $this->debug('ERROR: Authentication requires non-empty username and password.');
      return false;
    }

    $supported = array('PLAIN', 'LOGIN', 'CRAM-MD5');

    $this->SMTPauthMech = strtoupper($this->SMTPauthMech);

    if (!in_array($this->SMTPauthMech, $supported)) {
      $this->debug('ERROR: Library does not support auth mechanism '. $this->SMTPauthMech);
      return false;
    }

    if (!in_array($this->SMTPauthMech, $this->SMTPextensions['AUTH'])) {
      $this->debug('ERROR: SMTP server does not support auth mechanism '. $this->SMTPauthMech);
      return false;
    }

    switch ($this->SMTPauthMech) {
      case 'PLAIN':
        if (!$this->sendCommand('AUTH PLAIN', 334)) {
          return false;
        }
        if (!$this->sendCommand(base64_encode("\0". $this->SMTPusername ."\0". $this->SMTPpassword), 235)) {
          return false;
        }
        break;
      case 'LOGIN':
        if (!$this->sendCommand('AUTH LOGIN', 334)) {
          return false;
        }
        if (!$this->sendCommand(base64_encode($this->SMTPusername), 334)) {
          return false;
        }
        if (!$this->sendCommand(base64_encode($this->SMTPpassword), 235)) {
          return false;
        }
        break;
      case 'CRAM-MD5':
        if (!$this->sendCommand('AUTH CRAM-MD5', 334)) {
          return false;
        }
        $challenge = base64_decode(substr($this->last_reply, 4));
        $response  = $this->SMTPusername .' '. hash_hmac('md5', $challenge, $this->SMTPpassword);
        if (!$this->sendCommand('Username', base64_encode($response), 235)) {
          return false;
        }
        break;
      default:
        $this->debug('ERROR: Unsupported authentication mechanism '. $this->SMTPauthMech);
        return false;
    }

    return true;
  }


  /**
   * STARTTLS to SMTP server.
   * @return boolean
   */
  private function startTLS() {
    if (!$this->sendCommand('STARTTLS', 220)) {
      return false;
    }
    $method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
    if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
      $method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
      $method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
    }
    $this->debug('Selected CRYPTO METHOD '. $method);
    stream_socket_enable_crypto($this->SMTPsocket, true, $method);
    if (!$this->sendCommand('EHLO '. $this->SMTPhelo, 250)) {
      return false;
    }
/*
250-mail.domain.example
250-PIPELINING
250-SIZE 20971520
250-ETRN
250-AUTH PLAIN LOGIN CRAM-MD5
250-ENHANCEDSTATUSCODES
250-8BITMIME
250-DSN
250 SMTPUTF8
*/
    $this->updateExtensionList();
    return true;
  }


  /**
   * Updates list of supported extensions of SMTP server.
   *
   */
  private function updateExtensionList() {
    $ext = explode(self::CRLF, trim($this->last_reply));
    foreach ($ext as $val) {
      if (preg_match('/^\d{3}(\ |-)([A-Z0-9]{2,66})(\ ([A-Z0-9\ -]{1,128}))?$/', $val, $match)) {
        # Only if extension isn't in our array
        if (empty($this->SMTPextensions[$match[2]])) {
          if (!empty($match[2]) && $match[2] == 'SIZE' && !empty($match[4])) {
            $this->SMTPextensions[$match[2]] = $match[4];               # 'SIZE' => '20971520'
          }
          elseif (!empty($match[2]) && $match[2] == 'AUTH' && !empty($match[4])) {
            $this->SMTPextensions[$match[2]] = explode(' ', $match[4]); # 'AUTH' => array('PLAIN', 'LOGIN', 'CRAM-MD5')
          }
          else {
            $this->SMTPextensions[$match[2]] = true;                    # 'STARTTLS' => true
          }
        }
      }
    }
  }


  /**
   * Closes SMTP connection.
   *
   */
  private function SMTPconnectionClose() {
    $this->debug('---------------------------------------------------------');
    $this->sendCommand('QUIT', 221);
    $this->debug('---------------------------------------------------------');
    $this->debug('MESSAGES-SENT: '. $this->CounterSuccess .'; MESSAGES-REJECTED: '. $this->CounterReject .'; CONNECTION-TIME: '. $this->Benchmark($this->EpochConnectionOpened));
    fclose($this->SMTPsocket);
    $this->SMTPsocket = null;
    $this->EpochConnectionOpened = null;
  }


  /**
   * Sends SMTP commands.
   * @param  string  $commandstring
   * @param  mixed   $expect
   * @return boolean
   */
  private function sendCommand($commandstring, $expect) {
    $this->debug('>>> '. $commandstring);
    fwrite($this->SMTPsocket, $commandstring . self::CRLF);
    $this->last_reply = $this->get_lines();
    $this->debug('<<< '. $this->last_reply);
    $code = substr($this->last_reply, 0, 3);
    return !in_array($code, (array)$expect) ? false : true;
  }


   /**
    * Fetches data from socket connection.
    * @return string
    */
  private function get_lines() {
    # If the connection is bad, give up straight away
    if (!is_resource($this->SMTPsocket)) {
      return '';
    }
    $data    = '';
    $endtime = 0;
    stream_set_timeout($this->SMTPsocket, $this->SMTPtimeout);
    if ($this->SMTPtimeLimit > 0) {
      $endtime = time() + $this->SMTPtimeLimit;
    }
    while (is_resource($this->SMTPsocket) && !feof($this->SMTPsocket)) {
      $str = @fgets($this->SMTPsocket, 515);
      $data .= $str;
      # If 4th character is a space, we are done reading, break the loop.
      if (isset($str[3]) && $str[3] == ' ') {
        break;
      }
      # Timed-out?
      $info = stream_get_meta_data($this->SMTPsocket);
      if ($info['timed_out']) {
        break;
      }
      # Now check if reads took too long
      if ($endtime && time() > $endtime) {
        break;
      }
    }
    return $data;
  }


  /**
   * Is connection open?
   * @return boolean
   */
  private function isConnectionOpen() {
    if (empty($this->SMTPsocket)) {
      return false;
    }
    if (is_resource($this->SMTPsocket)) {
      $status = stream_get_meta_data($this->SMTPsocket);
      if ($status['eof']) {
        $this->SMTPconnectionClose();
        return false;
      }
      return true;
    }
    return false;
  }


  /**
   * Sanitises header.
   * @param  string  $str
   * @return string
   */
  private function sanitiseHeader($str) {
    if (empty($str)) {
      return '';
    }
    return preg_replace("/(?:\n|\r|\t|%0A|%0D|%08|%09)+/i", '', $str);
  }


  /**
   * Composes the whole string of mail message.
   * @throws \Exception
   */
  private function composeMessage() {

    $this->mimeHeaders = array();
    $this->mimeBody    = '';

    $this->RBstring = null;
    $this->setEncoding();

    if ($this->mailMethod != 'mail') {
      if (!empty($this->SanitisedTo)) {
        $this->mimeHeaders[] = $this->encodeNameHeader('To', $this->SanitisedTo);
      }
      else {
        $this->mimeHeaders[] = 'To: undisclosed-recipients:;';
      }
      $this->mimeHeaders[] = $this->encodeHeader('Subject', $this->SanitisedSubject);
    }

    if (!empty($this->SanitisedCc)) {
      $this->mimeHeaders[] = $this->encodeNameHeader('Cc', $this->SanitisedCc);
    }

    if (!empty($this->SanitisedBcc)) {
      $this->mimeHeaders[] = $this->encodeNameHeader('Bcc', $this->SanitisedBcc);
    }

    if (!empty($this->SanitisedFrom)) {
      $this->mimeHeaders[] = $this->encodeNameHeader('From', $this->SanitisedFrom);
    }

    if (!empty($this->SanitisedSender)) {
      $this->mimeHeaders[] = $this->encodeNameHeader('Sender', $this->SanitisedSender);
    }

    if (!empty($this->SanitisedReplyToHeader)) {
      $this->mimeHeaders[] = $this->encodeNameHeader('Reply-To', $this->SanitisedReplyToHeader);
    }

    $this->mimeHeaders[] = $this->getHeaderDate();
    $this->mimeHeaders[] = $this->getHeaderMessageId();

    if (!empty($this->customHeaders) && is_array($this->customHeaders)) {
      foreach ($this->customHeaders as $key => $val) {
        if ($this->isReservedHeader($key)) {
          throw new Exception('The header '. $key .' cannot be defined in array customHeaders');
        }
        $key = $this->sanitiseHeader($key);
        $val = $this->sanitiseHeader($val);
        $this->mimeHeaders[] = $this->encodeHeader($key, $val);
      }
    }

    if ($this->Xmailer === true) {
      $this->mimeHeaders[] = $this->foldLine('X-Mailer: Version/'. self::VERSION .' (https://github.com/peterkahl/Universal-PHP-Mailer)');
    }
    elseif (is_string($this->Xmailer) && strlen($this->Xmailer) > 0) {
      $this->mimeHeaders[] = $this->foldLine('X-Mailer: '. $this->sanitiseHeader($this->Xmailer));
    }

    $this->mimeHeaders[] = 'MIME-Version: 1.0';

    $multiTypes = array();
    $i = -1;


    if (!empty($this->attachment) && count($this->attachment) > 1 && empty($this->textPlain) && empty($this->textHtml)) {
      # Multiple attachment and nothing else
      $i++;
      $multiTypes[$i] = 'multipart/mixed';
    }
    elseif (!empty($this->attachment) && (!empty($this->textPlain) || !empty($this->textHtml))) {
      $i++;
      $multiTypes[$i] = 'multipart/mixed';
      if (!empty($this->textPlain)) {
        $i++;
        $multiTypes[$i] = 'multipart/alternative';
        if (!empty($this->textHtml)) {
          $i++;
          $multiTypes[$i] = 'multipart/related';
        }
      }
      elseif (!empty($this->textHtml)) {
        if (!empty($this->inlineImage)) {
          $i++;
          $multiTypes[$i] = 'multipart/related';
        }
        else {
          $i++;
          $multiTypes[$i] = 'multipart/alternative';
        }
      }
    }
    else {
      # No attachment
      if (!empty($this->textPlain) && !empty($this->textHtml)) {
        $i++;
        $multiTypes[$i] = 'multipart/alternative';
        $i++;
        $multiTypes[$i] = 'multipart/related';
      }
      elseif (!empty($this->textHtml) && !empty($this->inlineImage)) {
        $i++;
        $multiTypes[$i] = 'multipart/related';
      }
    }


    if ($i > -1) { # Multipart
      $go  = true;
      $k   = 0;
      $dir = 'up';
      while ($go) {
        if ($k > $i) {
          $k--;
          $dir = 'down';
        }
        if ($dir == 'up') {
          # counting up
          if ($k == 0) {
            # First multipart section announcement
            $this->mimeHeaders[] = 'Content-Type: '. $multiTypes[$k] .';';
            $this->mimeHeaders[] = "\tboundary=\"". $this->getBoundary($multiTypes[$k]) .'"';
            # boundary START
            if (!empty($this->textPlain) || !empty($this->textHtml)) {
              $this->mimeBody .= '--'. $this->getBoundary($multiTypes[$k]) . self::CRLF;
            }
          }
          else {
            # 2nd, 3rd ... announcement
            $this->mimeBody .= 'Content-Type: '. $multiTypes[$k] .';'. self::CRLF;
            $this->mimeBody .= "\tboundary=\"". $this->getBoundary($multiTypes[$k]) .'"'. self::CRLF;
            $this->mimeBody .= self::CRLF;
            # boundary START
            $this->mimeBody .= '--'. $this->getBoundary($multiTypes[$k]) . self::CRLF;
          }

          if ($multiTypes[$k] == 'multipart/alternative') {
            if (!empty($this->textContentLanguage)) {
              $this->mimeBody .= 'Content-Language: '. $this->textContentLanguage . self::CRLF;
            }
            $this->mimeBody .= 'Content-Type: text/plain; charset='. self::CHARSET . self::CRLF;
            $this->mimeBody .= 'Content-Transfer-Encoding: '. $this->textEncoding . self::CRLF;
            $this->mimeBody .= self::CRLF;
            $this->mimeBody .= $this->encodeBody(trim($this->textPlain)) . self::CRLF;
            # boundary
            $this->mimeBody .= '--'. $this->getBoundary($multiTypes[$k]) . self::CRLF;
          }
          elseif ($multiTypes[$k] == 'multipart/related') {
            if (!empty($this->textContentLanguage)) {
              $this->mimeBody .= 'Content-Language: '. $this->textContentLanguage . self::CRLF;
            }
            $this->mimeBody .= 'Content-Type: text/html; charset='. self::CHARSET . self::CRLF;
            $this->mimeBody .= 'Content-Transfer-Encoding: '. $this->textEncoding . self::CRLF;
            $this->mimeBody .= self::CRLF;
            $this->mimeBody .= $this->encodeBody(trim($this->textHtml)) . self::CRLF;
            if (!empty($this->inlineImage)) {
              $this->mimeBody .= $this->generateInlineImageParts($multiTypes[$k]);
            }
          }
          $k++;
        }
        else {
          # down
          if ($multiTypes[$k] == 'multipart/mixed') {
            $this->mimeBody .= $this->generateAttachmentParts($multiTypes[$k]);
          }

          # boundary END
          $this->mimeBody .= '--'. $this->getBoundary($multiTypes[$k]) .'--'. self::CRLF . self::CRLF;

          $k--;
        }
        if ($k == -1) {
          $go = false;
        }
      }
      $this->mimeBody = rtrim($this->mimeBody) . self::CRLF;
    }
    else {
      # Not multipart
      if (!empty($this->textPlain)) {
        if (!empty($this->textContentLanguage)) {
          $this->mimeHeaders[] = 'Content-Language: '. $this->textContentLanguage;
        }
        $this->mimeHeaders[] = 'Content-Type: text/plain; charset='. self::CHARSET;
        $this->mimeHeaders[] = 'Content-Transfer-Encoding: '. $this->textEncoding;
        $this->mimeBody      = $this->encodeBody(trim($this->textPlain));
      }
      elseif (!empty($this->textHtml)) {
        if (!empty($this->textContentLanguage)) {
          $this->mimeHeaders[] = 'Content-Language: '. $this->textContentLanguage;
        }
        $this->mimeHeaders[] = 'Content-Type: text/html; charset='. self::CHARSET;
        $this->mimeHeaders[] = 'Content-Transfer-Encoding: '. $this->textEncoding;
        $this->mimeBody      = $this->encodeBody(trim($this->textHtml));
      }
      elseif (!empty($this->attachment)) { # Only 1 attachment
        foreach ($this->attachment as $atk => $atv) {
          $this->mimeHeaders[] = 'Content-Type: '. MIMEtypes::getType($atv['file-extension']) .';';
          $this->mimeHeaders[] = "\tname=\"". $atv['original-filename'] .'"';
          $this->mimeHeaders[] = 'Content-Transfer-Encoding: base64';
          $this->mimeHeaders[] = 'Content-Disposition: attachment;';
          $this->mimeHeaders[] = "\tfilename=\"". $atv['original-filename'] .'";';
          $this->mimeHeaders[] = "\tsize=". $atv['size'];
          $this->mimeBody      = chunk_split($atv['base64-data'], self::WRAP_LEN, self::CRLF);
          break;
        }
      }
      else {
        throw new Exception('There\'s no content');
      }
    }
  }


  /**
   * Certain headers are not allowed in the custom header array.
   * @param  string
   * @return boolean
   */
  private function isReservedHeader($str) {
    return in_array(strtolower($str), $this->ReservedHeaders);
  }


  /**
   * Generates string containing all MIME inline image parts from
   * the private array inlineImage.
   * @param  string
   * @return string
   */
  private function generateInlineImageParts($boundaryKey) {
    $str = '';
    foreach ($this->inlineImage as $key => $val) {
      $str .= '--'. $this->getBoundary($boundaryKey) . self::CRLF;
      $str .= 'Content-ID: <'. $val['content-id'] .'>'. self::CRLF;
      $str .= 'Content-Type: '. MIMEtypes::getType($val['file-extension']) .'; name="'. $val['original-filename'] .'"'. self::CRLF;
      $str .= 'Content-Transfer-Encoding: base64'. self::CRLF;
      $str .= 'Content-Disposition: inline; filename="'. $val['original-filename'] .'"'. self::CRLF;
      $str .= self::CRLF;
      $str .= chunk_split($val['base64-data'], self::WRAP_LEN, self::CRLF) . self::CRLF;
    }
    return $str;
  }


  /**
   * Add an image to the private array inlineImage
   * @param  string
   * @return string ... The cid, which you need in your HTML markup.
   * @throws \Exception
   */
  public function addInlineImage($filename) {
    if (!file_exists($filename)) {
      throw new Exception('Could not read file "'. $filename .'"');
    }

    $hash = substr(strtoupper(sha1($filename . microtime(true))), 0, 10);
    $extension = $this->fileExtension($filename);
    $cid = $hash.'@'. $this->hostName;

    $this->inlineImage[$this->inlineImageKey]['original-filename'] = $hash .'.'. $extension;
    $this->inlineImage[$this->inlineImageKey]['file-extension']    = $extension;
    $this->inlineImage[$this->inlineImageKey]['base64-data']       = base64_encode($this->FileGetContents($filename));
    $this->inlineImage[$this->inlineImageKey]['content-id']        = $cid;

    $this->inlineImageKey++;
    return $cid;
  }


  /**
   * Unsets (clears) existing inline images.
   * This may be needed only when sending bulk mails in a loop and
   * only when this class is instantiated before the bulk loop.
   *
   */
  public function unsetInlineImages() {
    $this->inlineImage    = array();
    $this->inlineImageKey = 0;
  }


  /**
   * Generates string containing all MIME attachment parts from
   * the private array attachment.
   * @param  string
   * @return string
   */
  private function generateAttachmentParts($boundaryKey) {
    $str = '';
    foreach ($this->attachment as $key => $val) {
      $str .= '--'. $this->getBoundary($boundaryKey) . self::CRLF;
      $str .= 'Content-Type: '. MIMEtypes::getType($val['file-extension']) .';'. self::CRLF;
      $str .= "\tname=\"". $val['original-filename'] .'"'. self::CRLF;
      $str .= 'Content-Transfer-Encoding: base64'. self::CRLF;
      $str .= 'Content-Disposition: attachment;'. self::CRLF;
      $str .= "\tfilename=\"". $val['original-filename'] .'";'. self::CRLF;
      $str .= "\tsize=". $val['size'] . self::CRLF;
      $str .= self::CRLF;
      $str .= chunk_split($val['base64-data'], self::WRAP_LEN, self::CRLF) . self::CRLF;
    }
    return $str;
  }


  /**
   * Adds attachment to the private array attachment.
   * @param  string
   * @return string
   * @throws \Exception
   */
  public function addAttachment($filename) {
    if (!file_exists($filename)) {
      throw new Exception('Could not read/find file "'. $filename .'"');
    }

    $this->attachment[$this->attachmentKey]['original-filename'] = rawurlencode($this->endExplode('/', $filename));
    $this->attachment[$this->attachmentKey]['file-extension']    = $this->fileExtension($filename);
    $this->attachment[$this->attachmentKey]['base64-data']       = base64_encode($this->FileGetContents($filename));
    $this->attachment[$this->attachmentKey]['size']              = filesize($filename);

    $this->attachmentKey++;
  }


  /**
   * Unsets (clears) existing attachments.
   * This may be needed only when sending bulk mails in a loop and
   * only when this class is instantiated before the bulk loop.
   *
   */
  public function unsetAttachments() {
    $this->attachment    = array();
    $this->attachmentKey = 0;
  }


  /**
   * Returns MIME boundary corresponding to a given key.
   * @param  string
   * @return string
   */
  private function getBoundary($key) {
    if (empty($this->boundary[$key])) {
      $this->boundary[$key] = '__'. strtoupper(substr(sha1($key . microtime(true)), 0, 8)) .':'. strtoupper(sha1($this->getMessageId())) .'__';
    }
    return $this->boundary[$key];
  }


  /**
   * Unsets (clears) existing boundary strings.
   *
   */
  private function unsetBoundaries() {
    $this->RBstring = null;
    $this->boundary = array();
  }


  /**
   * Returns Message ID header
   * @return string
   * @throws \Exception
   */
  private function getHeaderMessageId() {
    return 'Message-ID: <'. $this->getMessageId() .'>';
  }


  /**
   * Returns Message ID of this format:
   *    'TIME.RANDSTRING@hostname'
   * @return string
   * @throws \Exception
   */
  private function getMessageId() {
    if (empty($this->hostName)) {
      throw new Exception('Undefined property hostName');
    }
    if (empty($this->messageId)) {
      $tim = str_replace('.', '', microtime(true));
      $tim = strtoupper(base_convert(dechex($tim), 16, 36));
      $this->messageId = $tim .'.'. $this->ranStr() .'@'. $this->hostName;
    }
    return $this->messageId;
  }


  /**
   * Returns a randomly generated string of base-36 characters.
   * @return string
   */
  private function ranStr($len = 8) {
    $bytes = $len;
    if (function_exists('random_bytes')) {
      $str = bin2hex(random_bytes($bytes));
    }
    elseif (function_exists('mcrypt_create_iv')) {
      $str = bin2hex(mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM));
    }
    elseif (function_exists('openssl_random_pseudo_bytes')) {
      $str = bin2hex(openssl_random_pseudo_bytes($bytes));
    }
    else {
      $str = substr(sha1(mt_rand() . $this->toEmail), 16);
    }
    return substr(strtoupper(base_convert($str, 16, 36)), -$len);
  }


  /**
   * Returns the Date header as per RFC5322.
   * @throws \Exception
   * @return string
   */
  private function getHeaderDate() {
    if (empty($this->DateHeaderStr)) {
      if (!empty($this->DateHeaderZone)) {
        $this->ValidateTimeZoneString($this->DateHeaderZone);
        $DateObj = new DateTime('now', new DateTimeZone('Etc/GMT'));
        $DateObj->setTimeZone(new DateTimeZone($this->DateHeaderZone));
        $suffix = !empty($DateObj->format('T')) ? ' ('. $DateObj->format('T') .')' : '';
        return 'Date: '. $DateObj->format('D, j M Y H:i:s O') . $suffix;
      }
      return 'Date: '. gmdate('D, j M Y H:i:s O (T)');
    }
    return 'Date: '. $this->sanitiseHeader($this->DateHeaderStr);
  }


  /**
   * Validates timezone string.
   * @param  string  $str ...... Timezone string, ex. 'Europe/London', 'Asia/Hong_Kong'
   * @throws \Exception
   */
  private function ValidateTimeZoneString($str) {
    if (!preg_match('/^[A-Z]{3}|[A-Z][a-z_\/]{2,20}$/', $str)) {
      throw new Exception('Illegal timezone string format');
    }
  }


  /**
   * Formats display name in headers per RFC5322.
   * NOTE: Use this method if you want to avoid some unpleasant surprises.
   * @param  string  $name ...... unquoted and unescaped display name
   * @param  string  $comment ... without braces, ASCII only; optional
   * @return string
   */
  public function formatDisplayName($name, $comment = '') {
    if (preg_match('~[,;:\(\)\[\]\.\\<>@"]~', $name)) {
      $name = preg_replace('/"/', '\"', $name);
      if (!empty($comment)) {
        return '"'. $name .'" ('. $comment .')';
      }
      return '"'. $name .'"';
    }

    if (!empty($comment)) {
      return '"'. $name .'" ('. $comment .')';
    }
    return $name;
  }


  /**
   * Encodes name header
   * @param  string  $name ... header name
   * @param  array   $arr .... array(email => displayname)
   * @return string
   */
  private function encodeNameHeader($hdr, $arr) {
    $new = array();
    foreach ($arr as $email => $name) {
      if (!empty($name)) {
        $new[] = $this->encodeString($name) .' <'. $email .'>';
      }
      else {
        $new[] = $email;
      }
    }
    return $this->foldLine($hdr.': '.implode(', ', $new));
  }


  /**
   * Encodes header
   * @param  string  $hdr .... header name
   * @param  string  $str .... value of the header
   * @param  boolean $fold ... enable folding of long lines
   * @return string
   */
  private function encodeHeader($hdr, $str, $fold = true) {
    if ($fold) {
      return $this->foldLine($hdr .': '. $this->encodeString($str));
    }
    return $hdr .': '. $this->encodeString($str);
  }


  /**
   * Takes a string and encodes only those substrings that are non-ASCII.
   * @param  string
   * @return string
   */
  private function encodeString($str) {
    $str = preg_replace('/\s+/', ' ', $str);
    if ($this->isMultibyteString($str)) {
      $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY); # array
      $new = array();
      $kn = 0;
      foreach ($chars as $kc => $ch) {
        if ($kc == 0) {
          # First character
          $mb = $this->isMultibyteString($ch);
          $new[$kn] = $ch;
        }
        else {
          # Subsequent character (2,3,...)
          $mbPrev = $mb;
          # If preceeding character is multibyte, whitespace should
          # also be grouped and encoded with preceeding character.
          if ($ch != ' ') {
            $mb = $this->isMultibyteString($ch);
          }
          if ($mbPrev == $mb) {
            $new[$kn] .= $ch; # Same type as previous
          }
          else {
            $kn++; # Type changed
            $new[$kn] = $ch;
          }
        }
      }

      $str = '';
      foreach ($new as $segm) {
        if ($this->isMultibyteString($segm)) {
          $arr = $this->break2segments($segm);
          foreach ($arr as $ak => $av) {
            $arr[$ak] = $this->encodeRFC2047($av);
          }
          $str .= implode(' ', $arr);
        }
        else {
          $str .= $segm;
        }
      }
    }
    return $str;
  }


  /**
   * Break string of multibyte characters into shorter segments
   * @param  string
   * @return string
   */
  private function break2segments($str) {
    $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY); # array
    $new = array();
    $i = 0;
    foreach ($chars as $ch) {
      if (empty($new[$i])) {
        $new[$i] = $ch;
      }
      else {
        $new[$i] .= $ch;
      }
      # Check length
      if (mb_strlen($new[$i]) == self::MB_LEN_MAX) {
        $i++;
      }
    }
    return $new;
  }


  /**
   * MIME Encode Non-ASCII Text
   * https://tools.ietf.org/html/rfc2047
   * @param  string
   * @return string
   */
  private function encodeRFC2047($str) {
    return '=?'. self::CHARSET .'?B?'. base64_encode($str) .'?=';
  }


  /**
   * Tells whether string contains a multibyte character.
   * @param  string
   * @return boolean
   */
  private function isMultibyteString($str) {
    return iconv_strlen($str, 'utf-8') < strlen($str);
  }


  /**
   * Folding of excessively long header lines, RFC5322.
   * https://tools.ietf.org/html/rfc5322.html#section-3.2.2
   * IMPORTANT:
   *     Headers with FWS (folding white space) may cause
   *     DKIM validation failures. In such case, you may
   *     need to set DKIM Canonicalization to 'relaxed'.
   * @param  string
   * @return string
   * @throws \Exception
   */
  private function foldLine($str) {
    if (strlen($str) <= self::WRAP_LEN) {
      return $str;
    }
    if ($this->isHeaderTooLong($str)) {
      throw new Exception('Line length exceeds RFC5322 limit of '. self::LINE_LEN_MAX);
    }
    $arr = explode(self::CRLF, wordwrap($str, self::WRAP_LEN - 1, self::CRLF));
    $new = array();
    foreach ($arr as $av) {
      if (strlen($av) > self::WRAP_LEN - 1 && strpos($av, '?=') !== false) {
        $tmp = explode('?=', $av);
        $tmp = array_filter($tmp);
        foreach ($tmp as $tv) {
          $new[] = $tv .'?=';
        }
      }
      else {
        $new[] = $av;
      }
    }
    return implode(self::CRLF .' ', $new);
  }


  /**
   * Check for total length limit per RFC5322.
   * https://tools.ietf.org/html/rfc5322.html#section-3.2.2
   * @param  string
   * @return boolean
   */
  private function isHeaderTooLong($str) {
    if (strlen($str) > self::LINE_LEN_MAX) {
      return true;
    }
    return false;
  }


  /**
   * Sets a valid value of the property textEncoding.
   */
  private function setEncoding() {
    if (!empty($this->textEncoding) && in_array($this->textEncoding, array('quoted-printable', 'base64'))) {
      return;
    }
    $this->textEncoding = 'quoted-printable';
  }


  /**
   * Encodes body string.
   * https://tools.ietf.org/html/rfc5322.html#section-3.2.2
   * @param  string
   * @return string
   */
  private function encodeBody($str) {
    if ($this->textEncoding == 'quoted-printable') {
      return $this->qpEncode($str) . self::CRLF;
    }
    return chunk_split(base64_encode($str), self::WRAP_LEN, self::CRLF);
  }


  /**
   * quoted-printable encoding is being dot-stuffed only for 'smtp'
   * @param  string
   * @return string
   */
  private function qpEncode($str) {
    $str = quoted_printable_encode($str);
    if ($this->mailMethod == 'mail') {
      return $str;
    }
    return preg_replace('/'. self::CRLF .'\./', self::CRLF .'..', $str);
  }


  /**
   * Returns last element of an array.
   * @param  string
   * @param  array
   * @return string
   */
  private function endExplode($glue, $str) {
    if (strpos($str, $glue) === false) {
      return $str;
    }
    $str = explode($glue, $str);
    $str = end($str);
    return $str;
  }


  /**
   * Parses filename to return extension.
   * @param  string
   * @return string
   * @throws \Exception
   */
  private function fileExtension($str) {
    if (strpos($str, '.') === false) {
      throw new Exception('File name has no extension');
    }
    $str = strrchr($str, '.');
    $str = substr($str, 1);
    $str = strtolower($str);
    return $str;
  }


  /**
   * Writes to debug log or screen.
   * @param  string
   * @throws \Exception
   */
  private function debug($str) {
    if ($this->debugEnable) {
      if ($this->debugMethod == 'echo') {
        echo htmlentities(trim($str)) . PHP_EOL;
      }
      elseif ($this->debugMethod == 'log') {
        $this->appendLog($str);
      }
      else {
        throw new Exception('Illegal value debugMethod');
      }
    }
  }


  /**
   * Generates a line with timestamp and appends it to the debug log.
   * Timestamp has microsend resolution.
   * @param  string
   */
  private function appendLog($str) {
    $str = trim($str);
    if (strlen($str) > 1000) {
      $str = substr($str, 0, 1000) .' ... [truncated]';
    }
    list($sec, $usec) = explode('.', number_format(microtime(true), 3, '.', ''));
    $this->FileAppendContents($this->CacheFile, '['. gmdate("Y-m-d H:i:s", $sec + $this->serverTZoffset) .'.'. $usec .'] '. $str . PHP_EOL);
  }


  /**
   * File Append Contents
   * @param  string .... file
   * @return mixed (but usually integer)
   * @throws \Exception
   */
  private function FileAppendContents($file, $str) {
    $bytes = 0;
    $a = 0;
    while (!file_exists($file) || !is_readable($file)) {
      $a++;
      if ($a > 1000000) {
        throw new Exception('Kept trying for 1 sec and still couldn\'t access file');
      }
      usleep(1);
    }

    $fileObj = new SplFileObject($file, 'a');
    $k = 0;
    while (!$fileObj->flock(LOCK_EX)) {
      $k++;
      if ($k > 1000000) {
        throw new Exception('Taking >1 sec to lock this file');
      }
      usleep(1);
    }
    $bytes = $fileObj->fwrite($str);
    $fileObj->flock(LOCK_UN);
    return $bytes;
  }


  /**
   * File Get Contents
   * @param  string .... file
   * @return string
   * @throws \Exception
   */
  private function FileGetContents($file) {
    $fileObj = new SplFileObject($file, 'r');
    $k = 0;
    while (!$fileObj->flock(LOCK_EX)) {
      $k++;
      if ($k > 1000000) {
        throw new Exception('Taking >1 sec to lock this file');
      }
      usleep(1);
    }
    $str = $fileObj->fread($fileObj->getSize());
    $fileObj->flock(LOCK_UN);
    return $str;
  }


  /**
   * Human-readable representation of a precisely quantified
   * time interval between 2 time points, one being right now.
   * @param  integer .... timestamp in the past
   * @return string
   */
  private function Benchmark($st) {
    $val = microtime(true) - $st;
    if ($val >= 1) {
      return number_format($val, 2, '.', ',') .' sec';
    }
    $val = $val * 1000;
    if ($val >= 1) {
      return number_format($val, 2, '.', ',') .' msec';
    }
    $val = $val * 1000;
    return number_format($val, 2, '.', ',') .' μsec';
  }

}
