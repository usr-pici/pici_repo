<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Usuario_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'viewModel' => 'view_model',
            'usuario' => 'usuario_model'
         );

        $this->loadModel(); 
    }
    
    function search($condicion = array(), $extras = array()) {
        
        return $this->CI->usuario_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->usuario_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
                
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL, $statusUpdate = '') {
            
		$rules = $this->CI->usuario_model->get_rules($reg, $varPostIndex);

        $action = empty($id) ? 'add' : 'update';
        
		if ( $statusUpdate ) 
			$this->CI->usuario_model->setVar('cveStatusUpdate', $statusUpdate);
			
        $result = $this->validar_form($reg, $rules, $this->CI->usuario_model, $action, $action === 'add' ? NULL : "idUsuario = '{$id}'");
                
        return $result;
    }
    
    function delete($id = NULL, $cond = NULL, $reg = []){

        $action = 'update';

        return $this->action_on_reg($this->CI->usuario_model, $reg, $action, $cond ? $cond : "idUsuario = '{$id}'");
    }

    function sendLinkValidMail($mail = '') {

		$this->CI->load->library('encryption');  

        $contacto = current( $this->CI->persona_service->getContactInactive($mail) );
        
        $user = current( $this->search(['idPersona' => !empty($contacto) ? $contacto['idPersona'] : '']) );

		if(empty($contacto)) return ['msg' => 'Este correo no esta asociado a ninguna cuenta', 'error' => 1];

		$url = site_url().'login?email=';
		
        $parametros = $mail;
		
        $idUsuario = $user['idUsuario'];
		
        $bandera = 'VALIDAR_EMAIL';
		
        $link_str = implode('|', [$parametros, $idUsuario, $bandera]);
        
        $link = bin2hex( $this->CI->encryption->encrypt($link_str) );

		unset($user['idUsuario']);

		$this->CI->session->set_userdata($user);

		$msg = $this->CI->load->view('email/correo.html', [
				'clasif' => "Portal Ecommerce",
				'title' => "Confirmaci&oacute;n de solicitud",
				'body'  => '<p>Ecommerce recibió una solicitud para ingresar al portal con esta cuenta de correo, para confirmar dicha solicitud haga clic en el siguiente enlace:</p><center><em><a href="'.$url.$link.'">Favor de dar clic en este link.</a></em></center>'
			], TRUE);

		$mail = [
			'from' => 'sender@quehayporaqui.com',
			'subject' => "Confirmaci\xF3n de solicitud",
			'to' => $mail,
			'msg' => $msg
		];
				
		return $this->CI->send_mail($mail);	
	}

}
