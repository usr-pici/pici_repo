<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

/**
 * Description of usuario_service
 *
 * @author Felipe Avila
 */
class Pharma_Service extends Class_Service {

    public function __construct() {

        parent::__construct();

        $this->modelToLoad = array(
            'pharma' => 'cat_farmaceuticas_model'
        );
        // Carga de los modelos
        $this->loadModel();
//        
//        $this->CI->load->library('services/view_service');
        $this->CI->load->library('services/utileria_service');
    }

    function search($condicion = array(), $extras = array()) {

        return $this->CI->cat_farmaceuticas_model->buscar($condicion, $extras);
    }

    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {

        return $this->CI->cat_farmaceuticas_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
    
    function save($reg = [], $id = NULL, $varPostIndex = 'reg', $method = NULL) {

        $rules = $this->CI->cat_farmaceuticas_model->get_rules($reg, $varPostIndex);
        
        $action = empty($id) ? 'add' : 'update';
        
        $result = $this->validar_form($reg, $rules, $this->CI->cat_farmaceuticas_model, $action, $action === 'add' ? NULL : "idFarmaceutica = '{$id}'", ['keyExistsValidation'], $id);
                
        return $result;
    }
    
    function delete($id = NULL, $cond = NULL) {

        return $this->action_on_reg($this->CI->promocion_model, ['borrado' => 1], 'update', $cond ? $cond : "idPromocion = '{$id}'");
    }
    
    function update($reg, $id = NULL, $cond = NULL) {

        return $this->action_on_reg($this->CI->promocion_model, $reg, 'update', $cond ? $cond : "idPromocion = '{$id}'");
    }
}
