<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_tipo_campo_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config("cat_tipo_campo", "Cat\xE1logo de campos");
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
            
        if ( isset($filtros['clave']) )
            $condicion[] = "clave = '{$filtros['clave']}'";
            
        if ( isset($filtros['nombre']) )
            $condicion[] = "nombre = '{$filtros['nombre']}'";

        if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
        
        return $rules;
    }
}
