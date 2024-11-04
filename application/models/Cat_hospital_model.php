<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_hospital_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->title = "Cat\xE1logo de Hospitales";
        $this->set_config('cat_hospital');
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
            
        if ( isset($filtros['idApp']) )
            $condicion[] = "idApp = '{$filtros['idApp']}'";

        if ( isset($filtros['idSucursal']) )
            $condicion[] = "idSucursal = '{$filtros['idSucursal']}'";

        if ( isset($filtros['clave']) )
            $condicion[] = "clave = '{$filtros['clave']}'";
            
        if ( isset($filtros['nombre']) )
            $condicion[] = "nombre = '{$filtros['nombre']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";

        if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array();

        $rules['idSucursal'] = array(
            'field'   => $name_reg.'[idFarmaceutica]', 
            'label'   => utf8_encode("Farmacéutica"), 
            'rules'   => 'required',
            'class'   => 'mb-2',
            'dependencia' => array(                
                'catalogo' => 'farmaceutica',
                'extras' => ['orderBy' => "nombre ASC", 'imprimirSQL' => 0],
                'filtros' => ['borrado' => 0, 'activo' => 1],
                'id' => 'idFarmaceutica',
                'desc' => 'nombre',
            )
        );

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
            'label'   => utf8_encode("Descripcion")
        );
        
        $rules['activo'] = array (
            'field' => $name_reg . '[activo]',
            'label ' => utf8_encode("Activo"),
            'rules ' => 'required',
            'config' => array (
                'type' => 'radio' ,
                'options' => array (
                     '0' => array('val' => '0' , 'desc' => utf8_encode("No") , 'default' => 1) ,
                     '1' => array('val' => '1' , 'desc' => utf8_encode("S\xED"))
				)
			)
		);
        
        return $rules;
    }
}
