<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

require_once PATH_INTERFACE . 'Persona_interface.php';

class Person_Service extends Class_Service implements Persona_interface {

    public function __construct() {
        
        parent::__construct();
     
         $this->modelToLoad = array(            
            'viewModel' => 'view_model',
            'persona' => 'persona_model',
         );

        $this->loadModel();
        $this->CI->load->library('services/medio_contacto_service');
        $this->CI->load->library('services/utileria_service');
    }
    
    function search($condicion = array(), $extras = array()) {
        
        return $this->CI->persona_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->persona_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }  

    function searchRFC($condicion = array(), $extras = array()) {
        
        return $this->CI->rfc_model->buscar($condicion, $extras);
    }
    
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL) {
        
        $rules = $this->CI->persona_model->get_rules($reg, $varPostIndex);

        $action = empty($id) ? 'add' : 'update';
        
        $result = $this->validar_form($reg, $rules, $this->CI->persona_model, $action, $action === 'add' ? NULL : "idPersona = '{$id}'");  //, ['userValidate'], $id
        
        if ( $result['error'] !== 0 )
            $this->CI->json($result);        
        
        return $result;
    }  
    
    function delete($id = NULL, $cond = NULL, $reg = []){

        $action = 'update';

        return $this->action_on_reg($this->CI->persona_model, $reg, $action, $cond ? $cond : "idPersona = '{$id}'");
    }

    function saveContactMean($idPerson, $reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL){

        $medioContacto = current( $this->CI->medio_contacto_service->searchCatContactMean(['clave' => $reg['claveMedioContacto']] ));

        if ( empty($medioContacto) ) 
            $this->CI->msg_error("Medio de contacto no encontrado, verifique.");       
        
        $person = current( $this->searchByModel('viewModel', ['idPersona' => $idPerson], ['imprimirSQL' => 0], 'personContact') );

        $reg = [
            'idx' => $person['idxPersona'],
            'idMedioContacto' => $medioContacto['idMedioContacto'],
            'etiqueta' => !empty($reg['etiqueta']) ? $reg['etiqueta'] : NULL,
            'valor' => !empty($reg['valor']) ? $reg['valor'] : NULL,
            'prioridad' => !empty($reg['prioridad']) ? $reg['prioridad'] : NULL,
        ];
		
        return $this->CI->medio_contacto_service->save($reg, $id, $varPostIndex, $method);
    }
    
    function getContactMean($idPerson, $type = [], $priority = [], $valor = ''){

        $result = [];

        if( !empty($valor) ){
            
            $idx = current( $this->CI->utileria_service->searchByModel('viewModel', ['email' => $valor], ['imprimirSQL' => 0], 'getContactUser') );

            if( !empty($idx) )
                return $this->search(['idx' => $idx['idx'], 'borrado' => 0], []);
            else 
                return [];
        }

        $persona = current( $this->search(['idPersona' => $idPerson, 'borrado' => 0], []) );

        $result['persona'] = $persona;

        $claveContacto = implode("', '", $type);

        $medioContacto = $this->CI->medio_contacto_service->indexed_searchCatContactMean('idMedioContacto',['clave_IN' => $claveContacto, 'borrado' => 0] );
        
        $idsContacto = implode(',', array_keys($medioContacto));

        if(!empty($priority))
            $idsPrioridad = 'FIELD(prioridad,' . implode(",", $priority) . ')';
        
        if(!empty($persona['idx'])){
            $contact = $this->CI->medio_contacto_service->search(['idx' => $persona['idx'], 'idMedioContacto_IN' => $idsContacto, 'borrado' => 0], ['imprimirSQL' => 0, 'orderBy' =>  !empty($idsPrioridad) ? $idsPrioridad : 'idMedioContacto']);
            $result['persona']['contact'] = $contact;
            return $result;
        } else {
            return $result['contact'] = [];
        }

    }

    function getContactInactive($email = ''){

        $result = [];

        if( !empty($email) ){
            
            $idx = current( $this->CI->medio_contacto_service->searchByModel('viewModel', ['email' => $email], ['imprimirSQL' => 0], 'getContactUserInactive') );

            if( !empty($idx) )
                return $this->search(['idx' => $idx['idx'], 'borrado' => 0], []);
            else 
                return [];
        }
    }
    
    function deleteContactMean($idPerson, $idContact = NULL, $cond = NULL){

        if( !empty($idPerson)) {
            $person = current( $this->searchByModel('viewModel', ['idPersona' => $idPerson, 'borrado' => 0], ['imprimirSQL' => 0], 'personContact') );
            $reg = ['borrado' => 1, 'idx' => $person['idxPersona']];
        } else {
            $reg = ['borrado' => 1];
        }

        $result = $this->CI->medio_contacto_service->delete($idContact, $cond, $reg);

        return $result;
    }
    
    function saveTMP($reg) {

		$persona["nombre"] = $reg["nombre"];
        $persona["apellidos"] = $reg["apellido"];

        $idx = $this->CI->utileria_service->getIdx('persona');
        $persona['idx'] = $idx['id'];

        $result = $this->save($persona);                

        $data['idContacto'] = ''; 
        $data['claveMedioContacto'] = 'MAIL';
        $data['etiqueta'] = 'email';
        $data['prioridad'] = 1;
        $data['valor'] = !empty($reg['correo']) ? $reg['correo'] : NULL;
        
        $saveContactMean = $this->saveContactMean($result['id'], $data, !empty($reg['idContacto']) ? $reg['idContacto'] : '', 'medioContactoMail', NULL);

		if ( $result['error'] === 0 ) {
				
				$idPersona = $result['id'];
				$password ='';
			  
				$response = $this->CI->usuario_service->save(['idPersona' => $idPersona, 'password' => $password, 'URL' => $reg['foto'], 'tipo' => $reg['red']]);
		}

        return $response;
    }

}
