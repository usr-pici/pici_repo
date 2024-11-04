<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Historico_estatus_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->title = "Cat\xE1logo de Historico de Estatus";        
        $this->name_table = "historico_estatus";
        $this->key_field = 'idHistorico';
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";

        if ( isset($filtros['id_NOT']) )
            $condicion[] = $this->key_field . " != '" . $filtros['id_NOT'] . "'";         

        if ( isset($filtros['idRegistro']) )
			$condicion[] = "idRegistro IN (" . $filtros['idRegistro'] . ")";
        
		if ( isset($filtros['idRegistro_IN']) )
			$condicion[] = "idRegistro IN (" . $filtros['idRegistro_IN'] . ")";
		
		if ( isset($filtros['idEstatus']) )
			$condicion[] = "idEstatus IN (" . $filtros['idEstatus'] . ")";
		
		if ( isset($filtros['idEstatus_IN']) )
			$condicion[] = "idEstatus IN (" . $filtros['idEstatus_IN'] . ")";
		
		if ( isset($filtros['idEstatus_NOT_IN']) )
			$condicion[] = "idEstatus NOT IN(" . $filtros['idEstatus_NOT_IN'] . ")";

        if ( isset($filtros['idUsuario']) )
			$condicion[] = "idUsuario IN ('" . $filtros['idUsuario'] . "')";
			
        if ( isset($filtros['fecha']) )
            $condicion[] = "fecha IN ('" . $filtros['fecha'] . "')";

        if ( isset($filtros['fechaInicio']) )
            $condicion[] = "fecha >= '" . $filtros['fechaInicio'] . "'";

        if ( isset($filtros['fechaFin']) )
            $condicion[] = "fecha <= '" . $filtros['fechaFin'] . "'";

		if ( isset($filtros['tabla']) )
            $condicion[] = "tabla = '" . $filtros['tabla'] . "'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();
        
        // $rules['clave'] = array(
        //     'field'   => $name_reg.'[clave]', 
        //     'label'   => utf8_encode("Clave"), 
        //     'rules'   => 'required'/*,
        //     'class'   => 'fecha'*/
        // );
        // $rules['nombre'] = array(
        //     'field'   => $name_reg.'[nombre]', 
        //     'label'   => utf8_encode("Nombre"), 
        //     'rules'   => 'required'
        // ); 
        
        return $rules;
    }
}
