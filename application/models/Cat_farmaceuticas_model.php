<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_farmaceuticas_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->set_config("cat_farmaceuticas","Cat\xE1logo de Farmacéuticas");
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

        $rules['clave'] = array(
            'field'   => $name_reg.'[clave]', 
            'label'   => utf8_encode("Clave"), 
            'rules'   => 'required'
        );
        
        $rules['nombre'] = array(
            'field'   => $name_reg.'[nombre]', 
            'label'   => utf8_encode("Nombre"), 
            'rules'   => 'required'
        );
        
        $rules['descripcion'] = array(
            'field'   => $name_reg.'[descripcion]', 
            'label'   => utf8_encode("Descripci\xF3n"), 
            'rules'   => 'required'
        );
        
        $rules['activo'] = array (
            'field' => $name_reg . '[activo]',
            'label ' => utf8_encode("Activo"),
            'rules ' => 'required',
            'config' => array (
                'type' => 'radio' ,
                'options' => array (
                     '0' => array('val' => '0' , 'desc' => utf8_encode("No")),
                     '1' => array('val' => '1' , 'desc' => utf8_encode("S\xED"), 'default' => 1)
				)
			)
		);
        
        return $rules;
    }
}
