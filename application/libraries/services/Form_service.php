<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Form_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'viewModel' => 'view_model',
            'formulario' => 'formulario_model',
            'question' => 'pregunta_model',
            'optionQuestion' => 'pregunta_opcion_model',
            'questionRol' => 'pregunta_rol_model',
         );

        $this->loadModel(); 

        $this->CI->load->library('services/catalogo_service');      
    }
    
    function search($condicion = array(), $extras = array()) {
        
        return $this->CI->formulario_model->buscar($condicion, $extras);
    }

    function search_OptionForm($condicion = array(), $extras = array()) {
        
        return $this->CI->pregunta_opcion_model->buscar($condicion, $extras);
    }
    
    function search_questionRol($condicion = array(), $extras = array()) {
        
        return $this->CI->pregunta_rol_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->formulario_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }

    function indexed_search_OptionForm($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->pregunta_opcion_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }

    function indexed_search_rol_question($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->pregunta_rol_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
                
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL, $statusUpdate = '') {
            
		$rules = $this->CI->formulario_model->get_rules($reg, $varPostIndex);

        $action = empty($id) ? 'add' : 'update';

        if( isset($reg['vigenciaIni']) && !empty($reg['vigenciaIni']) && isset($reg['vigenciaFin']) && !empty($reg['vigenciaFin']) ) {
            $reg['vigenciaIni'] = $this->CI->formato_fecha_bd($reg['vigenciaIni']);
            $reg['vigenciaFin'] = $this->CI->formato_fecha_bd($reg['vigenciaFin']);
        }
        			
        $result = $this->validar_form($reg, $rules, $this->CI->formulario_model, $action, $action === 'add' ? NULL : "idFormulario = '{$id}'");
                
        return $result;
    }
    
    function delete($id = NULL, $cond = NULL, $reg = []){

        $action = 'update';

        return $this->action_on_reg($this->CI->formulario_model, $reg, $action, $cond ? $cond : "idFormulario = '{$id}'");
    }

    function saveQuestion(array $reg, int $id = NULL) : array {

        if( isset($reg['idTipoCampo']) && !empty($reg['idTipoCampo']) ) {

            $idTipoCampo = current( $this->CI->catalogo_service->search('tipoCampo', ['clave' => $reg['idTipoCampo']]) )['idTipoCampo'];
            $reg['idTipoCampo'] = $idTipoCampo;

        }
        
        $result = $this->saveByModel('question', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveQuestionRol(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('questionRol', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveOptionQuestion(array $reg, int $id = NULL) : array {
        
        $result = $this->saveByModel('optionQuestion', $reg, $id, $cond = NULL);

        $this->CI->session->set_userdata('idPregunta', $reg['idPregunta']);

        return $result;
    }

    function saveQuestionCondition(array $reg, int $id = NULL) : array {
        
        $result = $this->saveByModel('optionQuestion', $reg, $id, $cond = NULL);

        return $result;
    }

    function clone($id = NULL){


        return $this->action_on_reg($this->CI->formulario_model, $reg, $action, $cond ? $cond : "idFormulario = '{$id}'");
    }

}
