<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Catalogo extends MY_Controller {
    
    function __construct() {
        
        parent::__construct();

        $this->methodByPrivilege = [
            'READ' => [],
            'ADD' => [],
            'EDIT' => [],
            'DELETE' => []            
        ];

        //$this->validar_acceso([]);
                
        $this->load->library('services/catalogo_service');
    }

    //Magic Method
    function _remap($method, $params = array()) {
        
        
        if (
            !$this->catalogo_service->is_valid($method)
            // || ( !empty($params) && in_array($params[0], array('search', 'add', 'edit', 'delete')) === FALSE  ) 
        ) {
            
            $this->json( array('msg' => 'Datos incorrectos, verifique.') );
        }
        
        if ( empty($params) ) {
            
            $this->index($method);
            
        } else {
            
            $this->{$params[0]}($method, $params);
        }
    }

    function index($catalogo = '') {
        
        $data_int['config'] = $this->catalogo_service->get_config_to_view($catalogo);
        $data_int['config']['catalogo'] = $catalogo;

        $data['fileToLoad'] = ['catalogo/js/catalogo.js'];
        $data['main_content'] = $this->load->view('catalogo/catalogo_view.php', $data_int, TRUE);
        
        $this->loadTemplate($data);
    }
    
    function get_regs($catalogo = '') {

        $filtros = array();
        $extras = array();

        $resp = $this->catalogo_service->get_regs($catalogo, array('borrado' => 0) + $filtros, $extras);

		// $this->imprimir($resp, 1);

        foreach ($resp as $key => &$value) {
            
            if (array_key_exists('activo', $value) ){

				// $this->imprimir($resp[$key]['activo'], 1);
                if($resp[$key]['activo'] == '1') {
					$resp[$key]['activo'] = 'Si';
                }else if ($resp[$key]['activo'] == ''){
                    $resp[$key]['activo'] = 'No';
                }
            }
        }
        
        echo json_encode( $resp );
    }
     
    function get_cat_dependiente($catalogo = '') {
        
        $dependencia = $this->input->post('dependencia');
        $filtros = $this->input->post('filtros');
//        $this->imprimir($filtros);
                
        echo $this->catalogo_service->get_cat_dependiente($catalogo, $dependencia, $filtros);
    }
    
    function get_reg($catalogo = '', $params = array()) {
        
        $resp = $this->catalogo_service->get_reg($catalogo, $this->input->post('id'));
        
        echo json_encode( $resp );
    }
    
    function delete($catalogo = '', $params = array()) {
        
        $result = $this->catalogo_service->delete($catalogo, $this->input->post('id'));
        
        echo json_encode( $result );
    }
    
    function save($catalogo = '', $params = array()) {
        
        $method = array();
        $reg = $this->input->post('reg');
        
        echo json_encode( $this->catalogo_service->save( $catalogo, $reg, empty($params[1]) ? 0 : $params[1], 'reg', $method ) );
    }
    
}
