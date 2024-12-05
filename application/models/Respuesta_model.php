<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Respuesta_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('respuesta', "Respuestas del formulario",['cveStatusAdd' => 'REGISTERED_RESPONSE',  'cveStatusUpdate' => '']);
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

        if ( isset($filtros['idPregunta']) )
            $condicion[] = "idPregunta = '{$filtros['idPregunta']}'";

        if ( isset($filtros['idPaciente']) )
            $condicion[] = "idPaciente = '{$filtros['idPaciente']}'";

        if ( !empty($filtros['idVisita']) )
            $condicion[] = "idVisita = '{$filtros['idVisita']}'";
            
        if ( !empty($filtros['vigente']) )
            $condicion[] = "borrado = '0' AND activo = '1'";

        if ( isset($filtros['idVisitaNull']) )
            $condicion[] = "idVisita IS NULL";

        if ( isset($filtros['idVisitaIsNotNull']) )
            $condicion[] = "idVisita IS NOT NULL";

        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
                
        return $rules;
    }
}
