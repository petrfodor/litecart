<?php
  
  class ref_manufacturer {
    
    private $_data = array();
    
    function __construct($manufacturer_id) {
    
      
      if (empty($manufacturer_id)) trigger_error('Missing manufacturer id');
      
      $this->_data['id'] = (int)$manufacturer_id;
    }
    
    public function __get($name) {
      
      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }
      
      $this->load($name);
      
      return $this->_data[$name];
    }
    
    public function __isset($name) {
      return $this->__get($name);
    }
    
    public function __set($name, $value) {
      trigger_error('Setting data is prohibited', E_USER_ERROR);
    }
    
    private function load($type='') {
    
      switch($type) {
      
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'meta_keywords':
          
          $this->_data['info'] = array();
          
          $query = database::query(
            "select language_code, description, short_description, head_title, meta_description, meta_keywords from ". DB_TABLE_MANUFACTURERS_INFO ."
            where manufacturer_id = '". (int)$this->_data['id'] ."'
            and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
          );
          
          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) $this->_data[$key][$row['language_code']] = $value;
          }
          
        // Fix missing translations
          foreach (array('description', 'short_description', 'head_title', 'meta_description', 'meta_keywords') as $key) {
            foreach (array_keys(language::$languages) as $language_code) {
              if (empty($this->_data[$key][$language_code])) $this->_data[$key][$language_code] = $this->_data[$key][settings::get('default_language_code')];
            }
          }
          
          break;
          
        default:
          
          if (isset($this->_data['date_added'])) return;
          
          $query = database::query(
            "select * from ". DB_TABLE_MANUFACTURERS ."
            where id = '". (int)$this->_data['id'] ."'
            limit 1;"
          );
          
          $row = database::fetch($query);
          
          if (database::num_rows($query) == 0) trigger_error('Invalid manufacturer id ('. $this->_data['id'] .')', E_USER_ERROR);
          
          foreach ($row as $key => $value) $this->_data[$key] = $value;
          
          break;
      }
    }
  }
  
?>