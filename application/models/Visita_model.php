<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Visita_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config('visita', "Visitas por paciente.");
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
                        
        if ( isset($filtros['idPaciente']) )
            $condicion[] = "idPaciente = '{$filtros['idPaciente']}'";

        if ( isset($filtros['idVisita']) )
            $condicion[] = "idVisita = '{$filtros['idVisita']}'";

        if ( isset($filtros['idEstudioClues']) )
            $condicion[] = "idEstudioClues = '{$filtros['idEstudioClues']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";
            
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
                
        return $rules;
    }
}
