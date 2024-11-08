<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pharma extends MY_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in 
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct() {

        parent::__construct();
        
//        $this->methodByPrivilege = [
//            'READ' => [],
//            'ADD' => [],
//            'EDIT' => [],
//            'DELETE' => []            
//        ];
//        
//        $this->validar_acceso(['','index','testMap', 'privacyPolicies', 'help']);
//
        $this->load->library('services/pharma_service');  
    }

    public function getStudios($id = 0) {

//        $user_data = $this->session->userdata();
//	$reg = $id ? current( $this->pharma_service->search(['id' => $id]) ) : FALSE;
        
        $this->load->view('pharma/studio_table.html', [
            
        ]);
    }
    
    public function reg($id = 0) {
        
	$reg = $id ? current( $this->pharma_service->search(['id' => $id]) ) : FALSE;
        
        $data['fileToLoad']  = ['pharma/js/data.js'];
        $data['main_content']  = $this->load->view('pharma/data.html', [
            'reg' => $reg
        ], TRUE);
        
        $this->loadTemplate($data);
    }
    
    function save($id = 0) {
        
        $data = $this->input->post();
//        $this->imprimir($data, 1);
        
        $result = $this->pharma_service->save($data['reg'], $id);
        
        $this->json($result);
    }
}