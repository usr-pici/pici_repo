<?php

/**
 * Description of MY_Form_Validation
 *
 * @author Felipe Avila
 */

class MY_Form_validation extends CI_Form_validation {
    
    function  __construct() {
        
        parent::__construct();        
    }
    
    function varios_vacios() {
        
        return FALSE;
    }
    
    function clear_rules() {
        
        $this->_field_data = $this->_error_array = array();
    }
    
    function get_all_errors() {

        return $this->_error_array;
    }
    
    function integer($val) {

        return preg_match('^[\d]+$', $val);
    }
    
    
}
