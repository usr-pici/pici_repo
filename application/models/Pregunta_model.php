<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pregunta_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('pregunta', "Preguntas del formulario",['cveStatusAdd' => 'REGISTERED_QUESTION', 'cveStatusUpdate' => 'UPDATE_QUESTION']);
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

        if ( isset($filtros['idFormulario']) )
            $condicion[] = "idFormulario = '{$filtros['idFormulario']}'";

        if ( isset($filtros['consecutivo']) )
            $condicion[] = "consecutivo = '{$filtros['consecutivo']}'";
            
        if ( !empty($filtros['vigente']) )
            $condicion[] = "borrado = '0' AND activo = '1'";

        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
                
        return $rules;
    }
}
