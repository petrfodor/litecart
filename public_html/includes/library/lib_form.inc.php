<?php
  
  class form {
    
    public static function construct() {
    }
    
    public static function load_dependencies() {
      
    // Check post token
      if (!empty($_POST) && (!defined('REQUIRE_POST_TOKEN') || REQUIRE_POST_TOKEN != false)) {
        if (!isset($_POST['token']) || $_POST['token'] != self::session_post_token()) {
          error_log('Warning: Blocked a potential CSRF hacking attempt by '. $_SERVER['REMOTE_ADDR'] .' ['. (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .'] requesting '. $_SERVER['REQUEST_URI'] .'.');
          session::reset();
          header('HTTP/1.1 400 Bad Request');
          die('HTTP POST Error: The form submit token was issued for another session identity.');
        }
      }
    }
    
    //public static function initiate() {
    //}
    
    public static function startup() {
      
    // Is there incoming ajax data that needs decoding?
      if (!empty($_POST) && strtolower(language::$selected['charset']) != 'utf-8') {
        $flag_unicoded = false;
        
        if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset=utf-8') !== false) $flag_unicoded = true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset') === false) $flag_unicoded = true;
        
        if ($flag_unicoded) {
          function utf8_decode_recursive($input) {
            $return = array();
            foreach ($input as $key => $val) {
              if (is_array($val)) $return[$key] = utf8_decode_recursive($val);
              else $return[$key] = utf8_decode($val);
            }
            return $return;          
          }
          $_POST = utf8_decode_recursive($_POST);
        }
      }
    }
    
    //public static function before_capture() {
    //}
    
    //public static function after_capture() {
    //}
    
    //public static function prepare_output() {
    //}
    
    //public static function before_output() {
    //}
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function session_post_token() {
      return sha1(SESSION_UNIQUE_ID .':'. session_id());
    }
  }
  
?>