<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/twilio/autoload.php'; 
 
use Twilio\Rest\Client; 
use Twilio\TwiML\MessagingResponse;

//header('Access-Control-Allow-Origin: *');

class Service extends MY_Controller {
    
    public function __construct() {

        parent::__construct();

       $this->load->library('services/file_service');
	   $this->load->library('services/mail_service');
    }
    
    function testAttrProdDetail($idProd) {
        
        $this->load->library('services/producto_service');        
        
        $result = $this->producto_service->getAttrToDetail($idProd, [1]);    // [11]        
        $this->imprimir($result);
        
//        $data['main_content'] = $result[11]['atributoHTML']['COLOR'];
//        
//        $this->loadTemplate($data);
    }
    
    function testAttrProd() {
        
        $this->load->library('services/producto_service');        
        
        $result = $this->producto_service->getAttrData([], [], []);    // [11]        
        $this->imprimir($result);
        
        $data['main_content'] = $result[11]['atributoHTML']['COLOR'];
        
        $this->loadTemplate($data);
    }

    function downloadInlineFile($idDocumento = 0) {
		$this->file_service->download($idDocumento, TRUE);
	}

    function downloadEcomm($idDocumento = 0) {
		$this->file_service->downloadEcomm($idDocumento, TRUE);
	}

    function downloadFromAdmin($idDocumento = 0) {
		$this->file_service->download($idDocumento, TRUE);
        redirect(URL_PORTAL_ADMINISTRACION.'service/downloadInlineFile/'.$idDocumento);
	}

    function downloadInlineFile2($idDocumento = 0) {
		$this->file_service->downloadTest($idDocumento, TRUE);
	}

    function downloadTest($idDocumento = 0) {
		$this->file_service->downloadTest($idDocumento, TRUE);
	}

    function downloadFromEcomm($idDocumento = 0) {
		$this->file_service->downloadTest($idDocumento, TRUE);
        redirect(URL_PORTAL_ECOMMERCE.'service/downloadInlineFile2/'.$idDocumento);
	}

    function downloadFile($idDocumento = '') {
        redirect(URL_PORTAL_ADMINISTRACION.'service/downloadFile/'.$idDocumento);
	}

    function cat_estado(){

        $reg = $this->input->post();
                
        $getEstados = $this->file_service->searchByModel('viewModel', ['cp' => $reg['cp']], ['imprimirSQL' => 0, 'orderBy' => 'ce.nombre ASC'], 'getEstados');
        
        $cat_estado = $this->catalogo_service->get_list_to_select(
            [
                'index_id' => 'idEstado',
                'index_desc' => 'estado',
                'id_reg' => '',
                'regs' => $getEstados,
                'etiqueta' => '-Elegir-',
                'con_etiqueta' => count($getEstados) > 1
            ]
        );

        echo $cat_estado;
    }

    function cat_municipio(){

        $reg = $this->input->post();

        $getMunicipios = $this->file_service->searchByModel('viewModel', ['cp' => $reg['cp']], ['imprimirSQL' => 0, 'orderBy' => 'cm.nombre ASC'], 'getMunicipios');
        
        $cat_municipios = $this->file_service->get_list_to_select(
            [
                'index_id' => 'idMunicipio',
                'index_desc' => 'municipio',
                'id_reg' => '',
                'regs' => $getMunicipios,
                'etiqueta' => '-Elegir-',
                'con_etiqueta' => count($getMunicipios) > 1
            ]
        );

        echo $cat_municipios;
    }

    function cat_localidad(){

        $reg = $this->input->post();

        $getLocalidades = $this->file_service->searchByModel('viewModel', ['cp' => $reg['cp']], ['imprimirSQL' => 0, 'orderBy' => 'cl.nombre ASC'], 'getLocalidades');
        
        $cat_localidades = $this->file_service->get_list_to_select(
            [
                'index_id' => 'idLocalidad',
                'index_desc' => 'localidad',
                'id_reg' => !empty($reg['idLocalidad']) ? $reg['idLocalidad'] : '',
                'regs' => $getLocalidades,
                'etiqueta' => '-Elegir-',
                'con_etiqueta' => count($getLocalidades) > 1
            ]
        );

        echo $cat_localidades;
    }

    function cat_rol(){

        $reg = $this->input->post();

        $getRoles = $this->file_service->searchByModel('viewModel', ['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0, 'orderBy' => 'nombre ASC'], 'cat_rol');
        
        $cat_rol = $this->file_service->get_list_to_select(
            [
                'index_id' => 'idRol',
                'id_reg' => '',
                'index_desc' => 'nombre',
                'regs' => $getRoles,
                'con_etiqueta' => false,
            ]
        );

        echo $cat_rol;
    }

    function cat_branch(){

        $reg = $this->input->post();

        $getBranch = $this->file_service->searchByModel('viewModel', ['idEmpresa' => $reg['idEmpresa'], 'activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0, 'orderBy' => 'nombre ASC'], 'cat_sucursal');
        
        $cat_rol = $this->file_service->get_list_to_select(
            [
                'index_id' => 'idSucursal',
                'id_reg' => '',
                'index_desc' => 'nombre',
                'regs' => $getBranch,
                'con_etiqueta' => false,
            ]
        );

        echo $cat_rol;
    }

    function testConfirmationOrder($idPedido = '') {

        $result = $this->pedido_service->generatePaymentConfirmation($idPedido);

        $this->imprimir($result,1);
    }

    function testMailOrder($idOrder = 0) {

        $result = $this->mail_service->sendMailOrder($idOrder);

        $this->imprimir($result,1);
    }

    function testMailInvoice($idOrder = 0) {
        
        $result = $this->mail_service->invoice($idOrder);
        
        $this->imprimir($result);
    }

    function testMailNotificationGuide() {
        
        $result = $this->mail_service->sendNotificationIncompleteGuide(['idPedido' => 1, 
                                                                        'numOrden' => '001-74125'], 
                                                                       ['tracking_number' => '794675171713', 
                                                                        'label_url' => '',
                                                                        'tracking_url_provider' => 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=794675171713']);
        
        $this->imprimir($result);
    }

    function sendWa() {

        //TOKEN QUE NOS DA FACEBOOK
        $token = 'EAAKRooF5GSgBO0zZCZCIIdcMfDkUfYCakRJXWGFSlDYjqXH9T99LxJbai14DROy4SchBXCVqnuUq2ZCORNHWzJ5zrfPe2NbTKeyLam9i7KZArrENiiHXv3ObFbL71TABJPYg2IZCQEbgMIpNAeruPhIXeSmIvikrYEdWJJIUy8KaZBUjQN5u1ySBsZBxZAv93ZCK3SkSVWOIQjZAlaeHEr';
        //NUESTRO TELEFONO
        $telefono = '527828209453';
        //URL A DONDE SE MANDARA EL MENSAJE
        $url = 'https://graph.facebook.com/v17.0/142225152316765/messages';

        //CONFIGURACION DEL MENSAJE
        $mensaje = ''
                . '{'
                . '"messaging_product": "whatsapp", '
                . '"to": "'.$telefono.'", '
                . '"type": "template", '
                . '"template": '
                . '{'
                . '     "name": "hello_world",'
                . '     "language":{ "code": "en_US" } '
                . '} '
                . '}';
        //DECLARAMOS LAS CABECERAS
        $header = array("Authorization: Bearer " . $token, "Content-Type: application/json",);
        //INICIAMOS EL CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
        $response = json_decode(curl_exec($curl), true);
        //IMPRIMIMOS LA RESPUESTA 
        print_r($response);
        //OBTENEMOS EL CODIGO DE LA RESPUESTA
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //CERRAMOS EL CURL
        curl_close($curl);
    }

    function webhookReceiveWA() {

        /*
        * VERIFICACION DEL WEBHOOK
        */
        //TOQUEN QUE QUERRAMOS PONER 
        $token = 'HolaNovato';
        //RETO QUE RECIBIREMOS DE FACEBOOK
        $palabraReto = $_GET['hub_challenge'];
        //TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
        $tokenVerificacion = $_GET['hub_verify_token'];
        //SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
        if ($token === $tokenVerificacion) {
            echo $palabraReto;
            exit;
        }
        /*
        * RECEPCION DE MENSAJES
        */
        //LEEMOS LOS DATOS ENVIADOS POR WHATSAPP
        $respuesta = file_get_contents("php://input");
        //CONVERTIMOS EL JSON EN ARRAY DE PHP
        $respuesta = json_decode($respuesta, true);
        //EXTRAEMOS EL TELEFONO DEL ARRAY
        $mensaje="Telefono:".$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['from']."</br>";
        //EXTRAEMOS EL MENSAJE DEL ARRAY
        $mensaje.="Mensaje:".$respuesta['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
        //GUARDAMOS EL MENSAJE Y LA RESPUESTA EN EL ARCHIVO text.txt
        file_put_contents("files/text.txt", $mensaje);

    }

    function testGenerateNumberOrder() {
        
        $user_data = $this->session->userdata();

		$data['numOrden'] = 'OID' . '-U' . $user_data['idUsuario'] . '-V-ECOMM-' . date("ymdHi");

        $this->imprimir($data);
    }

    function information($parameter = ''){

        $params = current( $this->file_service->searchByModel('viewModel', ['valor' => strtoupper ($parameter)], ['imprimirSQL' => 0], 'getAttribute') );

        $data['main_content'] = $this->load->view(
            'template/generic.html', 
            array(
				'parametros' => !empty($params) ? $params : '',
            ), 
            TRUE
        );

		$this->loadTemplate($data);
    }	
}
