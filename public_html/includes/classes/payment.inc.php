<?php

  class payment extends module {
    public $options;
    public $data;

    public function __construct() {
      
      parent::set_type('payment');
      
    // Link data to session object
      if (!isset(session::$data['payment']) || !is_array(session::$data['payment'])) {
        session::$data['payment'] = array();
      }
      $this->data = &session::$data['payment'];
      
      if (empty($this->data['selected'])) {
        $this->data['selected'] = array();
      }
      
    // Load modules
      $this->load();
      
      if (!isset($this->data['userdata'])) {
        $this->data['userdata'] = array();
      }
      
    // Attach userdata to module
      if (!empty($this->data['selected'])) {
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
        if (!empty($this->modules[$module_id])) $this->modules[$module_id]->userdata = &$this->data['userdata'][$module_id];
      }
    }
    
    public function options($items=null, $subtotal=null, $tax=null, $currency_code=null, $customer=null) {
      global $shipping;
      
      if ($items === null) $items = cart::$data['items'];
      if ($subtotal === null) $subtotal = cart::$data['total']['value'];
      if ($tax === null) $tax = cart::$data['total']['tax'];
      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;
      
      $cart_checksum = sha1(serialize(cart::$data) . @serialize($shipping->data['selected']));
      
      //if (isset($this->data['order_checksum']) && $this->data['order_checksum'] == $cart_checksum) {
      //  return $this->data['options'];
      //}
      
      $this->data['options'] = array();
      $this->data['order_checksum'] = $cart_checksum;
      
      if (empty($this->modules)) return;
      
      foreach ($this->modules as $module) {
        
        $module_options = $module->options($items, $subtotal, $tax, $currency_code, $customer);
        
        if (!empty($module_options['options'])) {
          
          $this->data['options'][$module->id] = $module_options;
          $this->data['options'][$module->id]['id'] = $module->id;
          $this->data['options'][$module->id]['options'] = array();
          
          foreach ($module_options['options'] as $option) {
            $this->data['options'][$module->id]['options'][$option['id']] = $option;
          }
        }
      }
      
      return $this->data['options'];
    }
    
    public function select($module_id, $option_id, $userdata=null) {
      
      if (!isset($this->data['options'][$module_id]['options'][$option_id])) {
        $this->data['selected'] = array();
        notices::add('errors', language::translate('error_invalid_payment_option', 'Cannot set an invalid payment option.'));
        return;
      }
      
      if (!empty($userdata)) {
        $this->data['userdata'][$module_id] = $userdata;
      }
      
      if (method_exists($this->modules[$module_id], 'select')) {
        if ($error = $this->modules[$module_id]->select($option_id)) {
          notices::add('errors', $error);
        }
      }

      
      $this->data['selected'] = array(
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
        'confirm' => $this->data['options'][$module_id]['options'][$option_id]['confirm'],
      );
    }
    
    public function set_cheapest() {
      
      foreach ($this->data['options'] as $module) {
        foreach ($module['options'] as $option) {
          if (!isset($cheapest_amount) || $option['cost'] < $cheapest_amount) {
            $cheapest_amount = $option['cost'];
            $module_id = $module['id'];
            $option_id = $option['id'];
          }
        }
      }
      
      $this->select($module_id, $option_id);
    }
    
    public function transfer() {
      
      if (empty($this->data['selected'])) trigger_error('Error: No payment option selected', E_USER_ERROR);
      
      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      
      if (!method_exists($this->modules[$module_id], 'transfer')) return;
      
      return $this->modules[$module_id]->transfer();
    }
    
    public function run($method_name, $module_id='') {
    
      if (empty($module_id)) {
        if (empty($this->data['selected']['id'])) return;
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      }
      
      if (method_exists($this->modules[$module_id], $method_name)) {
        return $this->modules[$module_id]->$method_name();
      }
    }
  }

?>