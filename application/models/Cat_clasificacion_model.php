<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cat_clasificacion_model extends MY_Model {
    
    function  __construct() {

        parent::__construct();
        
        $this->title = "Tabla de atributo por producto ";
        $this->set_config('cat_clasificacion');
    }
    
    function buscar($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['id']) )
            $condicion[] = $this->key_field . " = '" . $filtros['id'] . "'";          

        if ( isset($filtros['id_IN']) )
            $condicion[] = $this->key_field . " IN (" . $filtros['id_IN'] . ")";
        
        if ( isset($filtros['id_NOT_IN']) )
            $condicion[] = $this->key_field . " NOT IN (" . $filtros['id_NOT_IN'] . ")";
            
        if ( isset($filtros['idClasificacionPadre']) )
            $condicion[] = "idClasificacionPadre = '" . $filtros['idClasificacionPadre'] . "'";
        
		if ( isset($filtros['idClasificacionPadre_IN']) )
            $condicion[] = "idClasificacionPadre IN (" . $filtros['idClasificacionPadre_IN'] . ")";
		        
		if ( isset($filtros['idProductoServicio_IN']) )
            $condicion[] = "idProductoServicio IN(" . $filtros['idProductoServicio_IN'] . ")";
        
		if ( isset($filtros['nombre_LIKE']) )
            $condicion[] = "nombre LIKE '%{$filtros['nombre_LIKE']}%'";
            
        if ( isset($filtros['nombre']) )
            $condicion[] = "nombre = '{$filtros['nombre']}'";

        if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";

        if ( isset($filtros['borrado']) )
            $condicion[] = "borrado = '{$filtros['borrado']}'";

		if ( isset($filtros['clave']) )
            $condicion[] = "clave = '{$filtros['clave']}'";

		if ( isset($filtros['idClasificacion']) )
            $condicion[] = "idClasificacion = '{$filtros['idClasificacion']}'";
        
        return parent::buscar($condicion, $extras);
    }
    
    function get_rules(&$reg = array(), $name_reg = 'reg') {
        
        $rules = array(); 
        
        return $rules;
    }
}
