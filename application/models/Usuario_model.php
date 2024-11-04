<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usuario_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->title = "Usuarios del sistema";
        $this->set_config('usuario', "Usuarios del sistema");
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

		if ( isset($filtros['idPersona']) )
            $condicion[] = "idPersona = '{$filtros['idPersona']}'";

		if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";

        if ( isset($filtros['idUsuario']) )
            $condicion[] = "idUsuario = '{$filtros['idUsuario']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
                
        return $rules;
    }
}
