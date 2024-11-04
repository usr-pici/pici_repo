<?php

/**
 * Description of MY_Controller
 *
 * @author Felipe Avila
 */

class MY_Controller extends CI_controller {
    
    private $template_base = 'index';
    
    protected $privilege;
    
    protected $methodByPrivilege;
    
    function __construct() {
        
        parent::__construct();

		$session = $this->session->userdata();                
                
        date_default_timezone_set('America/Mexico_City');
        
        if ( !defined('URL_BASE') )
            define('URL_BASE', $this->config->item('base_url'));
        if ( !defined('URL_SITE') )
            define('URL_SITE', URL_BASE . $this->config->item('index_page'));
        if ( !defined('URL_APP') )
            define('URL_APP', URL_BASE . 'application/');
        if ( !defined('URL_VIEWS') )
            define('URL_VIEWS', URL_APP . 'views/');   
        if ( !defined('PATH_APP') )
            define('PATH_APP', APPPATH);
        if ( !defined('CONTROLLER') )
            define('CONTROLLER', $this->uri->segment(1));
        if ( !defined('METHOD') )
            define('METHOD', $this->uri->segment(2));
			
		if ( !defined('API_KEYS_MODE') )
			define('API_KEYS_MODE', 'DEVELOPMENT'); // PRODUCTION   DEVELOPMENT
		
		if ( !defined('GOOGLE_API_KEYS') )
			define('GOOGLE_API_KEYS', 'AIzaSyDMkdTil9zezgTB8nPhgDMdEQS9NAu8pbg'); // PRODUCTION   DEVELOPMENT

        if ( !defined('GOOGLE_RECAPTCHA_KEY') )
			define('GOOGLE_RECAPTCHA_KEY', ['keySiteWeb' => '6LdHjSImAAAAALOagnxpczgqk0ccPpZZtos7Oegd', 
                                            'keySecret' => '6LdHjSImAAAAAG3sZaLNwipcAaYXd2rSXzguvI5u']); // RECAPTCHA

        if ( !defined('KEY_APP_FACEBOOK') )
			define('KEY_APP_FACEBOOK', '411174144475732'); // FACEBOOK

        if ( !defined('FIREBASE_CONFIG') )
			define('FIREBASE_CONFIG', ['apiKey' => 'AIzaSyChwjhvg5ACe39NtRO5Oiz1lAoYU-CUW9I',
                                       'authDomain' => 'general-ecommerce-3766e.firebaseapp.com',
                                       'projectId' => 'general-ecommerce-3766e',
                                       'storageBucket' => 'general-ecommerce-3766e.appspot.com',
                                       'messagingSenderId' => '110065707903',
                                       'appId' => '1:110065707903:web:e7430fd85ca21eddf1884a']); // GOOGLE
		
		if ( !defined('CLAVE_PERFIL') )
            define('CLAVE_PERFIL', empty($session['rol']) ? FALSE : $session['rol']['clave']);
        
		if ( !defined('ES_ADMIN') )
            define('ES_ADMIN', CLAVE_PERFIL === 'CONACYT');
        
        if ( !defined('CLAVE_USUARIO') )
            define('CLAVE_USUARIO', $this->session->userdata('usuario'));
        
        if ( !defined('LOGGED') )
            define('LOGGED', $this->session->has_userdata('idUsuario'));
    	
		if ( !defined('PRODS') )
            define('PRODS', $this->session->has_userdata('prods'));
        
        if ( !defined('TIME_TOKEN') )   // Tiempo de inactividad máximo (en minutos)
            define('TIME_TOKEN', 10);
        
        if ( !defined('PATH_CLASS_SERVICE') )
            define('PATH_CLASS_SERVICE', 'application/libraries/services/class_service.php');
        
        if ( !defined('PATH_INTERFACE') )
            define('PATH_INTERFACE', 'application/libraries/interfaces/');
        
        if ( !defined('SERVER') )
            define('SERVER', $_SERVER['SERVER_NAME'] === 'vacantesepn.conacyt.mx' ? 'PROD' : ( $_SERVER['SERVER_NAME'] === '172.16.6.14' ? 'DEV' : 'LOCAL' ));

        if ( !defined('URL_PORTAL_ADMINISTRACION') )
            define('URL_PORTAL_ADMINISTRACION','https://localhost/general_admin_ecommerce/');
        
        $this->load->library('form_validation');
        $this->load->library('services/seguridad_service');

    }
    
    function array_index($indexes = NULL, $arr = [], $multiply = FALSE ) {
        
        if ( empty($arr) || empty($indexes) || !is_array($arr) ) {

            return FALSE;
        }

        if ( ! is_array($indexes) ) 
            $indexes = array($indexes);

        foreach ( $indexes as $index ) {

            if (!array_key_exists($index, $arr[0])) {

                return FALSE;
            }
        }

        $result = [];
        foreach ($arr as $reg) {

            $tmp = '';
            foreach ($indexes as $index) {

                $tmp .= "['{$reg[$index]}']";
            }

            eval("\$result{$tmp}" . ( $multiply ? "[]" : "") . " = \$reg;");
        }

        return $result;
    }
    
    function array_unique_without_empty($arr) {
        
        if ( empty($arr) ) {
            
            return FALSE;
        }
        
        $tmp = array_unique( $arr );
        
        $emptyIndex = array_search('', $tmp);        
        if ( $emptyIndex !== FALSE ) {
            
            if ( count($tmp) === 1 )
                return FALSE;
            
            unset($tmp[$emptyIndex]);
        }
        
        return $tmp;
    }
    
    public function log($param, $die = FALSE, $fileName = '') {
        
        $this->load->helper('file');
        
        ob_start();
        
            echo date('d/m/Y H:i:s:') . PHP_EOL;
            $this->imprimir( $param );
            echo PHP_EOL;
            
            $salida = ob_get_contents();

        ob_end_clean();
        
        if ( empty($fileName) ) { 
        
            $fileName = date('Y-m-d');
        }
        
        write_file("application/logs/{$fileName}.txt", $salida, 'ab');
        
        if ( $die ) {
            
            die();
        }
    }
    
    function validString($val, $valIsEmpty = NULL) {
        
        $val = trim($val);
        
        return strlen($val) === 0 ? $valIsEmpty : $val;
    }
    
    function validInt($val, $returnNAN = FALSE) {
        
        $val = trim($val);
        
        return strlen($val) === 0 || preg_match('/[^\d\-]/', $val) ? $returnNAN : ( (int) $val );
    }
    
    function validFloat($val, $returnNAN = FALSE) {
        
        $val = str_replace(',', '', trim($val));
        
        return strlen($val) === 0 || preg_match('/[^\d\.\-]/', $val) ? $returnNAN : ( (float) $val );
    }
    
    function nameSanitize($name) {
        
        $this->load->helper('text');
        
        return preg_replace('/[\s]+/', '_', convert_accented_characters($name));
    }
    
    function send_mail($mail) {
        
        $this->load->library('email');
        //$this->imprime($mail, 1); 
        
        //$this->email->from($mail['from']);
        $this->email->from('desarrollo.webmx2021@gmail.com', 'Ecommerce');
        $this->email->to($mail['to']);
//        $this->email->to('a.v.felipe@gmail.com');
        $this->email->subject( utf8_encode( $mail['subject'] ) );
        $this->email->message($mail['msg']);
        
        if ( !empty($mail['cc']) )
            $this->email->cc($mail['cc']);

        if ( !empty($mail['file']) ) {
            
            if ( is_array($mail['file']) ) {
                
                $this->email->attach($mail['file'][0], $mail['file'][1], $mail['file'][2]);
                
            } else {
                
                $this->email->attach($mail['file']);
            }
        }
        
        $result_envio = $this->email->send();
        //$this->imprime($this->email->print_debugger(), 1);

        if ( ! $result_envio ) {
            
            $result = array('error' => 1, 'msg' => ('No pudo ser enviado el correo, intente nuevamente.')); //$this->mensaje_de_salida
            $this->log( $this->email->print_debugger() );
            
        } else {
            
            $result = array('error' => 0, 'msg' => ('Se ha enviado el correo.'));    //$this->mensaje_de_salida
            //$this->log( $mail );
        }
        
        return $result;
    }

    function loadLibrary(){

        $data = array();

        $controllers_methods_google = ['login' => ['/', '']];
        $controllers_method_facebook = ['login' => ['/','']];
        $controllers_method_input_mask = ['login' => ['/', '', 'recuperar', 'validar', 'reset'], 'user' => ['register', 'profile', 'notified']];
        $controllers_method_recaptcha = ['login' => ['/', '', 'recuperar', 'validar', 'reset'], 'user' => ['register', 'profile']];
        $controllers_method_payment_gateway = ['checkout' => ['getCards', 'registerCard'], 'order' => ['/', '']];
        $controllers_method_maps = ['address' => ['register', 'edit'], 'product' => ['detailBranch']];
        $controllers_method_card_style = ['checkout' => ['registerCard']];
        //$controllers_method_datatable = ['address' => ['/', ''], 'checkout' => ['getCards'], 'user' => ['billing'], 'product' => ['wishList'], 'order' => ['/', '', 'history']];
        $controllers_method_oxxo_barcode = ['order' => ['checkout', 'history', 'detail', 'orderCompleted']];

        $data['loadGoogle'] = !empty($controllers_methods_google[CONTROLLER]) && in_array(METHOD, $controllers_methods_google[CONTROLLER]);
        $data['loadFacebook'] = !empty($controllers_method_facebook[CONTROLLER]) && in_array(METHOD, $controllers_method_facebook[CONTROLLER]);
        $data['loadInputMask'] = !empty($controllers_method_input_mask[CONTROLLER]) && in_array(METHOD, $controllers_method_input_mask[CONTROLLER]);
        $data['loadRecatpcha'] = !empty($controllers_method_recaptcha[CONTROLLER]) && in_array(METHOD, $controllers_method_recaptcha[CONTROLLER]);
        $data['loadPaymentGateway'] = !empty($controllers_method_payment_gateway[CONTROLLER]) && in_array(METHOD, $controllers_method_payment_gateway[CONTROLLER]);
        $data['loadMaps'] = !empty($controllers_method_maps[CONTROLLER]) && in_array(METHOD, $controllers_method_maps[CONTROLLER]);
        $data['loadCardStyle'] = !empty($controllers_method_card_style[CONTROLLER]) && in_array(METHOD, $controllers_method_card_style[CONTROLLER]);
        //$data['loadDatatable'] = !empty($controllers_method_datatable[CONTROLLER]) && in_array(METHOD, $controllers_method_datatable[CONTROLLER]);
        $data['loadBarcodeOxxo'] = !empty($controllers_method_oxxo_barcode[CONTROLLER]) && in_array(METHOD, $controllers_method_oxxo_barcode[CONTROLLER]);

        return $data;
    }

    function loadLibraryPayment($data = []) {

        if( !empty($data['cvePasarelaPago']) )
            return strtoupper($data['cvePasarelaPago']);
        else
            return FALSE;
    }
    
    function loadTemplate($data = []) {
                
        $data['controller'] = $this->uri->segment(1);
        $data['method'] = $this->uri->segment(2);

        $this->load->view("template/template.html", $data);
    }
    
    function replace_font_size($str = '', $size = 10) {
        
        return preg_replace('/(font\-size:[\s]*)([\d]+)(px)/i', '${1}'.$size.'${3}', $str);
    }
    
    function calcular_edad($fecha_nac = '', $formato = 'Y-m-d') {
        
        $timestamp_fecha_nac = strtotime($fecha_nac);
        $timestamp_hoy = strtotime('now');
        
        $diff = abs($timestamp_hoy - $timestamp_fecha_nac);
        
        return floor($diff / (60*60*24*365));
    }
    
    function get_date($psTime = ''){

        $months =array(1 => 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $days = array('Domingo', 'Lunes', 'Martes', utf8_encode("Mi\xE9rcoles"), 'Jueves', 'Viernes', utf8_encode("S\xE1bado"));

        if( !empty($psTime) && substr(0,4, $psTime) == "0000" ) {

            return "";
        }

        $d = empty($psTime) ? getdate() : getdate( strtotime($psTime) );

        $d['weekday'] = $days[$d['wday']];
        $d['month'] = $months[$d['mon']];

        $d['format1'] = sprintf("%s, %2d de %s de %d", $d['weekday'], $d['mday'], $d['month'], $d['year']);

        return $d;
    }
    
    function get_plain_html($text = '') {
        
        $text_sample = strip_tags($text, '<p><br>');
//        $text_sample = preg_replace(array('/<[\/]?[.]:[^>]*>/'), array(''), $text_sample);
//        $this->imprimir($text_sample);
//        
        return preg_replace(array('/<p[^>]*>/'), array('<p>'), $text_sample);
    }
    
    function get_plain_text($text = '') {
        
        $text_sample = strip_tags($text, '<p><br>');
//        $this->imprimir($text_sample);
//        
        return preg_replace(array('/<p[^>]*>/', '/<(\/p|br)[^>]*>/'), array("\n\r", ""), $text_sample);
    }
    
    function formato_miles($numero) {
        
        $val = trim($numero);
        
        if ( strlen( $val ) === 0 ) {   //  || preg_match('/^0\.0[0]+$/', $numero)
            
            return '';
        }
        
        return number_format($val, 2);
    }
    
    function formato_miles_bd($numero) {
        
        return str_replace(',', '', $numero);
    }
    
    function format_to_IN($data, $strpadLength = 0) {
        
        if ( is_array($data) ) {
            
            foreach ($data as &$v) {
                
                $v = "'" . ( $strpadLength > 0 ? $this->strpad($v, $strpadLength) : $v ) . "'";
            }
            
            $result = implode(',', $data);
            
        } else {
            
            $result = "'" . $data . "'";
        }
        
        return $result;
    }
    
    function strpad($val = '', $length = 0, $dir = 'l') {
        
        return str_pad($val, $length, '0', $dir === 'r' ? STR_PAD_RIGHT : STR_PAD_LEFT );
    }
    
    function formato_fecha_bd($valor) {
        
        if ( preg_match('/^([\d]{2})\/([\d]{2})\/([\d]{4})/', $valor, $fecha) ) {
            
            return implode('-', array($fecha[3], $fecha[2], $fecha[1])) . substr($valor, 10);            
        }
        
        return $valor;
    }
    
    function formato_fecha_pantalla($valor, $length = 0) {
        
        if ( preg_match('/^([\d]{4})-([\d]{2})-([\d]{2})/', $valor, $fecha) ) {
            
            if ( $fecha[1] == '0000' ) {
                
                return '';
            }
            
            $val = implode('/', array($fecha[3], $fecha[2], $fecha[1])) . substr($valor, 10);
            
            return $length ? substr($val, 0, $length) : $val;
        }
        
        return $valor;
    }
    
    function imprimir($var, $die = FALSE) {
        
        echo "<pre>"; var_dump($var); echo "</pre>";
        
        if ( $die ) { exit(0); }
    }
    
    public function msg_error($msg = '', $clave = '', $imprimir = TRUE, $indexes = NULL) {
        
        if ( empty($msg) ) {
            
            $msg_array = array(
                'DESCONOCIDO' => "Error inesperado, intente de nuevo.",
                'PARAMETRO' => "Par\xE1metro(s) incorrecto(s), verifique."
            );
        }
        
        return $this->json(array('error' => 1, 'msg' => !empty($msg) ? $msg : ( empty($msg_array[$clave]) ? $msg_array['DESCONOCIDO'] : $msg_array[$clave] )), $imprimir, $indexes);
    }
    
    function json( $datos = array(), $imprimir = TRUE, $indexes = NULL ) {
        
        if ( !isset($datos['error']) ) {    // || in_array($datos['error'], array(0, 1)) === FALSE
            
            $datos['error'] = 1;
        }
            
//        $datos['msg'] = empty($datos['msg']) ? '' : $this->mensaje_de_salida(utf8_encode($datos['msg']), $datos['error']);
        $datos['msg'] = empty($datos['msg']) ? '' : $this->format_output_screen( utf8_encode($datos['msg']), $indexes );
        
        if ( $imprimir ) {
            
            die( json_encode($datos) );
            
        } else {
            
            return $datos;
        }
    }
    
    function format_ul($tree, $type = 'danger', $indexes = NULL, $icon = '') {
        
        $elems = $this->format_output_screen($tree, $indexes);
        
        if ( !is_array($elems) ) {
            
            $elems = (array) $elems;
        }
        
        if ( empty($icon) && !empty($type) ) {
            
            $icons = array('danger' => 'times', 'success' => 'check');
            
            $icon = empty($icons[$type]) ? '' : $icons[$type];
        }
        
        $result[] = '<ul class="list-group">';        
        foreach ($elems as $elem) {
            
            $result[] = '<li class="list-group-item'.(empty($type) ? '' : ' list-group-item-'.$type ).'">' . ( empty($icon) ? '' : '<span class="fas fa-'.$icon.'"></span> ' ) . $elem . '</li>';
        }
        $result[] = '</ul>';
        
        return implode('', $result);
    }
    
    function format_output_screen($tree, $indexes = NULL) {
        
        if ( $indexes === 'none' ) {
            
            return $tree;
        }
        
        if (is_array($tree)) {

            foreach ($tree as $index => $value) {

                if ( empty($indexes) || ( is_array($indexes) && in_array($index, $indexes) ) || ( !is_array($indexes) && $index == $indexes) )
                    $tree[$index] = $this->format_output_screen($value, $indexes);
            }

            return $tree;

        } elseif (is_object($tree)) {

            return $tree;
        }

        return @htmlentities($tree, ENT_QUOTES, mb_detect_encoding($tree));
    }
    
    public function validar_acceso($param) {

        $data = $this->input->post();

        if (!empty($param['access_logout']) && is_array($param['access_logout']) && in_array($this->uri->segment(2), $param['access_logout'])) {

            return TRUE;
            
        } elseif (!LOGGED) {

            $this->load->library('user_agent');

            $user_data = $this->session->userdata();
            
            if ($this->agent->is_browser()) {

                if ($this->input->is_ajax_request()) {

                    if( $this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'getInfoContactUser' && !empty($user_data['products']) )
                        $this->session->set_userdata('urlGoCartCheckout', 'order');
     
                    if( !in_array($this->uri->segment(2), $param) )
                        die ( json_encode(['error' => -1, 'msg' => 'Redireccionando...']) );    
                } else {
  
                    if( !in_array($this->uri->segment(2), $param) )
                        redirect('login');
                } 
            } 

        } else {
            
            $result = $this->seguridad_service->validPrivilege($this->methodByPrivilege);
            //Error -10 indica que no tienes acceso
            if( $result['error'] == -10 ) {
                if ($this->input->is_ajax_request())
                    return $this->json(["error" => -10, "msg" => "No tienes permisos sobre esta acci\xf3n, verifique."]);
                else
                    redirect('login');
                
            }
        }
    }

}
