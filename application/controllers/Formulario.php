<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario extends MY_Controller {
    
    public function __construct() {

        parent::__construct();
       
        $this->methodByPrivilege = [
            'READ' => [],
            'ADD' => [],
            'EDIT' => [],
            'DELETE' => []            
        ];
        
        //$this->validar_acceso([]);

        $this->load->library('services/form_service');
        $this->load->library('services/view_service');
    }

    public function index() {
		    
        $data['fileToLoad']  = ['formulario/js/formulario.js'];
        $data['main_content']  = $this->load->view('formulario/formulario.html', [
            'title' => 'Búsqueda de Formulario',
        ], TRUE);
        $this->loadTemplate($data);
    }

    function ajustarParametros($filtros = []){

		if( isset($filtros['vigenciaIni']) && isset($filtros['vigenciaFin']) ){
			$fechaInicio = $this->formato_fecha_bd($filtros['vigenciaIni']);
			$fechaFin = $this->formato_fecha_bd($filtros['vigenciaFin']);

			$filtros['fechaOrdenPar'] = "'{$fechaInicio}'"." AND "."'{$fechaFin}'";
			unset($filtros['vigenciaIni'], $filtros['vigenciaFin']);
		}

		if( isset($filtros['vigenciaIni']) ){
			$fechaInicio = $this->formato_fecha_bd($filtros['vigenciaIni']);
			$filtros['fechaIniSolo'] = "'{$fechaInicio}'";
			unset($filtros['vigenciaIni']);
		}

        if( isset($filtros['vigenciaFin']) ){
			$fechaFin = $this->formato_fecha_bd($filtros['vigenciaFin']);
			$filtros['fechaFinSolo'] = "'{$fechaFin}'";
			unset($filtros['vigenciaFin']);
		}

		return $filtros;
	}

    function get_regs() {

        parse_str($this->input->post('filtros'), $filtros);

		foreach ($filtros as $idF => $f) {
			if (empty($f))
				unset($filtros[$idF]);
		}

        if( !empty($filtros['idEstatus']) ){
			if( $filtros['idEstatus'] == 'ALL' )
				unset($filtros['idEstatus']);
            else if( $filtros['idEstatus'] == 'NOT' )
                $filtros['activo'] = 0;
		}	

        $filtros = $this->ajustarParametros($filtros);

        $regs = $this->view_service->searchByModel('viewModel', $filtros, ['imprimirSQL' => 0, 'orderBy' => 'nombre ASC'], 'getForms');
        
        if ( $regs ) {
		
            foreach ( $regs as &$reg ) {

                $reg['vigenciaIni'] = $this->formato_fecha_pantalla($reg['vigenciaIni']);
                $reg['vigenciaFin'] = $this->formato_fecha_pantalla($reg['vigenciaFin']);
                $reg['estatus'] = $reg['activo'] == '1' ? 'Activo' : 'Inactivo';
                $reg['opciones'] = ' <a href="' . URL_SITE . 'formulario/editQuestion/' . $reg['idFormulario'] . '" title="Editar"><i class="fa fa-edit text-primary"></i></a>';
                $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg(this,'. $reg['idFormulario'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="clone_reg('. $reg['idFormulario'] . ')"><i title="Clonar" class="far fa-clone text-success"></i></a>';
            }
        }
        
        echo json_encode($regs);
    }

    function getRegsQuestions($idFormulario = 0) {

        $user_data = $this->session->userdata();

        if( empty($user_data['idFormulario']) ) {
            $user_data['idFormulario'] = $idFormulario;
        }

        if( !empty($user_data['idFormulario']) ) {

            $regs = $this->view_service->searchByModel('viewModel', ['idFormulario' => $user_data['idFormulario']], ['orderBy' => 'p.consecutivo ASC', 'imprimirSQL' => 0], 'getQuestions');
    
            if ( $regs ) {
            
                foreach ( $regs as &$reg ) {
                    $reg['opciones'] = '<a href="javascript:void(0);" title="Subir" ><i class="fas fa-arrow-up text-success"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" title="Bajar" ><i class="fas fa-arrow-down text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" title="Configurar" onclick="configQuestion(`'. $reg['idPregunta'] .'`)"><i class="fas fa-wrench" style="color:black"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg(this,'. $reg['idPregunta'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                }
            }   
        } else {
            $regs = array();
        }
        
        echo json_encode($regs);

    }

    function getOptionsRegs($idPregunta = '') {

        //$this->imprimir($idPregunta,1);
        //$user_data = $this->session->userdata();

        /*if( isset($user_data['idPregunta']) ) {
            $idPregunta = $user_data['idPregunta'];
        }*/

        if( !empty($idPregunta) ) {

            $regs = $this->view_service->searchByModel('viewModel', ['idPregunta' => $idPregunta], ['imprimirSQL' => 0, 'orderBy' => 'posicion ASC'], 'getOptionsQuestion');
            
            if ( $regs ) {
            
                foreach ( $regs as &$reg ) {
                    $reg['posicion'] = '<input type="text" class="form-control maskInteger text-center" min="1" value="'. $reg['posicion'] .'" >' ;
                    $reg['opcion'] = '<input type="text" class="form-control text-center" value="'. $reg['opcion'] .'" >' ;
                    $reg['opciones'] = '<a href="javascript:void(0);" title="Subir" ><i class="fas fa-arrow-up text-success"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" title="Bajar" ><i class="fas fa-arrow-down text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg_option(this,'. $reg['idPreguntaOpcion'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                }
            }

        } else {
            $regs = [];
        }        
        
        echo json_encode($regs);
    }

    function addModal() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}
            
        $this->load->view('formulario/modal-add.html', [

        ]);
    }

    function save() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $idFormulario = '';

        //Validar fecha inicial < fecha final
		if( strtotime($this->formato_fecha_bd($reg['vigenciaIni'])) > strtotime($this->formato_fecha_bd($reg['vigenciaFin'])) )
            return $this->msg_error("La fecha de vigencia inicial no puede ser mayor a la fecha de vigencia final!, verifique.");

        if( isset($reg['idFormulario']) ) {
            $idFormulario = $reg['idFormulario'];
            unset($reg['idFormulario']);
        }

        //$this->imprimir($idFormulario);
        //$this->imprimir($reg,1);

        $result = $this->form_service->save($reg, !empty($idFormulario) ? $idFormulario : NULL);

        if( $result['error'] == 0 ) {
            $this->session->set_userdata('idFormulario', !empty($idFormulario) ? $idFormulario : $result['id']);
        }

        echo json_encode($result);
    }

    function update() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        //Validar fecha inicial < fecha final
		if( strtotime($this->formato_fecha_bd($reg['vigenciaIni'])) > strtotime($this->formato_fecha_bd($reg['vigenciaFin'])) )
            return $this->msg_error("La fecha de vigencia inicial no puede ser mayor a la fecha de vigencia final!, verifique.");

        if( isset($reg['idFormulario']) ) {
            $idFormulario = $reg['idFormulario'];
            unset($reg['idFormulario']);
        }

        $result = $this->form_service->save($reg, $idFormulario);

        if( $result['error'] == 0 ) {
            $this->session->set_userdata('idFormulario', !empty($idFormulario) ? $idFormulario : $result['id']);
        }

        echo json_encode($result);
    }

    function saveQuestion() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        if( isset($reg['posicion']) )
            unset($reg['posicion']);

        if( isset($reg['opcion']) )
            unset($reg['opcion']);

        if( isset($reg['idPregunta']) ) {
            $idPregunta = $reg['idPregunta'];
            unset($reg['idPregunta']);
        }

        if( $reg['idTipoCampo'] != 'DATE' && isset($reg['formato']) )
            unset($reg['formato']);

        if( isset($reg['idRol']) ) {
            $roles = $reg['idRol'];
            unset($reg['idRol']);
        }

        //$this->imprimir($idPregunta);
        //$this->imprimir($reg,1);

        $result = $this->form_service->saveQuestion($reg, !empty($idPregunta) ? $idPregunta : NULL);

        if( $result['error'] == 0 ) {
            $result['idPregunta'] = !empty($idPregunta) ? $idPregunta : $result['id'];
            $this->session->set_userdata('idPregunta', $result['idPregunta']);

            if( !empty($roles) ){
                
                foreach ($roles as $key => $rol) {
                    $valid = current( $this->form_service->search_questionRol(['idPregunta' => !empty($idPregunta) ? $idPregunta : $result['id'], 'idRol' => $rol, 'borrado' => 0]) );
                    //$this->imprimir($registro,1);
                    if( empty($valid) )
                        $result = $this->form_service->saveQuestionRol(['idPregunta' => !empty($idPregunta) ? $idPregunta : $result['id'], 'idRol' => $rol], NULL);	
                    else 
                        return $this->msg_error("Combinaci\xf3n Pregunta/rol ya registrado, verifique.");
                }  
                
            }
        }

        echo json_encode($result);
    }

    function getConsecutiveOptions() {

		$user_data = $this->session->userdata();

        $questions = count( $this->view_service->searchByModel('viewModel', ['idPregunta' => !empty($user_data['idPregunta']) ? $user_data['idPregunta'] : ''], ['imprimirSQL' => 0], 'getOptionsQuestion') );

        echo json_encode(['posicion' => $questions + 1]);
    }

    function saveOptionsQuestion() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        //$this->imprimir($reg,1);

		$user_data = $this->session->userdata();

        if( isset($reg['idPregunta']) && empty($reg['idPregunta']) )
            $reg['idPregunta'] = !empty($user_data['idPregunta']) ? $user_data['idPregunta'] : '';

        $result = $this->form_service->saveOptionQuestion($reg, NULL);

        echo json_encode($result);
    }

    function saveQuestionCondition() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        $this->imprimir($reg,1);

        $result = $this->form_service->saveQuestionCondition($reg, NULL);

        echo json_encode($result);
    }

    function getQuestionsHTML($idFormulario = 0, $bandera = 0) {

        $html = '';

        if( $idFormulario == 0 ) {
            $idFormulario = $this->input->post('idFormulario');
            $bandera = $this->input->post('bandera');
        }

        $questions = $this->view_service->searchByModel('viewModel', ['idFormulario' => $idFormulario], ['orderBy' => 'p.consecutivo ASC', 'imprimirSQL' => 0], 'getQuestions');
        
        $options = $this->form_service->indexed_search_OptionForm(['idPreguntaOpcion','idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

        //$this->imprimir($options,1);

        $html .= '  <div class="row mb-2">';

        foreach( $questions as $key => $q ) {
            
            $html .= ' <div class="col-md-6 mb-2">
                                <h6 class="fw-bold">'. $q['consecutivo'] .'.- '. $q['etiqueta'] .':'. ($q['requerido'] == 1 ? '&nbsp;<span class="required">*</span>' : '') .'</h6>
                            
                            ';
            
            if( $q['cveField'] == 'TEXT' || $q['cveField'] == 'DATE' ) {
                $html .= '<input '. (!empty($q['longitud']) ? 'maxlength="'. $q['longitud'] .'"' : '') .' type="text" class="form-control '. ($q['cveField'] == 'DATE' ? 'fecha' : '') .'" name="'. $q['idPregunta'] .'" id="question_'.$q['idPregunta'].'" >';
            } elseif( $q['cveField'] == 'RADIO') {

                foreach( $options as $optKey => $optValue ) {

                    if( !empty($optValue[$q['idPregunta']]) ) {
                        $html .= '<div class="form-check">
                                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="'. $optValue[$q['idPregunta']]['opcion'] .'">
                                    <label class="form-check-label" for="'. $optValue[$q['idPregunta']]['opcion'] .'">'. $optValue[$q['idPregunta']]['opcion'] .'</label>
                                  </div>';
                    }            
                }

            } elseif( $q['cveField'] == 'CHECKBOX') {

                
            } elseif( $q['cveField'] == 'LIST') {

                
            } elseif( $q['cveField'] == 'LIST_MULTIPLE') {

                
            } elseif( $q['cveField'] == 'TEXT_AREA') {

                
            }

            
            $html .= '  </div>';

        }

        $html .= '</div>';

        if( $bandera == 0 ) {
            return $html;
        } else {
            echo $html;
        }
    }

    function addQuestion(){ 

        $data['fileToLoad']  = ['formulario/js/addQuestion.js'];
        $data['main_content']  = $this->load->view('formulario/addQuestion.html', [
            'title' => 'Registro de Formulario',
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function editQuestion($idFormulario = ''){

        $reg = current( $this->view_service->searchByModel('viewModel', ['idFormulario' => $idFormulario], ['imprimirSQL' => 0], 'getForms') );
                
        if( $reg ) {
			$reg['vigenciaIni'] = $this->formato_fecha_pantalla($reg['vigenciaIni']);
            $reg['vigenciaFin'] = $this->formato_fecha_pantalla($reg['vigenciaFin']);
        }

        $data['fileToLoad']  = ['formulario/js/editQuestion.js'];
        $data['main_content']  = $this->load->view('formulario/editQuestion.html', [
            'formulario' => $reg,
            'title' => 'Editar Formulario',
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function preview($idFormulario = 0) {

        $html = $this->getQuestionsHTML($idFormulario, 0);  

        $data['main_content']  = $this->load->view('formulario/preview.html', [
            'questions' => $html,
        ], TRUE);
        
        $this->loadTemplate($data);

    }

    function cat_rol(){

        $reg = $this->input->post();

		$getRoles = $this->catalogo_service->search('rol', []);
                
        $cat_rol = $this->catalogo_service->get_list_to_select(
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

    function addModalQuestion() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $numQuestion = count(  $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario']], ['imprimirSQL' => 0], 'getQuestions') );
        
        $questionOptions = $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario'], 'option_IN' => "'LIST', 'LIST_MULTIPLE', 'RADIO', 'CHECKBOX'"], ['imprimirSQL' => 0], 'getQuestions');

        //$this->imprimir($questionOptions,1);
		//$cat_rol = $this->catalogo_service->search('rol', []);
            
        $this->load->view('formulario/modal-add-question.html', [
            "posicion" => $numQuestion + 1,
            'idFormulario' => $reg['idFormulario'],
            //'cat_rol' => $cat_rol,
            'listaTipoCampo' => $this->catalogo_service->search_to_select(
				'tipoCampo',
				[
					'index_id' => 'clave',
					'index_desc' => 'nombre',
					'id_reg' => '',
					'filtros' => [],
					'extras' => ['orderBy' => 'nombre ASC'],
					'etiqueta' => '- Elegir -',
					//'con_etiqueta' => count($clasificacion) > 1
				]
			),
            'listaPregunta' => $this->catalogo_service->get_list_to_select(
				[
					'index_id' => 'idPregunta',
					'id_reg' => '',
					'index_desc' => 'etiqueta',
					'regs' => $questionOptions,
					'etiqueta' => ' - Pregunta - ',
				]
			)
        ]);
    }

    function cat_optionsQuestion(){

        $reg = $this->input->post();

		$getOptionsQuestion = $this->form_service->search_OptionForm(['idPregunta' => $reg['idPregunta'], 'vigente' => '1'], ['orderBy' => 'posicion ASC']);
        
        $options = $this->form_service->get_list_to_select(
            [
                'index_id' => 'idPreguntaOpcion',
                'id_reg' => '',
                'index_desc' => 'opcion',
                'regs' => $getOptionsQuestion,
				'etiqueta' => ' - Opción - ',
            ]
        );

        echo $options;
    }

    function editModalQuestion() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        $numQuestion = count(  $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario']], ['imprimirSQL' => 0], 'getQuestions') );

        $question = current( $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario'], 'idPregunta' => $reg['idPregunta']], ['imprimirSQL' => 0], 'getQuestions') );

        $cat_rol = $this->catalogo_service->search('rol', ['activo' => 1,'borrado' => 0]);
        $rolesSeleccionadas = $this->form_service->indexed_search_rol_question(['idRol'], ['idPregunta' => $reg['idPregunta'], 'borrado' => 0]);
		$ids = !empty($rolesSeleccionadas) ? implode(',', array_keys($rolesSeleccionadas) ) : [];
		$idsRoles = !empty($ids) ?$this->catalogo_service->search('rol', ['id_IN' => $ids]) : [];
		$arreglo = [];
		
		foreach($idsRoles as $id) {
			array_push($arreglo, $id['idRol']);
		}

        $questions = $this->catalogo_service->search('tipoCampo', []);

		//$cat_rol = $this->catalogo_service->search('rol', []);

        //$this->imprimir($cat_rol);
        //$this->imprimir($question['cveField'],1);
            
        $this->load->view('formulario/modal-add-question.html', [
            "posicion" => $numQuestion + 1,
            'idFormulario' => $reg['idFormulario'],
            'question' => $question,
            'roles' => $cat_rol,
			'idsRoles' => $arreglo,
            'listaTipoCampo' => $this->catalogo_service->search_to_select(
				'tipoCampo',
				[
					'index_id' => 'clave',
					'index_desc' => 'nombre',
					'id_reg' => $question['cveField'],
					'filtros' => [],
					'extras' => ['orderBy' => 'nombre ASC'],
					'etiqueta' => '- Elegir -',
					'con_etiqueta' => count($questions) > 1
				]
			),
            /*'listaRol' => $this->catalogo_service->search_to_select(
				'rol',
				[
					'index_id' => 'idRol',
					'index_desc' => 'nombre',
					'id_reg' => '',
					'filtros' => [],
					'extras' => ['orderBy' => 'nombre ASC'],
					'etiqueta' => '- Elegir -',
					//'con_etiqueta' => count($clasificacion) > 1
				]
			),*/
        ]);
    }


    function delete($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->form_service->delete($idRegistro, NULL, $reg);

        echo json_encode($result);  
	}

    function clone($idRegistro = 0) {

        if( $idRegistro != '' ) {
            
            $formulario = $this->form_service->search(['id' => $idRegistro, 'borrado' => 0]);

            $this->imprimir($formulario,1);

            if( $formulario ) {

                echo json_encode( ['error' => 0, 'msg' => 'Se duplicó el formulario'] );

            } else {
                echo json_encode( ['error' => 1, 'msg' => 'Error al consultar el formulario.'] );
            }

        } else {
            echo json_encode( ['error' => 1, 'msg' => 'Error al obtener el ID del formulario'] );
        }
	}
    
}
