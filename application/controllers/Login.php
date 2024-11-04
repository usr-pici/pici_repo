<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {
    
    public function __construct() {

        parent::__construct();

        $this->load->library('services/seguridad_service');
    }

    public function index() {

        $data['fileToLoad']  = ['login/js/login.js'];
        $data['main_content']  = $this->load->view('login/login.html', [
          
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function validRecaptcha($token = ''){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => GOOGLE_RECAPTCHA_KEY['keySecret'], 'response' => $token)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $datos = json_decode($response, true);

        return $datos;
    }
    
    function in() {

        $app_key = $this->input->post('APP_KEY');
        $login = $this->input->post('usuario');
        $password = $this->input->post('password');
        $idDisp = $this->input->post('device[sistemaOperativo]');
        $tokenDevice = $this->input->post('device[token_device]');
        $token = $this->input->post('token');
        
        $validRecaptcha = $this->validRecaptcha($token);
       
        if ($validRecaptcha['success'] == 1 && $validRecaptcha['score'] >= 0.5) {

            $result = $this->seguridad_service->login($app_key, $login, $password, TRUE, $idDisp, $tokenDevice);
           
            echo json_encode($result);
            
        } else {
            echo json_encode(array('error' => 2, 'msg' => 'Error en el captcha'));
        }
    }
    
    function out() {
        
        $this->session->sess_destroy();

        redirect();
    }


	function authenticateRedesSociales(){

        $app_key = 'APP_WEB';
        $data['usuario']['idUsuario'] = '';
        $reg = $this->input->post('reg');
		
        $contacto = current( $this->persona_service->getContactMean('', [], [], $reg['correo']) );

        $user_bd = current( $this->usuario_service->search(['idPersona' => !empty($contacto) ? $contacto['idPersona'] : '', 'activo' => 1, 'borrado' => 0]) );

        if ( empty($user_bd) ) {
            
			$result = $this->persona_service->saveTMP($reg);

			if(!empty($result)){

				$rol = $this->catalogo_service->get_regxclave('rol', 'USER');
				
                $app = $this->catalogo_service->get_regxclave('app', 'APP_WEB');

				$saveAppRolUser = $this->seguridad_service->saveAppRol(['idRol' => $rol['idRol'], 'idUsuario' => $result['id'], 'idApp' => $app['idApp']]);
			}
        }
                
        echo json_encode( $this->seguridad_service->login($app_key, $reg['correo'], NULL, FALSE, NULL, NULL) );
    }

    function recuperar() {

        if ( LOGGED ) redirect();
		    
        $data['fileToLoad']  = ['login/js/recuperar.js'];
        $data['main_content']  = $this->load->view('login/recuperar.html', [
            'keySiteWeb' => GOOGLE_RECAPTCHA_KEY['keySiteWeb']
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function validar() {

        if ( LOGGED ) redirect();

        $usuario = current( $this->usuario_service->search(['idPersona' => !empty( $this->session->userdata()['idPersona'] ) ? $this->session->userdata()['idPersona'] : '']) );

        $token = current( $this->seguridad_service->searchPasswordReset(['idUsuario' => !empty($usuario['idUsuario']) ? $usuario['idUsuario'] : ''], ['limit' => 1, 'orderBy' => 'idReset DESC']) );
	    
		$fecha = str_replace('-','/',$token['vigencia']);

        $data['fileToLoad']  = ['login/js/recuperar.js','js/clases/Temporizador.js'];
        $data['main_content']  = $this->load->view('login/codigo.html', [
            "idPersona" => !empty($this->session->userdata()['idPersona']) ? $this->session->userdata()['idPersona'] : '',
			"email" => !empty($this->session->userdata()['email']) ? $this->session->userdata()['email'] : '',
            "fecha" => str_replace('-','/',$token['vigencia']),
            'keySiteWeb' => GOOGLE_RECAPTCHA_KEY['keySiteWeb']
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function reset() {

        if ( LOGGED ) redirect('/');
                     
        $user_bd = current( $this->usuario_service->search(['idPersona' => !empty( $this->session->userdata()['idPersona'] ) ? $this->session->userdata()['idPersona'] : '']) );

        $data['fileToLoad']  = ['login/js/recuperar.js'];
        $data['main_content']  = $this->load->view('login/reset.html', [
            "idUsuario" => !empty($user_bd) ? $user_bd['idUsuario'] : '',
            'keySiteWeb' => GOOGLE_RECAPTCHA_KEY['keySiteWeb']
        ], TRUE);
               
        $this->loadTemplate($data);
    }

}
