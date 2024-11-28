<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password_reset_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->title = "Tabla de Passwords Reset";
        $this->name_table = "password_reset";
        $this->key_field = 'idReset';
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";

        if ( isset($filtros['email']) )
            $condicion[] = "email IN ('" . $filtros['email']. "')";

        if ( isset($filtros['token']) )
			$condicion[] = "token IN ('" . $filtros['token']. "')";
			
        if ( isset($filtros['idUsuario']) )
           $condicion[] = "idUsuario = '{$filtros['idUsuario']}'";

        if ( isset($filtros['vigencia']) )
           $condicion[] = "vigencia <= '{$filtros['vigencia']}'";

        if ( isset($filtros['vigenciaToken']) )
           $condicion[] = "vigencia > '{$filtros['vigenciaToken']}'";

        if ( isset($filtros['activo']) )
           $condicion[] = "activo = '{$filtros['activo']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();        
                
        return $rules;
    }
}
