<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

/**
 * Esta clase permite la administración genérica de los catálogos del sistema. *
 * @author Felipe Avila 
 */
class Catalogo_service extends Class_Service {
    
    private $modelos;
    
    public function __construct() {
          
        parent::__construct();
        
        //Catálogos para la configuración de la casilla
        $this->modelos = array(            
            'farmaceutica' => 'cat_farmaceuticas_model',
            'clues' => 'cat_clues_model',
            'estatus' => 'cat_estatus_model',
            'tipoCampo' => 'cat_tipo_campo_model',
            'rol' => 'cat_rol_model',
        );

        //Cargar los Modelos del Array        
        foreach ($this->modelos as $modelo) {
            
            $this->CI->load->model($modelo);
        }
    }

    //function search($condicion = array(), $extras = array()) { return; }
    
    function get_regxclave($catalogo = '', $clave = '') {
        
        $modelo = $this->get_modelo($catalogo);        
        if ( !$modelo || empty($clave) ) {
            
            return FALSE;
        }
        
        return current( $modelo->buscar(array('clave' => $clave)) ); 
    }
    
    function getMonth($cve = 0, $returnList = FALSE) {
        
        $opciones = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
//        $opciones = [
//            'CH' => ['clave' => 'CH', 'nombre' => 'Caja chica', 'largo' => 10, 'ancho' => 20, 'alto' => 30, 'costo' => 20],
//        ];
         
        return $returnList ? $opciones : ( empty($opciones[$cve]) ? FALSE : $opciones[$cve] );
    }

    function getEmpaques() {
        
        $tipoEmpaque = [            
            'SB' => ['id' => 5, 'nombre' => 'Sobre', 'largo' => 17.5, 'ancho' => 7, 'alto' => 23, 'peso' => 0.5, 'precio' => 15, 'volumen' => 2817.5],
            'CH' => ['id' => 1, 'nombre' => 'Chica', 'largo' => 25, 'ancho' => 16, 'alto' => 16, 'peso' => 1, 'precio' => 20, 'volumen' => 6400],
            'M' => ['id' => 2, 'nombre' => 'Mediana', 'largo' => 14, 'ancho' => 14, 'alto' => 28, 'peso' => 2, 'precio' => 25, 'volumen' => 5488],
            'G' => ['id' => 3, 'nombre' => 'Grande', 'largo' => 28, 'ancho' => 18, 'alto' => 21, 'peso' => 4, 'precio' => 30, 'volumen' => 10584],
            'EG' => ['id' => 4, 'nombre' => 'Extra Grande', 'largo' => 34, 'ancho' => 25, 'alto' => 27, 'peso' => 6, 'precio' => 35, 'volumen' => 22950]            
        ];

        return $tipoEmpaque;
    }
    
    function getYear($cve = 0, $returnList = FALSE) {
        
        $opciones = [];
        for ( $i = 2018; $i <= 2050; $i++ ) {
            
            $opciones[$i] = $i;
        }
        
        return $returnList ? $opciones : ( empty($opciones[$cve]) ? FALSE : $opciones[$cve] );
    }
    
    function is_valid($catalogo = '') {
        
        return isset($this->modelos[$catalogo]);
    }
    
    function save_direct($catalogo, $reg, $id = 0, $cond = NULL) {
        
        $action = empty($id) ? 'add' : 'update';
        $model = $this->get_modelo($catalogo);
        
        return $this->action_on_reg($model, $reg, $action, $action === 'add' ? NULL : ( empty($cond) ? [$model->get_var('key_field') => $id] : $cond ));
    }
    
    function get_modelo($catalogo = '') {
        return isset($this->modelos[$catalogo]) ? $this->CI->{$this->modelos[$catalogo]} : FALSE;
    }
    
    function actionBatch($catalogo = '', $regs = [], $action = 'add') {
        //$this->CI->imprimir($catalogo . " desde get_model");
        $model = $this->get_modelo($catalogo);
        
        if ( empty($model) ) {
            
            $this->CI->msg_error('', 'PARAMETRO');            
        }
        
        return $action === 'add' ? $model->addBatch($regs) : $model->updateBatch($regs);
    }
    
    /*
     * Se obtienen los registros de las dependencias configuradas en el catálogo en turno.
     * @param string $catalogo
     * @return array $result
     */
    function get_config_to_view($catalogo = '') {
        
        $modelo = $this->get_modelo($catalogo);
        
        $result = $modelo ? @$modelo->get_config_to_view() : FALSE;
            
        foreach ($result['dependencia'] as $campo => &$dependencia) {

            if ( empty($dependencia['regs']) ) {
                
                if ( empty($dependencia['method_get']) ) {
    //                $this->CI->log("intento cargar el modelo aux: " . $dependencia['catalogo']);
                    $modelo_aux = $this->get_modelo($dependencia['catalogo']);
    //                $this->CI->log("se cargó el modelo aux: " . $dependencia['catalogo']);
                    $dependencia['regs'] = $modelo_aux->indexed_search(
                        $dependencia['id'],
                        array('activo' => 1, 'borrado' => 0) + ( empty($dependencia['filtros']) ? array() : $dependencia['filtros'] ),
                        empty($dependencia['extras']) ? array() : $dependencia['extras']
                    );
                    
                } else {
                    
                    $dependencia['regs'] = $this->{$dependencia['method_get']}();
                }
            }
            
            $dependencia['regs_to_select'] = $this->get_list_to_select(array(
                'index_id' => $dependencia['id'],
                'index_desc' => $dependencia['desc'],
                'regs' => $dependencia['regs']/*,
                'con_etiqueta' => isset($dependencia['con_etiqueta']) ? $dependencia['con_etiqueta'] : TRUE*/                
            ));
        }
        
        return $result;
    }
    
    function get_regs_with_dep($catalogo = '', &$regs = array()) {
        
        $config = $this->get_config_to_view($catalogo);
//        $this->CI->imprimir($config);
        
        foreach ($regs as &$reg) {
            
            foreach ($config['dependencia'] as $campo => $dependencia) {

                $reg[$campo] = empty($dependencia['regs'][$reg[$campo]][$dependencia['desc']]) ? '' : $dependencia['regs'][$reg[$campo]][$dependencia['desc']];
            }
            foreach ($config['config_field'] as $field => $config_field) {

                $reg[$field] = empty($config_field['options'][$reg[$field]]['desc']) ? '' : $config_field['options'][$reg[$field]]['desc'];
            }
        }
        
        return $regs;
    }
    
    function get_cat_dependiente($catalogo = '', $dependencia_param = '', $filtros = array()) {
        
        $modelo = $this->get_modelo($catalogo);
        
        $result = $modelo ? @$modelo->get_config_to_view() : FALSE;
//        $this->CI->imprimir($result);
        $regs_to_select = array();
            
        foreach ($result['dependencia'] as $campo => &$dependencia) {

            if ( $campo != $dependencia_param ) {
                
                continue;
            }
            
            $modelo_aux = $this->get_modelo($dependencia['catalogo']);
            
            $regs = $modelo_aux->indexed_search(
                $dependencia['id'],
                array('activo' => 1) + ( empty($dependencia['filtros']) ? array() : $dependencia['filtros'] ) + $filtros,
                array('imprimirSQL' => 0)
            );
            $regs_to_select = $this->get_list_to_select(array(
                'index_id' => $dependencia['id'],
                'index_desc' => $dependencia['desc'],
                'regs' => $regs
            ));
            
            break;
        }
        
        return $regs_to_select;
    }
    
    function search_to_select($catalogo, $params = array()) {
            
        $params['regs'] = $this->search(
            $catalogo,
            array_merge( empty($params['filtros']) ? array() : $params['filtros'], array('activo' => 1) ),
            empty($params['extras']) ? array() : $params['extras']
        );
        
        return parent::get_list_to_select($params);
    }
    
    function search($catalogo = '', $filtros = array(), $extras = array()) {

        $modelo = $this->get_modelo($catalogo);
        $result = $modelo ? $modelo->buscar($filtros, $extras) :  FALSE;
        
        if ( $result && in_array($catalogo, ['institucion']) && key_exists('nombre', current($result)) ) {
            
            foreach ($result as &$r) {
                
                $r['nombre'] = ucwords( strtolower($r['nombre']));
            }
        }
        
        return $result;
    }
    
    function indexed_search($catalogo = '', $indexes = NULL, $filtros = NULL, $extras = NULL, $multiply = FALSE) {
        
        $modelo = $this->get_modelo($catalogo);
        
        $result = $modelo ? $modelo->indexed_search($indexes, $filtros, $extras, $multiply) :  FALSE;
        
        if ( $result && in_array($catalogo, ['institucion']) && key_exists('nombre', current($result)) ) {
            
            foreach ($result as &$r) {
                
                $r['nombre'] = ucwords( strtolower($r['nombre']) );
            }
        }
        
        return $result;
    }
    
    function get_regs($catalogo = '', $filtros = array(), $extras = array()) {
        
        $modelo = $this->get_modelo($catalogo);        
        if ( !$modelo ) {
            
            return FALSE;
        }
        
        $config = @$modelo->get_config_to_view();
        
        $regs = $modelo->buscar($filtros, $extras);
    //    $this->CI->imprimir($regs, 1);
        
        foreach ($regs as &$reg) {
            
            $reg['opciones'] = '<a href="javascript:void(0);" title="Editar" onclick="reg('.($reg[$modelo->get_var('key_field')]).')"><i class="fa fa-edit"></i></a> | ';            
            $reg['opciones'] .= '<a href="javascript:void(0);" title="Eliminar" onclick="delete_reg('.($reg[$modelo->get_var('key_field')]).')"><i class="fa fa-trash-alt deleteAction" style="color:red;"></i></a>';
        }
        
        return !empty($config['dependencia']) || !empty($config['config_field']) ? $this->get_regs_with_dep($catalogo, $regs) : $regs;
    }
    
    function get_reg($catalogo = '', $id = 0, $lqs = NULL) {
        
        $modelo = $this->get_modelo($catalogo);        
        if ( !$modelo || empty($id) ) {
            
            return array('error' => 1, 'msg' => 'Parámetros incorrectos, verifique.');
        }
        
        $result['reg'] = current( $modelo->buscar(array('id' => $id)) ); 
        $result['error'] = empty($result['reg']) ? 1 : 0;
        $result['msg'] = empty($result['reg']) ? 'Registro no encontrado, verifique.' : '';
        
        return $result;
    }
    
    function delete($catalogo = '', $id = 0) {
        
        $modelo = $this->get_modelo($catalogo);        
        if ( !$modelo ) {
            
            return array('error' => 1, 'msg' => 'Parámetros incorrectos, verifique.');
        }
        
        $reg['borrado'] = 1;
        
        return $this->action_on_reg($modelo, $reg, 'update', array($modelo->get_var('key_field') => $id));
    }
/*
*Metodo para comparar los valores del campo clave 
*
**/
    function validarClave($reg = array(), $accion = '', $modelo = '', $cond = NULL, $id = NULL){

        $filtros =  [ 'clave' => strtoupper($reg['clave']), 'borrado' => 0];

        if ($accion == 'update'){
            $filtros['id_NOT_IN'] = $id;             
        }
        //$this->CI->imprimir($filtros, 1);
        $resp = $modelo->buscar($filtros);
        
        if ( !empty($resp) ) {
            echo json_encode(array('error' => 1, 'msg' => $this->CI->format_ul("Clave existente, verifique.") ) );
            die();
        }

    }
    
    function validateStatus($reg = array(), $accion = '', $modelo = '', $cond = NULL, $id = NULL) {
        
        $filtros = ['id_estatus' => $reg['ID_Estatus'], 'borrado' => 0];

        if ( $accion == 'update' ){
            
            $filtros['id_NOT'] = $id;             
        }
        //$this->CI->imprimir($filtros, 1);
        $resp = $modelo->buscar($filtros);
        
        if ( !empty($resp) ) {
            
            echo json_encode(array('error' => 1, 'msg' => "Estatus ya registrado, verifique." ) );
            die();
        }
    }
    
    function save($catalogo = '', $reg = array(), $id = 0, $name_reg = 'reg', $method = array() ){
        
        $modelo = $this->get_modelo($catalogo); 
        $cond = '';        

        if ( !$modelo ) {
            
            return array('error' => 1, 'msg' => "Par\xE1metros incorrectos, verifique.");
        }
        
        $rules = $modelo->get_rules($reg, $name_reg, $id);
        
        if ( !empty($id) ) {
            
            $cond = $modelo->get_var('key_field') . " = '" . $id . "'";
            $action = 'update';
            
        } else {
            
            $action = 'add';
        }
        
        //Agregar validación de clave en caso de que exista campo clave
        if ( array_key_exists('clave', $reg) ) {
            
            $reg['clave'] = strtoupper($reg['clave']);
            $method = array_merge( ['validarClave'], $method );
        }

        //la respuesta del siguiente metodo debe incluir el parametro 'error'
        return $this->validar_form($reg, $rules, $modelo, $action, $cond, $method, $id);
    }

    // My Functions
    // Funcion para obtener semana de año especifico

    // Determina cuantas semanas tiene el año recibido
    function getWeeks($year) {
        $opciones = [];
        for ( $i = 1; $i <= $this->getIsoWeeksInYear($year); $i++ ) {
            $week_array = $this->getStartAndEndDate($i, $year);
            $opciones[$i] = $week_array["week_start"] . " a " . $week_array["week_end"];
        }
        
        return $opciones;
    }

    function getIsoWeeksInYear($year) {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }
    //Function related to Database end information
    function getStartAndEndDateInterval($week, $year = 2019 ) {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }
    function getStartAndEndDate($week, $year = 2019 ) {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('M-d');
        $dto->modify('+6 days'); $ret['week_end'] = $dto->format('M-d');
        $this->datesToLower($ret);
        return $ret;
    }
    // Convertir caracteres de Fecha en Minuscula
    function datesToLower(&$week_array) {
        $week_array["week_start"] = strtolower($week_array["week_start"]);
        $week_array["week_end"] = strtolower($week_array["week_end"]);
        $this->changeDates($week_array);
    }

    function changeDates(&$week_array) {
        $monthsEng = ["jan", "apr", "aug", "dec"];
        $monthsSpa = ["ene", "abr", "ago", "dic"];
        for($i = 0; $i < 4; $i++) {
            $week_array["week_start"] = str_replace($monthsEng[$i], $monthsSpa [$i], $week_array["week_start"]);
            $week_array["week_end"] = str_replace($monthsEng[$i], $monthsSpa [$i], $week_array["week_end"]);
        }
    }


}
