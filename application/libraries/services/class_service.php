<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of usuario_service
 *
 * @author Felipe Avila
 */
abstract class Class_Service {
    /** @var MY_Controller */
    protected $CI;
    protected $modelToLoad;
    protected $config_pago;
    protected $config_paqueteria;
    protected $stripe;

    public function __construct() {
	
        $this->CI = &get_instance();        
    }
    
    abstract function search($filtros = [], $extras = []);
    
    abstract function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE);
    
    abstract function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL);
    
    abstract function delete($id = NULL, $cond = NULL);
    
    function loadModel() {
        
        foreach ($this->modelToLoad as $model) {
            
            $this->CI->load->model($model);
        }
    }
    
    function getModel($index = '') {        
                
        if ( !array_key_exists($index, $this->modelToLoad) ) {
            
            $this->CI->msg_error("El modelo con \xEDndice: {$index} no se encontr\xF3, verifique.");            
        }
        
        return  $this->CI->{$this->modelToLoad[$index]};
    }
    
    function saveByModel($index, $reg, $id = 0, $cond = NULL) {
        
        $action = empty($id) ? 'add' : 'update';
        $model = $this->getModel($index);
        
        return $this->action_on_reg($model, $reg, $action, $action === 'add' ? NULL : ( empty($cond) ? [$model->get_var('key_field') => $id] : $cond ));
    }
    
    function actionBatchByModel($index, $regs, $action = 'add') {
        
        $model = $this->getModel($index);
        
        return $action === 'add' ? $model->addBatch($regs) : $model->updateBatch($regs);
    }
    
    function searchByModel($index, $filtros = [], $extras = [], $metodo_buscar = 'buscar') {
        
        $model = $this->getModel($index);
            
        $result = $model->$metodo_buscar($filtros, $extras);
        
        return $result;
    }
    
    function indexedSearchByModel($index, $indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE, $metodo_buscar = 'buscar') {
        
        $model = $this->getModel($index);
            
        $result = $model->indexed_search($indexes, $condicion, $extras, $multiply, $metodo_buscar);
        
        return $result;
    }
    
    function get_reg($filtros = array(), $extras = array(), $metodo_buscar = 'search') {
        
        return current( $this->$metodo_buscar($filtros, array_merge($extras, array('limit' => 1))) );
    }
    
    function keyExistsValidation($reg = array(), $accion = '', $modelo = NULL, $cond = NULL, $id = NULL){

        $filtros =  ['clave' => strtoupper($reg['clave']), 'borrado' => 0];

        if ( $id ) {
            
            $filtros['id_NOT_IN'] = $id;             
        }
//        $this->CI->imprimir($filtros, 1);
        $resp = $modelo->buscar($filtros, ['imprimirSQL' => 0]);
        
        if ( !empty($resp) ) {
            
            echo json_encode(array('error' => 1, 'msg' => $this->CI->format_ul("Clave existente, verifique.") ) );
            die();
        }
    }
    
    function validar_form($reg = NULL, $rules = NULL, &$oModel = NULL, $action = NULL, $cond = NULL, $method = array(), $id = NULL) {
        
        $this->CI->form_validation->set_rules($rules);
        
        if ( empty($rules) || $this->CI->form_validation->run() ) {
            
            if ( !empty($method) ) {
                
                foreach ($method as $key => $value) {
                    
                    $this->$value($reg, $action, $oModel, $cond, $id);
                }
            }
            
            return $this->action_on_reg($oModel, $reg, $action, $cond);
            
    	} else {
                
                return array('error' => 1, 'msg' => $this->CI->format_ul($this->CI->form_validation->get_all_errors(), 'danger'));
    	}
    }
    
    function get_order_by_datatable($default = '') {
        
        $data = $this->CI->input->post();
        $orderBy = $default;
        
        if ( !empty($data['order'][0]['column']) ) {
            
            $orderBy = $data['columns'][$data['order'][0]['column']]['data'] . ' ' . $data['order'][0]['dir'];
        }
//        $this->CI->imprimir($orderBy, 1);
        
        return $orderBy;
    }
    
    function obtener_parametros_datatable(&$oModel, $filtros = array(), $extras = array(), $params = array()) {
        
        $metodo_buscar = 'buscar';
        
        if ( !empty($params) && is_array($params) ) {
            
            extract($params);
        }
        
        if ( empty($metodo_num_regs) ) {
            
            $metodo_num_regs = $metodo_buscar;
        }
        
        $num_rows = $this->CI->input->post('length') ? $this->CI->input->post('length') : 50;
        $start = $this->CI->input->post('start') ? $this->CI->input->post('start') : 0;        
        
        $total_regs = $oModel->$metodo_num_regs($filtros, array('limit' => -1));
        
        $regs = $oModel->$metodo_buscar(
            $filtros, 
            array_merge($extras, array('limit' => $num_rows, 'offset' => $start))
        ); 
        
        $array['recordsTotal'] = $total_regs['total']; //numero total de registros
        $array['recordsFiltered'] = $total_regs['total']; //numero total de registros
        $array['data'] = $regs; //registros

        return($array);
    }
    
    /*
     * Para evitar confusiones, hay que asegurarse de sólo usar modelos incluidos en el servicio en cuestión.
     * Está pendiente el ajuste para asegurarlo por código.
     */
    function action_on_reg(&$model, $reg = array(), $action = 'add', $cond = null) {
        
        if ( in_array($action, array('add', 'update', 'delete')) === FALSE ) {
            
            return array('error' => 1, 'msg' => 'Parametros incorrectos, verifique.');
        }
        
        $action === 'add' ? $model->$action( $reg ) : $model->$action( $reg, $cond );
        
        $error = $model->get_var('error');
        
        return array('error' => $error, 'msg' => $model->get_var('msg'), 'id' => $error === 0 ? $model->getID() : 0);
    }
    
    function add_and_list($reg = array(), $params = array(), $method_add = 'add', $method_list = 'get_list_to_chosen') {
        
        $result_add = $this->$method_add($reg);
        
        if ( $result_add['error'] === 0 ) {
            
            return $this->$method_list( array_merge($params, array('id_reg' => $result_add['id'])) );
            
        } else {
            
            return $result_add;
        }
    }
    
    function get_list_to_select($params = array()) {
        
        $id_reg = [];
        $filtros = array();
        $index_id = 'id';
        $index_desc = 'descripcion';
        $etiqueta = '-Elegir-';
        $metodo_buscar = 'search';
        $busqueda_indexada = FALSE;
        $indexes = NULL;
        $multiply = FALSE;
        $extras = null;
        $con_etiqueta = TRUE;
        $con_clave = FALSE;
        $index_clave = 'clave';
        
        if ( !empty($params) ) {
            
            extract($params);
        }
        
        if ( !empty($id_reg) && !is_array($id_reg) ) {
            
            $id_reg = (array) $id_reg;
        }
        
        if ( ( !isset($regs) || !is_array($regs) ) && !empty($metodo_buscar) ) {
//            $this->CI->imprimir($regs, 1);
            $regs = $busqueda_indexada ? $this->$metodo_buscar($filtros, $extras) : $this->$metodo_buscar($indexes, $filtros, $extras, $multiply);
        }
        
        $result = $con_etiqueta ? array("<option value=''>{$etiqueta}</option>") : array();
        
        foreach ( $regs as $index => $desc ) {
            
            if ( is_array($desc) ) {
                
                $txt_desc = $desc[$index_desc];
                
                if ( $con_clave && !empty($desc[$index_clave]) ) {
                    
                    $txt_desc = $desc[$index_clave] . ' - ' . $txt_desc;
                }
                
                $selected = !empty($id_reg) && in_array($desc[$index_id], $id_reg) ? ' selected' : '';
                $result[] = "<option value='{$desc[$index_id]}' {$selected}>{$this->CI->format_output_screen($txt_desc)}</option>";
                
            } else {
                
                $selected = !empty($id_reg) && in_array($index, $id_reg) ? ' selected' : '';
                $result[] = "<option value='{$index}' {$selected}>{$this->CI->format_output_screen($desc)}</option>";
            }
        }
            
        return implode('', $result);
    }

    function get_list_to_select_array($params = array(), $selected = array()) {

        $filtros = array();
        $index_id = 'id';
        $index_desc = 'descripcion';
        $etiqueta = '-Elegir-';
        $metodo_buscar = 'search';
        $extras = null;
        $id_reg = $registros = array();
        
        if ( !empty($params) ) {
            
            extract($params);
        }

        if(isset($id_reg))
            $registros = explode(',', $id_reg);
        
        $result = array("<option value=''>{$etiqueta}</option>");
            
        foreach ( $this->$metodo_buscar($filtros, $extras) as $reg ) {

            $selected = in_array($reg[$index_id], $registros) ? ' selected' : '';
            $result[] = "<option value='{$reg[$index_id]}'". $selected .">{$this->CI->format_output_screen($reg[$index_desc])}</option>";
        }
            
        return implode('', $result);
    }
}