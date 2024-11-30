<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Patient_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'viewModel' => 'view_model',
            'paciente' => 'patient_model',
            'respuesta' => 'respuesta_model',
            'medioContact' => 'medio_contacto_model',
            'responsible' => 'cat_responsable_model',
            'visit' => 'visita_model',
         );

        $this->loadModel(); 

        $this->CI->load->library('services/historico_estatus_service');
    }
    
    function search($condicion = array(), $extras = array()) {
        
        return $this->CI->patient_model->buscar($condicion, $extras);
    }

    function search_response($condicion = array(), $extras = array()) {
        
        return $this->CI->respuesta_model->buscar($condicion, $extras);
    }

    function search_response_contacto($condicion = array(), $extras = array()) {
        
        return $this->CI->medio_contacto_model->buscar($condicion, $extras);
    }

    function search_response_responsable($condicion = array(), $extras = array()) {
        
        return $this->CI->cat_responsable_model->buscar($condicion, $extras);
    }

    function search_visit($condicion = array(), $extras = array()) {
        
        return $this->CI->visita_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->patient_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }

    function indexed_search_response($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->respuesta_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
                
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL, $statusUpdate = '') {
            
		$rules = $this->CI->patient_model->get_rules($reg, $varPostIndex);

        $action = empty($id) ? 'add' : 'update';         
              			
        $result = $this->validar_form($reg, $rules, $this->CI->patient_model, $action, $action === 'add' ? NULL : "idPaciente = '{$id}'");
                
        return $result;
    }
    
    function delete($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->patient_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->patient_model, $reg, $action, $cond ? $cond : "idPaciente = '{$id}'");
    }

    function deleteResponsible($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->cat_responsable_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->cat_responsable_model, $reg, $action, $cond ? $cond : "idResponsable = '{$id}'");
    }

    function deleteContact($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->medio_contacto_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->medio_contacto_model, $reg, $action, $cond ? $cond : "idContacto = '{$id}'");
    }

    function deleteResponse($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->respuesta_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->respuesta_model, $reg, $action, $cond ? $cond : "idRespuesta = '{$id}'");
    }

    function saveResponse(array $reg, int $id = NULL) : array {

		$changedReg = 0;
        $cveFieldOption = '';

        if( isset($reg['cveFieldOption']) ) {
            $cveFieldOption = $reg['cveFieldOption'];
            unset($reg['cveFieldOption']);
        }

        $action = empty($id) ? 'add' : 'update';
		
        if($action == 'update') {

			$registroOrigen = current( $this->search_response(['id' => $id, 'borrado' => 0]) );
            
            if( !empty($registroOrigen['idVisita']))
			    $visit = current( $this->search_visit(['id' => $registroOrigen['idVisita']], ['imprimirSQL' => 1]) );
            else
                $visit = NULL;

            $changedReg = $this->checkDiferencias($reg, $registroOrigen);
            $question = current( $this->CI->form_service->search_questions(['id' => $registroOrigen['idPregunta']]) );

            $user_data = $this->CI->session->userdata();

            $status = current( $this->CI->catalogo_service->search('estatus', ['clave' => 'UPDATE_RESPONSE']) );

            $data['idUsuario'] = $user_data['idUsuario'];
            $data['idRol'] = $user_data['idRol'];
            $data['idRegistro'] = $id;
            $data['fecha'] = date('Y-m-d H:i:s');

            if( !empty($changedReg['flag']) && $question['etiqueta'] != 'CLUES' ) {
                if( $cveFieldOption == '1' ){
                    $optionAnterior = current($this->CI->form_service->search_OptionForm(['id' => $changedReg['anterior']]) );
                    $changedReg['anterior'] = $optionAnterior['opcion'];
                    $optionActual = current($this->CI->form_service->search_OptionForm(['id' => $changedReg['actual']]) );
                    $changedReg['actual'] = $optionActual['opcion'];
                }

                $data['comentario'] = 'Actualizó "'. $question['etiqueta'] .'" de "'. $changedReg['anterior'] .'" a "'. $changedReg['actual'] .'" '. (!empty($visit['numVisita']) ? ('en la visita número ' . $visit['numVisita']) : '' ) .''; 
            }

            //$this->CI->imprimir($question);
            //$this->CI->imprimir($changedReg,1);
		}

        $result = $this->saveByModel('respuesta', $reg, $id, $cond = NULL);

        if( $result['error'] == 0 && $action == 'update' && !empty($changedReg['flag']) ) {
        	$this->CI->historico_estatus_service->save($data, 'respuesta', $status['idEstatus'], '');
        }


        return $result;
    }

    function checkDiferencias($reg = [], $registroOrigen = []) {
		
		foreach ($registroOrigen as $key => $regOrigin) {
			
			if(!empty($reg[$key])) {
				if($reg[$key] != $regOrigin)
					return ['actual' => $reg[$key], 'anterior' => $regOrigin, 'flag' => 1];
			}
		}
	}

    function saveContact(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('medioContact', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveResponsible(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('responsible', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveVisit(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('visit', $reg, $id, $cond = NULL);

        return $result;
    }

}
