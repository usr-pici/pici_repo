<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Persona_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('persona','Tabla de negocio de personas');
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
            
        if ( isset($filtros['nombre_LIKE']) )
            $condicion[] = "nombre LIKE '%{$filtros['nombre_LIKE']}%'";
            
        if ( isset($filtros['nombre']) )
            $condicion[] = "nombre = '{$filtros['nombre']}'";
        
        if ( isset($filtros['idPersona']) )
            $condicion[] = "idPersona = '{$filtros['idPersona']}'";

        if ( isset($filtros['idx']) )
            $condicion[] = "idx = '{$filtros['idx']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();  
                
        return $rules;
    }
}
