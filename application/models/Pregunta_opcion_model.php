<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pregunta_opcion_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('pregunta_opcion', "Opciones de pregunta");
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
                        
        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";
            
        if ( !empty($filtros['vigente']) )
            $condicion[] = "borrado = '0' AND activo = '1'";

        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
                
        return $rules;
    }
}
