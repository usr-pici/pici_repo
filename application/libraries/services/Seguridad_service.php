<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

require_once PATH_INTERFACE . 'Seguridad_interface.php';

/**
 * Description of usuario_service
 *
 * @author Felipe Avila
 */
class Seguridad_Service extends Class_Service implements Seguridad_interface {

    public function __construct() {
        
        parent::__construct();  

        $this->modelToLoad = array(            
            'viewModel' => 'view_model',
         );

        $this->loadModel(); 
        
        $this->CI->load->library('services/usuario_service');
        $this->CI->load->library('services/catalogo_service');      
        
        $this->CI->load->library('user_agent');          
    }

    function search($condicion = array(), $extras = array()) { return; }

    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) { return; }

    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL){ return; }
    
    function delete($id = NULL, $cond = NULL){ return; }

    function searchController($condicion = array(), $extras = array()) {
        
        return $this->CI->cat_controller_model->buscar($condicion, $extras);
    }

    function searchAppRol($condicion = array(), $extras = array()) {
        
        return $this->CI->appRolUsuario_model->buscar($condicion, $extras);
    }

    function searchPasswordReset($condicion = array(), $extras = array()) {
        
        return $this->CI->password_reset_model->buscar($condicion, $extras);
    }

    function searchPrivilegeRol($condicion = array(), $extras = array()) {
        
        return $this->CI->permiso_x_rol_model->buscar($condicion, $extras);
    }

    function searchTokenSesion($condicion = array(), $extras = array()) {
        
        return $this->CI->token_sesion_model->buscar($condicion, $extras);
    }

    function getPrivilege(int $idUser, int $idApp, int $idRol, $idController = NULL, $cvePermiso = NULL, $catalogoSearch = NULL) : array {
                        
        $getPrivilege = $this->indexedSearchByModel('viewModel',
                                                        ['idRol','idController','idPermiso'],
                                                        ['idUsuario' => $idUser, 'idApp' => $idApp, 'idRol' => $idRol, 'idController_IN' => !empty($idController) && gettype($idController) == 'array' ? implode(", ", $idController) : $idController, 'clavePermiso' => $cvePermiso, 'searchCatalog' => !empty($catalogoSearch) ? strtoupper($catalogoSearch) : ''],
                                                        ['imprimirSQL' => 0],
                                                        FALSE,
                                                        'getPrivilege');
        return $getPrivilege;
    }

    function getPrivilegeToCurrentRequest(){

        $user_data = $this->CI->session->userdata();       
        
        $idUsuario = !empty($user_data['idUsuario']) ? $user_data['idUsuario'] : '';
        
        $idApp = !empty($user_data['app']['idApp']) ? $user_data['app']['idApp'] : '';
        
        $idRol = !empty($user_data['rol']['idRol']) ? $user_data['rol']['idRol'] : '';

        $idController = !empty($user_data['idController']) ? $user_data['idController'] : '';
                
        $getPrivilege = $this->getPrivilege($idUsuario, $idApp, $idRol, $idController, NULL, NULL);

        return $getPrivilege;
    }

    function validPrivilege($params) {

        $methods = [];
        $keysPrivilege = $catalogoSearch = '';
        $idApp = current( $this->CI->catalogo_service->search('app', ['clave' => 'APP_WEB', 'activo' => 1, 'borrado' => 0]) );
        $idApp = $idApp ? $idApp['idApp'] : '';
        $controller = current( $this->searchController(['clave' => strtoupper($this->CI->uri->segment(1) != NULL ? $this->CI->uri->segment(1) : 'welcome' ), 'idApp' => $idApp]) );

        foreach($params as $key => $param){
            foreach($param as $value){
                $methods[] = $value;
                
                if( $this->CI->uri->segment(2) == $value ){
                    $keysPrivilege = $key;

                    if( !$this->CI->input->is_ajax_request() && $this->CI->uri->segment(1) == 'catalogo')
                        $catalogoSearch = $this->CI->uri->segment(2);
                }

                //Si el controlador es generico (catalogo) cambiar de segmento al posicion 3
                if( $this->CI->uri->segment(1) == 'catalogo' && $this->CI->uri->segment(3) == $value )
                    $keysPrivilege = $key;
            }
        }
        //$this->CI->imprimir($this->CI->uri->segment(2),1);
        $user_data = $this->CI->session->userdata();

        if( $user_data['isRoot'] == '0'){

            $privilegios = $this->getPrivilege($user_data['idUsuario'], $user_data['app']['idApp'], $user_data['rol']['idRol'], !empty($controller['idController']) ? $controller['idController'] : '', !empty($keysPrivilege) ? $keysPrivilege : 'N/A', $catalogoSearch);
            //$this->CI->imprimir($privilegios,1);
            if( !empty($privilegios)) {

                if( in_array($this->CI->uri->segment(2), $methods) && !empty($privilegios[$user_data['rol']['idRol']][$controller['idController']]))
                    return ['error' => 0, 'msg' => 'Permiso concedido'];
    
            } else {

                if( in_array($this->CI->uri->segment(2), $methods) && !empty($keysPrivilege))
                    return ['error' => -10, 'msg' => 'No se encontraron permisos en el sistema.'];
                else 
                    return ['error' => 0, 'msg' => 'Ok'];
            }

        } else {
            return ['error' => 0, 'msg' => 'Ok'];
        }
        
    }
    
    function saveAppRol(array $reg, int $idReg = NULL) : array {

        $action = empty($idReg) ? 'add' : 'update';

        return $this->action_on_reg($this->CI->appRolUsuario_model, $reg, $action, $action === 'add' ? NULL : "idAppRolUsuario = '{$idReg}'");
    }

    function savePrivilegeRol(array $reg, int $idReg = NULL) : array {

        if( isset($reg['idPermisoRol']) )
            unset($reg['idPermisoRol']);

        $action = empty($idReg) ? 'add' : 'update';

        $result = $this->validar_form($reg, [], $this->CI->permiso_x_rol_model, $action, $action === 'add' ? NULL : "idPermisoRol = '{$idReg}'",['permissionRolExisting']);

        return $result;
    }

    function permissionRolExisting(&$reg) {

        $permiso = $this->searchPrivilegeRol(['idRol' => $reg['idRol'], 'idPermiso' => $reg['idPermiso'], 'idController' => $reg['idController']]);

        if( !empty($permiso) )
		    return $this->CI->msg_error("Permiso por Rol y Controlador existente, verifique.");
    }

    function saveTokenSession(array $reg, int $idReg = NULL) : array {

        $time = time(); 

        $action = empty($idReg) ? 'add' : 'update';

        if( isset($reg['idTokenSesion']) )
            unset($reg['idTokenSesion']);

        $reg['IP'] = !empty($this->CI->input->ip_address()) ? $this->CI->input->ip_address() : '';
        $reg['fecha'] = date("Y-m-d H:i:s", $time);
        $reg['vigencia'] = /*$this->CI->agent->is_browser() ? NULL :*/ date("Y-m-d H:i:s", $time + ( TIME_TOKEN * 60 ));            
        $reg['ultimoMovimiento'] = date("Y-m-d H:i:s", $time);

        return $this->action_on_reg($this->CI->token_sesion_model, $reg, $action, $action === 'add' ? NULL : "idTokenSesion = '{$idReg}'");
    }

    function savePasswordReset(array $reg) : array {

		$this->CI->load->helper('string');

        //date_default_timezone_set('Etc/GMT-6');

		date_default_timezone_set('America/Mexico_City');
        
        $time = time();
        
        $action = 'add';
        
        $reg['token'] = random_string('numeric', 4);
        
        $reg['vigencia'] = date("Y-m-d H:i:s", $time + ( TIME_TOKEN * 60 )); 
                
        //Enviar email
        $msg = $this->CI->load->view('email/correo.html', [
            'clasif' => "Portal Ecommerce",
            'title' => "Recuperaci&oacute;n de contrase&ntilde;a",
            'body'  => '<p>Portal Ecommerce recibió una solicitud para recuperar la contraseña de la cuenta asociada a este correo. Para seguir con el proceso debe introducir el siguiente código:</p>
        <center><em><h3>' . $reg['token'] . '</h3></em></center>
        <small>Este código expira en 10 minutos o deja de ser válido al requerir uno nuevo.</small>'
        ], TRUE);

        $mail = [
			'from' => 'sender@quehayporaqui.com',
			'subject' => "Recuperaci\xF3n de contrase\xF1a",
			'to' => $reg['email'],
			'msg' => $msg
		];

        $response = $this->CI->send_mail($mail);

        if( $response['error'] == 1 )
            return $response;

        $result = $this->validar_form($reg, [], $this->CI->password_reset_model, $action, NULL,['inactiveTokenExistsUser']);   

        return $result;
    }

    function inactiveTokenExistsUser(&$reg) {

        $token = $this->searchPasswordReset(['idUsuario' => $reg['idUsuario'], 'activo' => 1], ['imprimirSQL' => 0]);

        if( !empty($token) )
            $this->CI->password_reset_model->update(['activo' => 0], "idUsuario = '{$reg['idUsuario']}' AND activo = 1");   
    }

    function init_session($idToken = 0, $idRol = 0, $data = []) {
        
        $token = current( $this->searchTokenSesion( ['id' => $idToken] ) );
        
        if ( empty($token) || $this->CI->input->ip_address() !== $token['IP'] )
            $this->CI->msg_error('', 'PARAMETRO');
        
        $rolxappxuser = empty($idRol) ? FALSE : current( $this->searchAppRol(['idUsuario' => $token['idUsuario'], 'activo' => 1, 'borrado' => 0]) );

        if ( empty($rolxappxuser) ) 
            $this->CI->msg_error("No tiene acceso a esta aplicaci\xF3n, verifique.");
    
        if ( empty($data) ) {      

            $data = current( $this->CI->usuario_service->search(['id' => $token['idUsuario'], 'activo' => 1, 'borrado' => 0]) );
           
            unset($data['password']);
           
            $data['app'] = current( $this->CI->catalogo_service->search('app', ['id' => $token['idApp'], 'activo' => 1, 'borrado' => 0]) );
           
            $data['device'] = empty($token['idDispositivo']) ? FALSE : current( $this->CI->catalogo_service->buscar('device', ['id' => $token['dDispositivo'], 'activo' => 1, 'borrado' => 0]) );
        }
        
        $data['persona'] = current( $this->CI->persona_service->search( ['id' => $data['idPersona']] ) );
        
        $telefono = current( $this->CI->persona_service->getContactMean($data['idPersona'], ['CEL']) );
        $data['telefono'] = !empty($telefono['contact'] ) ? current( $telefono['contact'] )['valor'] : NULL;
        
        $correo = current( $this->CI->persona_service->getContactMean($data['idPersona'], ['MAIL']) );
        $data['email'] = !empty($correo['contact']) ? current( $correo['contact'] )['valor'] : NULL;       
        
        $data['rol'] = current( $this->CI->catalogo_service->search('rol', ['id' => $idRol, 'activo' => 1, 'borrado' => 0]) );
        
        $this->CI->session->set_userdata($data);
    }

    function getToken($data, $idRol = 0) {

        $this->CI->load->library('encryption');        
        $time = time();        
        
        $token_reg = [
            'dDispositivo' => empty($data['device']) ? NULL : $data['device']['idDispositivo'],
            'idUsuario' => $data['idUsuario'],
            'idApp' => $data['app']['idApp']
        ];

        $result = $this->saveTokenSession($token_reg);

        $token_str = implode('|', [$result['id'], $this->CI->input->ip_address()]);
        $token = bin2hex( $this->CI->encryption->encrypt($token_str) );

        if ( empty($token) )
            $this->msg_error("Error al generar token de sesi\xF3n, intente de nuevo.");

        if ( $this->CI->agent->is_browser() )
            $this->init_session($result['id'], $idRol, $data);

		$data['token'] = $this->CI->agent->is_browser() ? NULL : $token;

        return ['error' => 0, 'msg' => "Iniciando sesión", 'usuario' => $data];
    }

    function login($cveApp, $user, $pswd, $int = TRUE, $cveDisp = NULL, $tokenDevice = NULL) {
        
        $app = $this->CI->catalogo_service->get_regxclave('app', $cveApp);

        if ( empty($app) )
            $this->CI->msg_error("", 'PARAMETRO');
            
        $contacto = current( $this->CI->persona_service->getContactMean('', [], [], trim($user)) );
        
        $user_bd = empty($contacto) ? FALSE : current( $this->CI->usuario_service->search(['idPersona' => $contacto['idPersona'], 'vigente' => 1], ['imprimirSQL' => 0]) );

        if ( empty($user_bd) ) {
            
            $this->CI->msg_error("Usuario no encontrado, si no ha validado su cuenta, verifique.");
            
        } elseif ( $user_bd['activo'] != 1 ) {
            
            $this->CI->msg_error("Usuario inactivo, consulte al administrador del sistema.");
        
        } elseif ( $int && ! password_verify($pswd, $user_bd['password']) ) {
            
            $this->CI->msg_error("Contrase\xF1a incorrecta, verifique.");
        }
        
        unset($user_bd['password']);

        // Podría agregarse la condición de buscar el rol primario ('primario' => 1) para cuando esté implementado el registro de usuario completo.
        $primaryRol = current( $this->searchAppRol(['idUsuario' => $user_bd['idUsuario'], 'activo' => 1, 'borrado' => 0]) );

        if ( empty($primaryRol) )
            $this->CI->msg_error("No tiene acceso a esta aplicaci\xF3n, verifique.");
        
        if ( ! $this->CI->agent->is_browser() ) {
            
            if ( empty($cveDisp) ) {
                
                $this->CI->msg_error("Es requerido el ID del dispositivo.");
            }
            
            $device = $this->CI->catalogo_service->get_regxclave('device', $cveDisp);
            // Aquí podría agregarse la validación de si existe y está activo, y reportar cuando sea inactivo, terminando el flujo.
            if ( empty($device) ) { 
                
                $result_device = $this->CI->catalogo_service->save_direct(
                    'device', 
                    ['clave' => $cveDisp, 'nombre' => $this->CI->agent->mobile(), 'descripcion' => $this->CI->agent->platform()]
                );
                $device = current( $this->CI->catalogo_service->buscar('device', ['id' => $result_device['id']]) );
            }
            
            $user_bd['device'] = $device;
        }
        
        $user_bd['email'] = $user;        
        $user_bd['app'] = $app;      
        $user_bd['token_device'] = $tokenDevice;      
        $this->CI->session->set_userdata($user_bd);
        $response = $this->getToken($user_bd, $primaryRol['idRol']);

        return $response;
    }

    function saveMenuRolUser(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('appRolMenu', $reg, $id, $cond = NULL);

        return $result;
    }

    function checkToken($email = '', $token = '', $recoverPassword = 0) {
		
        //date_default_timezone_set('Etc/GMT-6');
        
        date_default_timezone_set('America/Mexico_City');
		
        $horaActual = date("H:i");
			
		$userEmail = current( $this->searchPasswordReset(['email' => $email, 'activo' => 1], ['limit' => 1, 'orderBy' => 'idReset DESC']) );

		if(!empty($userEmail)) {

			if( $userEmail['token'] != $token ) return ['msg' => 'Token incorrecto' , 'error' => 1, 'email' => $email];
			
			if( $horaActual >= substr($userEmail['vigencia'], 11, 5) ) return ['msg' => 'Ingrese un nuevo token, este ha expirado' , 'error' => 1, 'email' => $email];

            return ['msg' => 'Token verifado correctamente', 'error' => 0];

		} else {
			return ['msg' => 'Token incorrecto o expirado, intente de nuevo.', 'error' => 1];
		}
	}

    function decipher_bill_email($token) {

		$this->CI->load->library('encryption');

        $key = $this->CI->encryption->decrypt( hex2bin($token) );
        
		if ( ! $key ) {
            
            $this->CI->log(utf8_encode("Token no v\xE1lido: {$token}"));

            $this->CI->json(array('error' => -1, 'msg' => "Token no v\xE1lido."));
        }
        
		$token_data = explode('|', $key);
		
        $reg['activo'] = '1';

		if($token_data[2] == 'VALIDAR_EMAIL'){

            $data = current( $this->CI->usuario_service->search(['id' => $token_data[1], 'activo' => 0, 'borrado' => 0]) );

            unset($data['password']);
			
			if(!empty($data)){
				
                $rolxappxuser = current( $this->searchAppRol(['idUsuario' => $data['idUsuario'], 'activo' => 1]) );
				
                $tokenSesion['idUsuario'] = $data['idUsuario'];
				$tokenSesion['device'] = NULL;
				$tokenSesion['app']['idApp'] = $rolxappxuser['idApp'];
				$tokenSesion['token_device'] = NULL;
				$tokenSesion['idPersona'] = $data['idPersona'];
				
                $getToken = $this->getToken($tokenSesion, $rolxappxuser['idRol']);
				
                $result_update_user = $this->CI->usuario_service->save($reg, $token_data[1]);

                $this->CI->session->set_userdata($data);  

				return array('error' => 0, 'msg' => 'Acceso autorizado', "data"=>['usuario' => $data['tipo'], 'idUsuario' => $data['idUsuario']]);
			
            } else {

                $data = current( $this->CI->usuario_service->search(['id' => $token_data[1], 'activo' => 1, 'borrado' => 0]) );  

                if( $data['activo'] == '1' ) return array('error' => 0, 'msg' => 'Cuenta ya activada', "data"=>['usuario' => $data['tipo'], 'idUsuario' => $data['idUsuario']]);
                
            }

		} else {
			
            $this->CI->json(array('error' => -1, 'msg' => "Acceso no autorizado, consulte con el administrador del sistema.", "data"=>[]));
		}

    }

    function getBranchCompany(){

        $user_data = $this->CI->session->userdata();

        $regs = $this->searchByModel('viewModel', ['isRoot' => '1'], ['imprimirSQL' => 0, 'orderBy' => 'cs.idSucursal ASC'], 'getDataBranch');

        return $regs;
    }


}
