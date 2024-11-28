<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Medio_Contacto_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'contacto' => 'cat_medio_contacto_model',
            'medioContacto' => 'medio_contacto_model',
            'viewModel' => 'view_model'
         );

        $this->loadModel();
    }
    
	function search($condicion = array(), $extras = array()) {
        
        return $this->CI->medio_contacto_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->medio_contacto_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }

    function searchCatContactMean($condicion = array(), $extras = array()) {
        
        return $this->CI->cat_medio_contacto_model->buscar($condicion, $extras);
    }

    function indexed_searchCatContactMean($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->cat_medio_contacto_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
    
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL) {
        
        $action = empty($id) ? 'add' : 'update';

        return $this->action_on_reg($this->CI->medio_contacto_model, $reg, $action, $action === 'add' ? NULL : "idContacto = '{$id}'");
    }

    function delete($id = NULL, $cond = NULL, $reg = []) {

		$action = 'update';

        if( isset($reg['idx']) ){
            $person = current( $this->searchByModel('viewModel', ['idx' => $reg['idx'], 'idContacto' => $id], ['imprimirSQL' => 0], 'personContact') );

            if( empty($person) )
                $this->CI->msg_error("No se encontr\xf3 el registro a eliminar, verifique.");
        }


        return $this->action_on_reg($this->CI->medio_contacto_model, $reg, $action, $cond ? $cond : "idContacto = '{$id}'");
	}
    
        
}
