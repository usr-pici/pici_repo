<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller {

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
        
		$this->methodByPrivilege = [
            'READ' => [],
            'ADD' => [],
            'EDIT' => [],
            'DELETE' => []            
        ];
        
        $this->validar_acceso(['','index','testMap', 'privacyPolicies', 'help']);

        $this->load->library('services/view_service');  
    }

    public function index() {

        $user_data = $this->session->userdata();
		    
        $data['fileToLoad']  = ['home/js/home.js'];
        $data['main_content']  = $this->load->view('home/home.html', [
          
        ], TRUE);
        
        $this->loadTemplate($data);
    }
    
}