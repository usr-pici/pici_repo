<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
    
    public function __construct() {

        parent::__construct();  

        $this->load->library('services/seguridad_service');
        
        $this->methodByPrivilege = [
            'READ' => ['profile', 'billing', 'registerRFC', 'editRFC', 'get_regs_rfc', 'getRFCData'],
            'ADD' => ['saveRFC'],
            'EDIT' => ['update', 'updateRFC', 'savePredefinedRFC'],
            'DELETE' => ['deleteRFC']            
        ];
        
        $this->validar_acceso(['register','save','update','recoverPasswordToken','checkTokenUser','getTokenUpd', 'notified', 'resendEmailUser']);
    }
    
    function index() {

        //Buscar una persona pors su id e indexarla por su idx
        //$persona = current( $this->persona_service->search(['idPersona' => 1, 'borrado' => 0]) );
        //$personaIndexed = $this->persona_service->indexed_search('idx',['idPersona' => 1, 'borrado' => 0]);

        //Metodo para buscar los datos de una persona utilizando view_model
        $personaModel = $this->persona_service->searchByModel(
            'viewModel',
            ['idPersona' => 1],
            ['imprimirSQL' => 0],
            'personContact'
        );

        $personaIndexedModel = $this->persona_service->indexedSearchByModel(
            'viewModel',
            'clave',
            ['idPersona' => 1],
            ['imprimirSQL' => 0],
            FALSE,
            'personContact'
        );

        //Metodo para buscar una persona con sus medios de contacto (email, telefono, etc)
        //$contacto = $this->persona_service->getContactMean(1, ['MAIL','CEL','TEL'],[2,3,1]);

        $result = [//'search' => $persona, 
                   //'indexed_search' => $personaIndexed,
                   'searchByModel' => $personaModel,
                   'indexedSearchByModel' => $personaIndexedModel,
                   //'getContactMean' => $contacto,
                ];

        echo json_encode($result);
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

    function calculateAge($fecha_nac = '') {
        
        $fechaNacimiento = new DateTime($fecha_nac);
        
        $ahora = new DateTime(date("Y-m-d"));
        
        $diferencia = $ahora->diff($fechaNacimiento);
        
        return $diferencia->format("%y");
    }

    function save() {

        if(!empty($this->input->post('reg')))
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $validRecaptcha = $this->validRecaptcha($reg['token']);
       
        if ($validRecaptcha['success'] == 1 && $validRecaptcha['score'] >= 0.5) {
             
            //Validar la edad del usuario por la fecha de nacimiento
            if( isset($reg['persona']['fechaNacimiento']) ){
                $edad = $this->calculateAge($this->formato_fecha_bd($reg['persona']['fechaNacimiento']));		

                if($edad < 18)
                    return $this->msg_error("Debes ser mayor de 18 a\xF1os para registrarte, verifique.");
                else
                    $reg['persona']['fechaNacimiento'] = $this->formato_fecha_bd($reg['persona']['fechaNacimiento']);
            }

            //Validar email
            $contacto = current( $this->persona_service->getContactMean('', [], [], $reg['medioContactoMail']['email']) );
            $user_bd = current( $this->usuario_service->search(['idPersona' => !empty($contacto) ? $contacto['idPersona'] : '']) );
            
            if( $user_bd )
                $this->msg_error('Correo ya registrado, verifique.');
            
            if( empty($reg['idPersona']) ){
                $idx = $this->utileria_service->getIdx('persona');
                $reg['persona']['idx'] = $idx['id'];
            }

            $result = $this->persona_service->save($reg['persona'], !empty($reg['idPersona']) ? $reg['idPersona'] : '','persona');
            $idPersona = $result['id'];
                    
            $reg['medioContactoMail']['valor'] = $reg['medioContactoMail']['email'];
            $resultEmail = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoMail'], !empty($reg['medioContactoMail']['idContacto']) ? $reg['medioContactoMail']['idContacto'] : '', 'medioContactoMail', NULL);

            if( isset($reg['medioContactoCel']['celular']) ){
                $reg['medioContactoCel']['valor'] = $reg['medioContactoCel']['celular'];
                $resultCelular = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoCel'], !empty($reg['medioContactoCel']['idContacto']) ? $reg['medioContactoCel']['idContacto'] : '', 'medioContactoCel', NULL);
            }

            if( isset($reg['medioContactoTel']['telefono']) ){
                $reg['medioContactoTel']['valor'] = $reg['medioContactoTel']['telefono'];
                $resultTelefono = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoTel'], !empty($reg['medioContactoTel']['idContacto']) ? $reg['medioContactoTel']['idContacto'] : '', 'medioContactoTel', NULL);
            }

            if ( $result['error'] === 0 ) {
                
                $password = !empty($reg['usuario']['password']) ? $reg['usuario']['password'] : NULL;
                $resultUsuario = $this->usuario_service->save(['idPersona' => $result['id'], 'password' => password_hash($password, PASSWORD_DEFAULT), 'tipo' => 'APP_WEB', 'activo' => '0']);

                if(!empty($resultUsuario)){
                    $rol = $this->catalogo_service->get_regxclave('rol', 'USER');
                    $app = $this->catalogo_service->get_regxclave('app', 'APP_WEB');
                    $this->seguridad_service->saveAppRol(['idUsuario' => $resultUsuario['id'], 'idApp' => $app['idApp'], 'idRol' => $rol['idRol']], NULL);	

                    //Enviar email para activar cuenta
                    $resultActiveEmail = $this->usuario_service->sendLinkValidMail($reg['medioContactoMail']['valor']);
	
					if( $resultActiveEmail['error'] == '1' ){
						$result = ['error'=> 0, 'msg'=> $resultActiveEmail['msg'], 'data' => ['idPersona' => $idPersona, 'bandera' => 'validar', 'envio' => '0'] ];
						return $this->json( $result );
					}
                }
            }

            $result['data'] = ['idPersona' => $idPersona, 'bandera' => 'validar', 'envio' => '1']; 
			            
            echo json_encode($result);

        } else {
            echo json_encode(array('error' => 1, 'msg' => 'Error en el captcha'));
        }
       
    }

    function update() {

        $user_bd = [];

        if(!empty($this->input->post('reg')))
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $validRecaptcha = $this->validRecaptcha($reg['token']);

        if ($validRecaptcha['success'] == 1 && $validRecaptcha['score'] >= 0.5) {

            //Validar la edad del usuario por la fecha de nacimiento
            if( isset($reg['persona']['fechaNacimiento']) ){
                $edad = $this->calculateAge($this->formato_fecha_bd($reg['persona']['fechaNacimiento']));		

                if($edad < 18)
                    return $this->msg_error("Debes ser mayor de 18 a\xF1os para registrarte, verifique.");
                else
                    $reg['persona']['fechaNacimiento'] = $this->formato_fecha_bd($reg['persona']['fechaNacimiento']);
            }

            if( !empty($reg['idPersona']) )
                $user_bd = current( $this->usuario_service->search(['idPersona' => $reg['idPersona']]) );
        
            if( !empty($reg['usuario']['idUsuario']) )
                $user_bd = current( $this->usuario_service->search(['idUsuario' => $reg['usuario']['idUsuario']]) );
       
            if(!empty($reg['usuario'])){

                if( isset($reg['usuario']['passwordActual']) )
                    $datos['passwordActual'] = $reg['usuario']['passwordActual'];

                if( isset($reg['usuario']['password']) )
                    $datos['password'] = password_hash($reg['usuario']['password'], PASSWORD_DEFAULT);

                if(!empty($datos)){
            
                    if( isset( $reg['usuario']['passwordActual'] ) ){
                        if ( ! password_verify($reg['usuario']['passwordActual'], $user_bd['password']) )
                            return $this->msg_error("Tu Contrase\xF1a actual es incorrecta, verifique.");
                    }
        
                    if( isset($reg['usuario']['password']) ){
                        
                        if( isset($datos['passwordActual']) )
                            unset($datos['passwordActual']);
                            
                        $result = $this->usuario_service->save($datos, $user_bd['idUsuario']);
                    }  
                }
            }  

            if( !empty($reg['persona']) )
                $result = $this->persona_service->save($reg['persona'], !empty($reg['idPersona']) ? $reg['idPersona'] : '','persona');
            
            if( isset($reg['medioContactoMail']['email']) ){
                $reg['medioContactoMail']['valor'] = $reg['medioContactoMail']['email'];
                $resultEmail = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoMail'], !empty($reg['medioContactoMail']['idContacto']) ? $reg['medioContactoMail']['idContacto'] : '', 'medioContactoMail', NULL);
            }
        
            if( isset($reg['medioContactoCel']['celular']) ){
                $reg['medioContactoCel']['valor'] = $reg['medioContactoCel']['celular'];
                $resultCelular = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoCel'], !empty($reg['medioContactoCel']['idContacto']) ? $reg['medioContactoCel']['idContacto'] : '', 'medioContactoCel', NULL);
            }

            if( isset($reg['medioContactoTel']['telefono']) ){
                $reg['medioContactoTel']['valor'] = $reg['medioContactoTel']['telefono'];
                $resultTelefono = $this->persona_service->saveContactMean($result['id'], $reg['medioContactoTel'], !empty($reg['medioContactoTel']['idContacto']) ? $reg['medioContactoTel']['idContacto'] : '', 'medioContactoTel', NULL);
            }

            if(!empty($_FILES)){

                $user_data = $this->session->userdata();

                $clasifPhoto = current( $this->catalogo_service->search('clasificacion', ['clave' => 'PHOTO_PROFILE']) )['idClasificacion'];

                foreach ($_FILES as $fileName => $file) {
                    $archivo = $this->file_service->save(['idx' => $user_data['persona']['idx'], 'idClasificacion' => $clasifPhoto], NULL, NULL, NULL, $fileName);
                }
            }
		
		    echo json_encode($result);
        } else {
            echo json_encode(array('error' => 1, 'msg' => 'Error en el captcha'));
        }
    }

    function delete() {

        $reg = ['borrado' => 1];

        $result = $this->persona_service->delete(NULL,'idPersona IN (1)', $reg);

        echo json_encode($result);        
    }

    function deleteContactMean() {

        $result = $this->persona_service->deleteContactMean(1, 3, NULL);

        echo json_encode($result);        
    }

	function savePhoto() {

		$result = ['error' => 0, 'msg' => 'Test savePhoto Controlador Usuario'];
		
		echo json_encode($result);
	}

    function savePasswordReset() {

        if( !empty($this->input->post('reg')) )
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $reg = ['idUsuario' => 1, 'email' => 'ing.nestorricardo@gmail.com'];

        $result = $this->seguridad_service->savePasswordReset($reg);
        
		echo json_encode($result);
    }

    function savePrivilegeRol() {
            
        if( !empty($this->input->post('reg')) )
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $reg = ['idPermisoRol' => '', 'idRol' => 1, 'idPermiso' => 1, 'idController' => 1];

        $result = $this->seguridad_service->savePrivilegeRol($reg, !empty($reg['idPermisoRol']) ? $reg['idPermisoRol'] : NULL);

        echo json_encode($result);
    }

    function saveTokenSession() {

        if( !empty($this->input->post('reg')) )
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $reg = [
            'idTokenSesion' => 1,
            'dDispositivo' => NULL,
            'idUsuario' => 1
        ];

        $result = $this->seguridad_service->saveTokenSession($reg, !empty($reg['idTokenSesion']) ? $reg['idTokenSesion'] : NULL);

        echo json_encode($result);

    }

    function saveMenuRolUser() {

        if( !empty($this->input->post('reg')) )
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $reg = [
            'idUsuario' => 1,
            'idApp' => 1,
            'idRol' => 1,
            'idMenu' => 1
        ];

        $result = $this->seguridad_service->saveMenuRolUser($reg, !empty($reg['idTokenSesion']) ? $reg['idTokenSesion'] : NULL);

        echo json_encode($result);
    }

    function register() {

        if ( LOGGED ) redirect();

        $cat_gradoestudios = $this->catalogo_service->search('cat_gradoestudios', ['activo' => 1, 'borrado' => 0]);

        $cat_ocupacion = $this->catalogo_service->search('cat_ocupacion', ['activo' => 1, 'borrado' => 0]);
		    
        $data['fileToLoad']  = ['user/js/register.js'];
        $data['main_content']  = $this->load->view('user/register.html', [
            'keySiteWeb' => GOOGLE_RECAPTCHA_KEY['keySiteWeb'],
            'cat_gradoestudio' => $this->catalogo_service->get_list_to_select([
                'regs' => $cat_gradoestudios,
                'index_id' => 'idGradoEstudio',
                'index_desc' => 'nombre',
                'etiqueta' => '-Elegir-'
            ]),
            'cat_ocupacion' => $this->catalogo_service->get_list_to_select([
                'regs' => $cat_ocupacion,
                'index_id' => 'idOcupacion',
                'index_desc' => 'nombre',
                'etiqueta' => '-Elegir-'
            ]),
        ], TRUE);
      
        $this->loadTemplate($data);
    }

    function myAccount() {

        $user_data = $this->session->userdata();

        $persona = $this->persona_service->getContactMean($user_data['idPersona'],['MAIL', 'CEL', 'TEL']);
        $contacto = $this->persona_service->indexedSearchByModel('viewModel', 'clave', ['idPersona' => $user_data['idPersona']], ['imprimirSQL' => 0], FALSE, 'personContact');
		    
        $data['fileToLoad']  = ['user/js/myAccount.js'];
        $data['main_content']  = $this->load->view('user/my-account.html', [
            'persona' => $persona['persona'],
            'email' => $contacto['MAIL']['valor'],
            'idContactoEmail' => $contacto['MAIL']['idContacto'],
            'celular' => $contacto['CEL']['valor'],
            'idContactoCelular' => $contacto['CEL']['idContacto']
        ], TRUE);
      
        $this->loadTemplate($data);
    }

    function profile() {

        $bandera = 0;

        $cat_gradoestudios = $this->catalogo_service->search('cat_gradoestudios');

        $cat_ocupacion = $this->catalogo_service->search('cat_ocupacion');

        $user_data = $this->session->userdata();

        $idx = current( $this->persona_service->search(['idPersona' => $user_data['idPersona']]) );

        $persona = $this->persona_service->getContactMean($user_data['idPersona'],['MAIL', 'CEL', 'TEL']);
        
        $contacto = $this->persona_service->indexedSearchByModel('viewModel', 'clave', ['idPersona' => $user_data['idPersona']], ['imprimirSQL' => 0], FALSE, 'personContact');

        $file = $this->file_service->get_profile_photo($idx['idx']);

        if( ($file['file'] == URL_VIEWS.'images/profile.png') && !empty($user_data['URL']) ){
			$file['file'] = $user_data['URL'];
            $bandera = 1;
        }
        
        if( ($file['file'] != URL_VIEWS.'images/profile.png') && empty($user_data['URL']) ) $bandera = 1;
   
        $data['fileToLoad']  = ['user/js/profile.js'];
        $data['main_content']  = $this->load->view('user/profile.html', [
            'persona' => $persona['persona'],
            'email' => $contacto['MAIL'],
            'idContactoEmail' => $contacto['MAIL']['idContacto'],
            'celular' => !empty( $contacto['CEL'] ) ?  $contacto['CEL'] : [],
            'fechaNac' => !empty($persona) ? $this->formato_fecha_pantalla($persona['persona']['fechaNacimiento'],10) : '',
            'idContactoCelular' => !empty($contacto['CEL']) ?  $contacto['CEL']['idContacto'] : '',
            'file' => $file,
            'bandera' => $bandera,
            'tipo' => !empty($user_data['tipo']) ? $user_data['tipo'] : '',
            'keySiteWeb' => GOOGLE_RECAPTCHA_KEY['keySiteWeb'],
            'cat_gradoestudio' => $this->catalogo_service->get_list_to_select(
				array(
				'index_id' => 'idGradoEstudio',
				'index_desc' => 'nombre',
				'id_reg' => !empty($persona['persona']['idGradoEstudio']) ? $persona['persona']['idGradoEstudio'] : '',
				'regs' => $cat_gradoestudios           
			)),
            'cat_ocupacion' => $this->catalogo_service->get_list_to_select(
				array(
				'index_id' => 'idOcupacion',
				'index_desc' => 'nombre',
				'id_reg' => !empty($persona['persona']['idOcupacion']) ? $persona['persona']['idOcupacion'] : '',
				'regs' => $cat_ocupacion           
			)),
        ], TRUE);
      
        $this->loadTemplate($data);
    }

    function recoverPasswordToken() {
	
        $email = $this->input->post('email');

        $token = $this->input->post('token');

        $validRecaptcha = $this->validRecaptcha($token);

        if ($validRecaptcha['success'] == 1 && $validRecaptcha['score'] >= 0.5) {

            $contacto = current( $this->persona_service->getContactMean('', [], [], $email) );
        
            $user_bd = current( $this->usuario_service->search(['idPersona' => !empty($contacto) ? $contacto['idPersona'] : '']) );

            if(!empty($user_bd)){

                if( $user_bd['tipo'] !== 'FACEBOOK' && $user_bd['tipo'] !== 'GOOGLE' && $user_bd['tipo'] == 'APP_WEB'){

                    //Validar que el token actual este vigente
                    date_default_timezone_set('America/Mexico_City');
                    $horaActual = date("H:i");
                    $fechaActual = date("Y-m-d");
                    $fecha = $fechaActual . ' ' . $horaActual;
                    $token = current( $this->seguridad_service->searchPasswordReset(['email' => $email, 'vigenciaToken' => $fecha, 'activo' => 1], ['imprimirSQL' => 0, 'limit' => 1, 'orderBy' => 'idReset DESC']) );

                    $hora1 = strtotime($horaActual);

                    if( !empty($token['vigencia']) )
                        $hora2 = strtotime(substr($token['vigencia'], 11, 5));
                    else 
                        $hora2 = strtotime($horaActual);

                    if( $hora1 < $hora2 ) {
                        echo json_encode(['error' => 0, 'msg' => 'Tienes un codigo vigente']);
                    } else {

                        $reg = ['idUsuario' => $user_bd['idUsuario'], 'email' => $email];
        
                        $result = $this->seguridad_service->savePasswordReset($reg);
                        
                        $dataSession['idPersona'] = $user_bd['idPersona'];
        
                        $dataSession['email'] = $email;
        
                        $this->session->set_userdata($dataSession);

                        if( $result['error'] == 0 )
                            $result['msg'] = 'En breve se enviará un correo electrónico con el código de verificación';
        
                        echo json_encode($result);
                    }


                } else if($user_bd['tipo'] == 'FACEBOOK' || $user_bd['tipo'] == 'GOOGLE'){
                    return $this->msg_error("Esta cuenta esta asociado a otro tipo de usuario.");
                } 
                
            } else {
                $this->msg_error("Medio de contacto no encontrado, verifique.");
            }	

        } else {
            echo json_encode(array('error' => 1, 'msg' => 'Error en el captcha'));
        }
	}

    function checkTokenUser() {
		
        $email = $this->input->post('email');
		
        $token = $this->input->post('token');
		
        $recoverPassword = $this->input->post('recoverPassword');

        $tokenRecaptcha = $this->input->post('tokenRecaptcha');

        $validRecaptcha = $this->validRecaptcha($tokenRecaptcha);

        if ($validRecaptcha['success'] == 1 && $validRecaptcha['score'] >= 0.5) {

            $result = $this->seguridad_service->checkToken($email, $token, $recoverPassword);
            
            echo json_encode($result);

        } else {
            echo json_encode(array('error' => 1, 'msg' => 'Error en el captcha'));
        }	
	}

    function notified($idPersona = '', $bandera = '', $envio = '') {

        $contacto = current( $this->persona_service->getContactMean($idPersona,['MAIL']) )['contact'];

        $data['fileToLoad']  = ['user/js/register.js'];
        $data['main_content']  = $this->load->view('user/notified.html', [
            'email' => current( $contacto )['valor'],
			'bandera' => $bandera,
			'envio' => $envio
        ], TRUE);
        
        $this->loadTemplate($data);
	}

    function resendEmailUser(){

		$user_data = $this->session->userdata();
        
        $email = $this->input->post('email');
        
        $bandera = $this->input->post('bandera');
	
		$result = $this->usuario_service->sendLinkValidMail($email);

		echo json_encode($result);
	}

    function billing(){

        $data['fileToLoad']  = ['billing/js/billing.js'];
        $data['main_content']  = $this->load->view('billing/billing.html', [], TRUE);
        
        $this->loadTemplate($data);
    }

    function registerRFC($context = 0){

        $cat_regimen_fiscal = $this->catalogo_service->search('cat_regimen_fiscal',['activo' => 1, 'borrado' => 0]);

        $data['fileToLoad']  = ['billing/js/insert.js'];
        $data['main_content']  = $this->load->view('billing/insert.html', [
            'context' => $context,
            'cat_regimen_fiscal' => $this->catalogo_service->get_list_to_select(
				array(
				'index_id' => 'idRegimenFiscal',
				'index_desc' => 'nombre',
				'id_reg' => '',
				'regs' => $cat_regimen_fiscal           
			)),
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function get_regs_rfc() {

		$user_data = $this->session->userdata();

        $regs = $this->persona_service->searchByModel('viewModel', ['idUsuario' => $user_data['idUsuario']], ['imprimirSQL' => 0, 'orderBy' => 'r.idRFC ASC'], 'getRFC');
        
        if ( $regs ) {
						
            foreach ($regs as &$reg) {

                $predef_address = $reg['predefinido'] === '1' ? 'checked' : '';

	            $reg['rfc'] = "{$reg['claveRF']} - {$reg['razonSocial']} {$reg['rfc']}";
                $reg['predefinido'] = '<div class="form-check text-center">
											<input type="radio" onclick="savePredefinedRFC(' . $reg['idRFC'] . ')" value="1" ' . $predef_address . '>
										</div>';
                
				$reg['opciones'] = '<a class="validEdit" href="' . URL_SITE . 'user/editRFC/' . $reg['idRFC'] . '" title="Editar"><i class="fa fa-edit text-primary"></i></a>';
				$reg['opciones'] .= ' <span class="validDelete">|</span> <a class="validDelete" href="javascript:void(0);" class="borrar-registro" title="Eliminar" onclick="deleteRFC(this,' . $reg['idRFC'] . ')"><i class="fa fa-trash-alt" style="color:red;"></i></a>';
            }
        }
        
        echo json_encode($regs);
    }

    function saveRFC() {

        if(!empty($this->input->post('reg')))
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $user_data = $this->session->userdata();

        //Validar que el RFC de usuario no sxista
        $validRfc = current( $this->persona_service->searchByModel('viewModel', ['rfc' => $reg['rfc'], 'idUsuario' => $user_data['idUsuario']], ['imprimirSQL' => 0, 'limit' => 1], 'getRFC') );
        
        if( !empty($validRfc) )
            return $this->msg_error("RFC ya registrado, verifique.");

        $reg['idUsuario'] = $user_data['idUsuario'];

        if( !empty($reg['predefinido']) ) {
            
			$predef_rfc = $this->persona_service->checkSavePredefinedRFC();

			if( $predef_rfc ) $this->persona_service->saveRFC(['predefinido' => 0], $predef_rfc['idRFC']);
		}

        $result = $this->persona_service->saveRFC($reg, NULL);

        echo json_encode($result);
    }

    function editRFC($idRFC = 0){

        $reg = current( $this->persona_service->searchByModel('viewModel', ['idRFC' => $idRFC], ['imprimirSQL' => 0, 'orderBy' => 'r.idRFC ASC'], 'getRFC') );

        $cat_regimen_fiscal = $this->catalogo_service->search('cat_regimen_fiscal',['activo' => 1, 'borrado' => 0]);

        $data['fileToLoad']  = ['billing/js/edit.js'];
        $data['main_content']  = $this->load->view('billing/edit.html', [
            'reg' => $reg,
            'cat_regimen_fiscal' => $this->catalogo_service->get_list_to_select(
				array(
				'index_id' => 'idRegimenFiscal',
				'index_desc' => 'nombre',
				'id_reg' => $reg['idRegimenFiscal'],
				'regs' => $cat_regimen_fiscal           
			)),
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function savePredefinedRFC() {

		if(!empty($this->input->post('reg'))) 
			$reg = $this->input->post('reg');
		else {
			$reg = $this->input->post();
			$_POST['reg'] = $reg;
		}

		$result = $this->persona_service->savePredefinedRFC($reg['idRFC']);
		
		echo json_encode($result);
    }

    function deleteRFC($idRFC = 0) {

        $reg = ['borrado' => 1];

        $result = $this->persona_service->deleteRFC($idRFC, NULL, $reg);

        echo json_encode($result);        
    }

    function updateRFC() {

        if(!empty($this->input->post('reg')))
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $user_data = $this->session->userdata();
        //Validar que el RFC de usuario no exista
        $validRfc = current( $this->persona_service->searchByModel('viewModel', ['idUsuario' => $user_data['idUsuario'], 'idRFC' => $reg['idRFC']], ['imprimirSQL' => 0, 'limit' => 1], 'getRFC') );
        
        if( $reg['rfc'] != $validRfc['rfc'] )
            return $this->msg_error("RFC ya registrado, verifique.");

        if( !empty($reg['predefinido']) ) {
            
			$predef_rfc = $this->persona_service->checkSavePredefinedRFC();

			if( $predef_rfc ) $this->persona_service->saveRFC(['predefinido' => 0], $predef_rfc['idRFC']);
		} else {
            $reg['predefinido'] = '0';
        }

        $result = $this->persona_service->saveRFC($reg, $reg['idRFC']);

        echo json_encode($result);
    }	

    function getRFCData() {

		$idRFC = $this->input->post('idRFC');

		$result = current( $this->catalogo_service->search('rfc', ['id' => $idRFC]) );

		echo json_encode($result);
	}

    function updateDataUser() {

		if(!empty($this->input->post('reg'))) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}
		
        if( empty($reg['nombre']) || empty($reg['apellidos']) || empty($reg['valor']) )
            return $this->msg_error("Todos los campos son obligatorios, verifique.");
		
        $user_data = $this->session->userdata();
                
        $result = $this->persona_service->save(['nombre' => $reg['nombre'], 'apellidos' => $reg['apellidos']], !empty($user_data['idPersona']) ? $user_data['idPersona'] : '', 'persona');

        $resultCelular = $this->persona_service->saveContactMean($user_data['idPersona'], ['claveMedioContacto' => $reg['claveMedioContacto'], 'etiqueta' => $reg['etiqueta'], 'prioridad' => $reg['prioridad'], 'valor' => $reg['valor']], !empty($reg['idContacto']) ? $reg['idContacto'] : '', 'medioContactoCel', NULL);
        
        $data['persona'] = current( $this->persona_service->search( ['id' => $user_data['idPersona']] ) );
        
        $telefono = current( $this->persona_service->getContactMean($user_data['idPersona'], ['CEL']) );
        
        $data['telefono'] = !empty($telefono['contact'] ) ? current( $telefono['contact'] )['valor'] : NULL;
        
        $this->session->set_userdata($data);

		echo json_encode($resultCelular);
	}

    function getInfoContactUser() {

        $user_data = $this->session->userdata();

        $person = $this->persona_service->getContactMean($user_data['idPersona'], ['CEL'])['persona'];

        $idPedido = current( $this->pedido_service->returnIdsOrder() );

        $order = current( $this->pedido_service->search(['id' => $idPedido]) );

        //Validar medio de contacto cel y nombre
		if( !empty( $person['contact'] ) && !empty($person['nombre']) && !empty($person['apellidos']) && !empty($order['requiereFactura']) )
			echo json_encode(['error' => 0, 'msg' => 'Informacion encontrada']);
		else if( !empty( $person['contact'] ) && empty($person['nombre']) && !empty($person['apellidos']) && !empty($order['requiereFactura']) )
			echo json_encode(['error' => 1, 'msg' => 'NO SE TIENE REGISTRADO EL NOMBRE EN LA CONFIRMACIÓN DE DATOS, VERIFIQUE Y ACTUALICE.']);
        else if( !empty( $person['contact'] ) && !empty($person['nombre']) && empty($person['apellidos']) && !empty($order['requiereFactura']) )
			echo json_encode(['error' => 1, 'msg' => 'NO SE TIENE REGISTRADO LOS APELLIDOS EN LA CONFIRMACIÓN DE DATOS, VERIFIQUE Y ACTUALICE.']);
		else if( empty( $person['contact'] ) && !empty($person['nombre']) && !empty($person['apellidos']) && !empty($order['requiereFactura']))
			echo json_encode(['error' => 1, 'msg' => 'NO SE TIENE REGISTRADO EL CELULAR EN LA CONFIRMACIÓN DE DATOS, VERIFIQUE Y ACTUALICE.']);
		else if( !empty( $person['contact'] ) && !empty($person['nombre']) && !empty($person['apellidos']) && empty($order['requiereFactura']) )
			echo json_encode(['error' => 1, 'msg' => 'NO SE TIENE REGISTRADO LA CONFIRMACIÓN REQUIERE FACTURA, VERIFIQUE Y ACTUALICE.']);
		else if( empty( $person['contact'] ) && empty($person['nombre']) && empty($person['apellidos']) && empty($order['requiereFactura']) )
			echo json_encode(['error' => 1, 'msg' => 'NO SE TIENE REGISTRADO SI REQUIERE FACTURA, EL NOMBRE Y EL CELULAR EN LA CONFIRMACIÓN DE DATOS, VERIFIQUE Y ACTUALICE.']);
    }
}




