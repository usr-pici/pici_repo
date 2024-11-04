<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of MY_Model
 *
 * @author Felipe Avila
 */
class MY_Model extends CI_Model {

    //put your code here
    protected $id = 0;
    protected $oDB;
    protected $name_table;
    protected $fields_table = array(); // Como se llamaran los campos en la tabla => vista (este ultimo del doc donde se envien los datos)
    protected $key_field;
    protected $search_order_by;
    protected $valid_value_for_key_field = '/^[1-9][\d]*$/';
    protected $error;
    protected $msg;
    protected $title;
    protected $config_database = 'default';
    protected $cveStatusHistorical;
    protected $cveStatusUpdate;
    protected $cveStatusAdd;
    protected $cveStatusDelete;
    protected $flagHistory = 'historico';
    protected $flagDelete = 'borrado';

    function __construct($config_database = 'default') {

        parent::__construct();
    }
    
    function getCompiledQuery($regs, $action = 'add') {
        
        $result = [];
        
        foreach ($regs as $r) {
            
            $result[] = $this->oDB->set( $r )->get_compiled_insert( $this->name_table );
        }
        
        return $result;
    }
    
    function get_config_to_view() {
        
        $data['title'] = utf8_encode( $this->title );
        $data['key_field'] = $this->key_field;
        $data['dependencia'] = array();
        $data['config_field'] = array();
        
        $fields = $this->get_rules();
 
        $data['headers'][] = "ID";
        $data['columns'][] = array('data' => $this->key_field);
        
        foreach ($fields as $index => $field) {
            
            $data['headers'][] = $field['label'];
            $data['columns'][] = array(
                'data' => $index, 
                'type' => empty($field['type']) ? '' : $field['type'],
                'style' => empty($field['style']) ? '' : $field['style'],
                'required' => preg_match('/required/i', $field['rules']),
                'class' => empty($field['class']) ? '' : $field['class'],
            );
            
            if ( !empty($field['dependencia']) ) {
                $data['dependencia'][$index] = $field['dependencia'];
            }
            
            if ( !empty($field['config']) ) {
                $data['config_field'][$index] = $field['config'];
            }
        }
        
        $data['headers'][] = "Opciones";
        $data['columns'][] = array('data' => 'opciones');
        
        return $data;
    }
    
    function setVar($var = '', $val = NULL) {
        
        if ( property_exists($this, $var) ) {
            
            $this->$var = $val;
        }
    }
    
    function set_config($table = '', $title = NULL, $param = NULL) {
        
        if ( $title ) {
            
            $this->title = $title;
        }
        
        if ( !empty($param) && is_array($param) ) {
            
            foreach ($param as $k => $v) {
                
                $this->setVar($k, $v);
            }
        }
        
        $this->oDB = $this->load->database($this->config_database, TRUE);
        
        $this->name_table = $table;
        $fields = $this->oDB->field_data($table);
        
        $keys = 0;
        foreach ($fields as $field) {
            
            $this->fields_table[$field->name] = $field->name;
            
            if ( $field->primary_key ) {
                $keys++;
                $this->key_field = $field->name;
            }
        }
        
        $this->key_field = $keys > 1 ? null : $this->key_field;
//        $this->imprimir($this->fields_table);
//        $this->imprimir($this->key_field);
        $this->oDB->close();
    }
    
    function getID() {

        return $this->id;
    }

    function setID($id) {
        $this->id = $id;
    }

    function get_var($var = '') {

//        return in_array($var, array('id', 'error', 'msg', 'key_field', 'cveStatusAdd', 'cveStatusUpdate', 'cveStatusHistorical')) ? $this->$var : NULL;
        return $this->$var;
    }

    function obtenerRegistro($id = NULL) {

        $datos = array();

        if ($this->isValidId($id)) {

            $datos = $this->buscar(array($this->key_field => $id), array('imprimirSQL' => 0));
//            $datos = $this->buscar(array('id' => $id), array('imprimirSQL' => FALSE));

            if (!empty($datos))
                $datos = $this->_getDatosParaForm(1, $datos[0]);
        }

        return $datos;
    }

    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE, $method = 'buscar') {
        
        $regs = $this->$method($condicion, $extras);
        
        if ( empty($regs) ) {

            return $regs;
        }

        if (!is_array($indexes))
            $indexes = array($indexes);

        foreach ($indexes as $index) {

            if (!array_key_exists($index, $regs[0])) {

                return FALSE;
            }
        }

        $result = [];
        foreach ($regs as $reg) {

            $tmp = '';
            foreach ($indexes as $index) {

                $tmp .= "['{$reg[$index]}']";
            }

            eval("\$result{$tmp}" . ( $multiply ? "[]" : "") . " = \$reg;");
        }

        return $result;
    }
    
    function execute_view(&$sql, &$condicion, &$extras) {
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);    
        
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );
        
        if ( !empty($extras['having']) )
            $sql .= " HAVING " . ( is_array($extras['having']) ? implode(", ", $extras['having']) : $extras['having'] );
        
        if ( !empty($extras['orderBy']) )
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );
        
        if ( !empty($extras['limit']) ) {
            
            $offset = empty($extras['offset']) ? 0 : $extras['offset'];
            $sql .= " LIMIT {$offset}, {$extras['limit']}";
        }
        
        return $this->execute_query($sql, $extras);
    }
    
    function execute_query($sql, $extras = array()) {

        if ( is_array($extras) && !empty($extras) )
            extract($extras);

        if ( !empty($imprimirSQL) )
            $this->imprimir($sql);

        try {

            $this->oDB = $this->load->database($this->config_database, TRUE);
            
            $query = $this->oDB->query($sql);
//            $this->imprimir($query);
            
            $datos = is_object($query) ? $query->result_array() : $query;            
            
            $this->oDB->close();
                
            return $datos;
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
    }
    
    function buscar($condicion = null, $extras = null) {

        $datos = array();
        $txtGroupBy = "";
        $txtCondicion = "";
        $txtOrderBy = "";
        $txtHaving = "";

        if (is_array($extras) && !empty($extras))
            extract($extras);

        if (!isset($limit))
            $limit = 0;

        if (!isset($offset))
            $offset = 0;

        if (!isset($campos))
            $campos = '*';

        if ( !empty($condicion) )
            $txtCondicion = " WHERE " . ( is_array($condicion) ? implode(" AND ", $condicion) : $condicion );

        if ( !empty($groupBy) )
            $txtGroupBy = " GROUP BY " . $groupBy;
        
        if ( !empty($having) )
            $txtHaving = " HAVING " . $having;

        if ( !empty($orderBy) || !empty($this->search_order_by) )
            $txtOrderBy = " ORDER BY " . ( empty($orderBy) ? $this->search_order_by : ( is_array($orderBy) ? implode(", ", $orderBy) : $orderBy ) );


        if ($limit == -1)
            $sql = "SELECT COUNT(*) AS total FROM " . $this->name_table . $txtCondicion;
        else
            $sql = "SELECT {$campos} FROM " . $this->name_table . $txtCondicion . $txtGroupBy . $txtHaving . $txtOrderBy . ($limit > 0 ? " LIMIT $offset, $limit" : "");

        if (isset($imprimirSQL) && $imprimirSQL)
            $this->imprimir($sql);

        try {

            $this->oDB = $this->load->database($this->config_database, TRUE);

            $query = $this->oDB->query($sql);
//            $this->imprimir($query);
            
            if ($limit == -1) {
                
                $datos = $query->row_array();
                
            } elseif ($query->num_rows() > 0) {
                
                $datos = $query->result_array();
            }
            
            $this->oDB->close();
                
            return $datos;
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
    }

    function set_msg($msg = '', $error = 1) {

        $this->error = $error;
        $this->msg = $msg;

        return $error != 1;
    }
    
    function get_msg() {
        
        return array('msg' => $this->msg, 'error' => $this->error);
    }

    function addBatch($regs = array()) {

        if (empty($regs)) {

            return $this->set_msg("Error: Datos no v\xE1lidos.");
        }

        try {

            $this->oDB = $this->load->database($this->config_database, TRUE);
            
            $result = $this->oDB->insert_batch($this->name_table, $regs);
            
            $this->oDB->close();
            
            $num_regs = count($regs);
            $this->set_msg("Se agregaron {$result} registros de {$num_regs}", $result == $num_regs ? 0 : 1);
            
            return array_merge($this->get_msg(), ['total' => $num_regs, 'added' => $result, 'fails' => $num_regs - $result]);
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
    }
    
    function updateBatch($regs = array(), $cond = NULL) {

        if (empty($regs)) {

            return $this->set_msg("Error: Datos no v\xE1lidos.");
        }

        try {

            $this->oDB = $this->load->database($this->config_database, TRUE);
            
            $result = $this->oDB->update_batch($this->name_table, $regs, empty($cond) ? $this->key_field : $cond);
            
            $this->oDB->close();
            
            $num_regs = count($regs);
            $this->set_msg("Se actualizaron {$result} registros de {$num_regs}", $result == $num_regs ? 0 : 1);
            
            return array_merge($this->get_msg(), ['total' => $num_regs, 'updated' => $result, 'fails' => $num_regs - $result]);
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
    }
    
    function add($campos = array()) {
        
//        $campos = $this->_getDatosParaForm(2, $reg);

        if (empty($campos)) {

            return $this->set_msg("Error: Datos no v\xE1lidos.");
        }

        try {
            
            if ( isset($campos[$this->key_field]) && empty($campos[$this->key_field]) )
                unset($campos[$this->key_field]);

            $this->oDB = $this->load->database($this->config_database, TRUE);
            
            $this->oDB->insert($this->name_table, $campos);

            if ( $this->oDB->affected_rows() != 1 ) {

                $this->oDB->close();
                
                return $this->set_msg("Error: No se guard\xF3 el registro. Intente otra vez.");
                
            } else {
                
                $this->id = $this->oDB->insert_id();
                
                $this->oDB->close();
                
                if ( !empty($this->cveStatusAdd) )
                    $this->saveHistoricalStatus($this->cveStatusAdd);
                
                return $this->set_msg("El registro ha sido guardado.", 0);
            }
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
    }
    
    function checkChanges($campos = [], $reg_bd = []) {
        
        $changed = FALSE;
        foreach ($campos as $k => $v) {
            
            if ( array_key_exists($k, $this->fields_table) && $reg_bd[$k] != $v ) {
                
                $changed = TRUE;
                break;
            }
        }
//        $this->imprimir($campos);
//        $this->imprimir($reg_bd);
//        $this->imprimir($changed);
        if ( $changed ) {
            
            if ( array_key_exists($this->flagHistory, $this->fields_table) ) {
                
                $reg_bd[$this->flagHistory] = 1;
            }
            
            $id_bd = $reg_bd[$this->key_field];
            unset($reg_bd[$this->key_field]);
            
            $cveStatusAdd = $this->cveStatusAdd;
            $this->cveStatusAdd = FALSE;
            $this->add($reg_bd); // Guardamos "copia" del registro original
            $this->cveStatusAdd = $cveStatusAdd;
            
            /* Se debe invocar inmediatamente después de haber guardado la "copia" del registro original */
            $this->saveHistoricalStatus($this->cveStatusHistorical, $id_bd);
            
            $this->id = $id_bd;
        }
    }
    
    function saveHistoricalStatus($cveStatus = 0, $traza = NULL) {
        
        $this->oDB = $this->load->database($this->config_database, TRUE);
        
        $query = $this->oDB->query("select * from cat_estatus where clave = '" . strtoupper($cveStatus) . "'");
//            $this->imprimir($query);
        if ($query->num_rows() > 0) {

            $status = current( $query->result() ) ;
//            $this->imprimir($status);

            $this->oDB->insert('historico_estatus', [
                'tabla' => $this->name_table, 
                'idRegistro' => $this->id, 
                //'traza' => $traza,
                'idEstatus' => $status->idEstatus, 
                'idUsuario' => LOGGED ? $this->session->userdata('idUsuario') : NULL, 
                'idRol' => LOGGED ? $this->session->userdata['rol']['idRol'] : NULL, 
                'fecha' => date('Y-m-d H:i:s')
            ]);
        }            
            
        $this->oDB->close();
    }

    function update($campos = array(), $cond = NULL) {
        
//        $campos = $this->_getDatosParaForm(2, $reg);

        if ( empty($campos) || ( (!isset($campos[$this->key_field]) || !$this->isValidId($campos[$this->key_field], true)) && empty($cond) )) {
//            $this->imprimir( $this->isValidId($campos[$this->key_field]) );
//            $this->imprimir( "No pasa primer validacion", 1 );
            return $this->set_msg("Error: Datos no v\xE1lidos.");
        }
        //
        try {
            
            if ( empty($campos[$this->key_field]) || !empty($this->cveStatusHistorical) ) {
                    
                $reg_bd = current( self::buscar(empty($cond) ? "{$this->key_field} = '{$this->id}'" : $cond, ['imprimirSQL' => 0, 'limit' => 1]) ); //$this->name_table === 'documento' ? 1 : 
                $this->id = $reg_bd[$this->key_field];
                //die($reg_bd);
                if ( !empty($this->cveStatusHistorical) ) {
                    $this->checkChanges($campos, $reg_bd);
                }
            }
            

            $this->oDB = $this->load->database($this->config_database, TRUE);
		   
            if ( $this->oDB->update($this->name_table, $campos, empty($cond) ? array($this->key_field => $this->id) : $cond ) ) {
                
                $this->oDB->close();                
                
                if ( !empty($this->cveStatusUpdate) ) {                    
                    
                    $this->saveHistoricalStatus($this->cveStatusUpdate);
                }                    
                
                return $this->set_msg("El registro ha sido actualizado.", 0);
                
            } else {
//                $this->imprimir( $this->oDB->last_query(), 1 );
                return $this->set_msg("Error: No se actualizo el registro. Intente otra vez.");
            } 
            
        } catch (Exception $e) {
//            $this->imprimir( $this->oDB->last_query(), 1 );
            return $this->set_msg($e->getMessage());
        }
    }

    function delete($id = 0, $cond = NULL) {
        
        if ( empty($cond) && !$this->isValidId($id, true) ) {
            
            return $this->set_msg("Error: Datos no v\xE1lidos.");
        }
        //$data = $this->buscar($id);
        try {
            
            $this->oDB = $this->load->database($this->config_database, TRUE);
            
            $this->oDB->delete($this->name_table, empty($cond) ? array($this->key_field => $this->id) : $cond);
            
            if ( $this->oDB->affected_rows() > 0 ) {
                
                $this->oDB->close();
/*
 * Mandar a llamar a funcion bitaacora
 */                
//                if ( empty($bitacora_datos['accion_clave']) ) {
//     
//                    $bitacora_datos['accion_clave'] = 'eliminar';
//                }
//                $bitacora_datos['borrado_fisico'] = 1;
//                
//                $this->bitacora($bitacora_datos, $data);
                
                return $this->set_msg("El registro fue eliminado.", 0);
                
            } else {
                
                $this->oDB->close();

                return $this->set_msg("Error: No se elimin\xF3 el registro. Intente otra vez.");
            }
            
        } catch (Exception $e) {

            return $this->set_msg($e->getMessage());
        }
       
    }

    function _getDatosParaForm($modo, $row) {

        $reg = array();

        if (empty($row))
            return $reg;

        // $modo == 2 Arreglo para alta/actualizacion del registro
        $campos = $modo == 2 ? array_flip($this->fields_table) : $this->fields_table;

        foreach ($row as $k => $v) {

            if (array_key_exists($k, $campos))
                $reg[$campos[$k]] = $v;
        }

        return $reg;
    }

    function prepareArrayParam($tree) {

        if (is_array($tree)) {

            foreach ($tree as $index => $value) {
                $tree[$index] = $this->prepareArrayParam($value);
            }
            return $tree;
        } elseif (is_object($tree)) {

            return $tree;
        }

        return addslashes($tree);
    }

    function isValidId($valor = NULL, $asignar = FALSE) {

        if (empty($valor) || preg_match($this->valid_value_for_key_field, $valor) == 0) {

            return FALSE;
            
        } else {

            if ($asignar)
                $this->id = $valor;

            return TRUE;
        }
    }

    function imprimir($var, $die = FALSE) {
        
        echo "<pre>"; var_dump($var); echo "</pre>";
        
        if ($die) { exit(0); }
    }
} 
