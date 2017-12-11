<?php

  class smtp {
    private $_socket = null;
    private $_host = null;
    private $_port = null;
    private $_logfh = '';

    function __construct($host, $port, $username='', $password='') {

      if ($port == 465) {
        $this->_host = "ssl://$host:$port";
      } else {
        $this->_host = "tcp://$host:$port";
      }

      $this->_username = $username;
      $this->_password = $password;
    }

    public function send($sender, $recipients, $data='') {

      $this->_logfh = fopen(FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'last_smtp.log', 'w');

      if (!is_resource($this->_socket)) $this->connect();

      $this->read(220)
           ->write("EHLO {$_SERVER['SERVER_NAME']}\r\n", 250);

      if (preg_match('#250-STARTTLS#', $this->_last_response)) {
        $this->write("STARTTLS\r\n", 220);
        if (!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT)) {
        //if (!stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
          throw new Exception('Could not start TLS encryption');
        }
        $this->write("EHLO {$_SERVER['SERVER_NAME']}\r\n", 250);
      }

      if (!empty($this->_username)) {

        $auths = array();
        if (preg_match('#250.AUTH ([^\R]+)#', $this->_last_response, $matches)) {
          $auths = explode(' ', $matches[1]);
        }

        switch(true) {
          case (in_array('CRAM-MD5', $auths)):
            $this->write("AUTH CRAM-MD5\r\n", 334)
                 ->write(base64_encode($this->_username .' '. hash_hmac('md5', $this->_last_response, $this->_password)) . "\r\n", 235);
          break;

          case (in_array('LOGIN', $auths)):
            $this->write("AUTH LOGIN\r\n", 334)
                 ->write(base64_encode($this->_username) . "\r\n", 334)
                 ->write(base64_encode($this->_password) . "\r\n", 235);
          break;

          case (in_array('PLAIN', $auths)):
            $this->write("AUTH PLAIN\r\n", 334)
                 ->write(base64_encode("\0" . $this->_username . "\0" . $this->_password) . "\r\n", 235);
            break;

          default:
            throw new Exception('No supported authentication methods ('. implode(', ', $auths).')');
        }
      }

      $this->write("MAIL FROM: <$sender>\r\n", 250);

      if (!is_array($recipients)) $recipients = array($recipients);
      foreach ($recipients as $recipient) {
        $this->write("RCPT TO: <$recipient>\r\n", 250);
      }

      $this->write("DATA\r\n", 354)
           ->write("$data\r\n")
           ->write(".\r\n", 250);

      return true;
    }

    public function connect() {

      $stream_context = $context = stream_context_create(array(
        'ssl' => array(
          // set some SSL/TLS specific options
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        ),
      ));

      $this->_log .= "Connecting to $this->_host ...\r\n";
      $this->_socket = stream_socket_client($this->_host, $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $stream_context);

      if ($errno) throw new Exception('Could not connect to socket '. $this->_host .': '. $errstr);
      if (empty($this->_socket)) throw new Exception('Failed opening socket connection to '. $this->_host);

      stream_set_blocking($this->_socket, true);
      stream_set_timeout($this->_socket, 6);

      return $this;
    }

    public function disconnect() {

      if (!is_resource($this->_socket)) return;

      $this->write("QUIT\r\n");

      fclose($this->_socket);
      fclose($this->_logfh);

      return $this;
    }

    public function read($expected_response=null) {

      $response = '';

      $buffer = '';
      while (substr($buffer, 3, 1) != ' ') {
        if (!$buffer = fgets($this->_socket, 256)) throw new Exception('No response from socket');
        fwrite($this->_logfh, "< $buffer");
        $response .= $buffer;
      }

      $this->_last_response = $response;

      if (substr($response, 0, 3) != $expected_response) throw new Exception('Unexpected socket response; '. $response);

      return $this;
    }

    public function write($data, $expected_response=null) {

      fwrite($this->_logfh, "> $data");
      $result = fwrite($this->_socket, $data);

      if ($expected_response !== null) {
        $this->read($expected_response);
      }

      return $this;
    }

    public function get_log() {
      return $this->_log;
    }
  }
