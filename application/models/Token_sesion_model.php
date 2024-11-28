<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Token_sesion_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('token_sesion','Tabla de tokens de sesion del sistema');
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
           
        if ( isset($filtros['idUsuario']) )
            $condicion[] = "idUsuario IN (" . $filtros['idUsuario'] . ")";

        if ( isset($filtros['idApp']) )
            $condicion[] = "idApp = '{$filtros['idApp']}'";

        if ( isset($filtros['idUsuario']) )
            $condicion[] = "idUsuario = '{$filtros['idUsuario']}'";

        if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";
            
		if ( isset($filtros['notAppEmpty']) )
            $condicion[] = "idApp != ''";
                    
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
        
        return $rules;
    }
}
