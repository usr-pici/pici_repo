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
			if (empty($f)) unset($filtros[$idF]);
		}

        if( !empty($filtros['idEstatus']) ){
			if( $filtros['idEstatus'] == 'ALL' )
				unset($filtros['idEstatus']);
            else if( $filtros['idEstatus'] == 'NOT' ) {
                $filtros['activo'] = 0;
				unset($filtros['idEstatus']);
            } else if( $filtros['idEstatus'] == '1' ) {
                $filtros['activo'] = 1;
				unset($filtros['idEstatus']);
            }
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

    function getRegsQuestions($idFormulario = '') {

        if( !empty($idFormulario) ) {

            $regs = $this->view_service->searchByModel('viewModel', ['idFormulario' => $idFormulario], ['orderBy' => 'p.consecutivo ASC', 'imprimirSQL' => 0], 'getQuestions');
    
            if ( $regs ) {
                foreach ( $regs as &$reg ) {
                    $reg['opciones'] = '<a onclick="configLevelQuestion('. $reg['idPregunta'] . ','. $reg['consecutivo'] .',`UP`, '. $reg['idFormulario'] .')" href="javascript:void(0);" title="Subir" ><i class="fas fa-arrow-up text-success"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a onclick="configLevelQuestion('. $reg['idPregunta'] . ','. $reg['consecutivo'] .',`DOWN`, '. $reg['idFormulario'] .')" href="javascript:void(0);" title="Bajar" ><i class="fas fa-arrow-down text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" title="Configurar" onclick="configQuestion(`'. $reg['idPregunta'] .'`)"><i class="fas fa-wrench" style="color:black"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_question_reg(this,'. $reg['idPregunta'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                }
            }   
        } else {
            $regs = array();
        }
        
        echo json_encode($regs);
    }

    function getOptionsRegs($idPregunta = '') {

        if( !empty($idPregunta) ) {

            $regs = $this->view_service->searchByModel('viewModel', ['idPregunta' => $idPregunta], ['imprimirSQL' => 0, 'orderBy' => 'posicion ASC'], 'getOptionsQuestion');
            
            if ( $regs ) {
                foreach ( $regs as &$reg ) {
                    $reg['opciones'] = '<a onclick="configLevelOption('. $reg['idPreguntaOpcion'] . ','. $reg['posicion'] .',`UP`, '. $reg['idPregunta'] .')" href="javascript:void(0);" title="Subir" ><i class="fas fa-arrow-up text-success"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a onclick="configLevelOption('. $reg['idPreguntaOpcion'] . ','. $reg['posicion'] .',`DOWN`, '. $reg['idPregunta'] .')" href="javascript:void(0);" title="Bajar" ><i class="fas fa-arrow-down text-warning"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a onclick="edit_opcion_question('. $reg['idPreguntaOpcion'] . ', '. $reg['posicion'] .', `'. $reg['opcion'] .'`, '. $reg['idPregunta'] .')" href="javascript:void(0);" title="Editar"><i class="fa fa-edit text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_option_reg(this,'. $reg['idPreguntaOpcion'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                    //$reg['posicion'] = '<input title="'. $reg['posicion'] .'" id="txtPos'. $reg['idPreguntaOpcion'] .'" disabled type="text" class="form-control maskInteger text-center pos'. $reg['idPreguntaOpcion'] .'" min="1" value="'. $reg['posicion'] .'" >' ;
                    //$reg['opcion'] = '<input title="'. $reg['opcion'] .'" id="txtOpc'. $reg['idPreguntaOpcion'] .'" disabled type="text" class="form-control text-center opc'. $reg['idPreguntaOpcion'] .'" value="'. $reg['opcion'] .'" >' ;
                    //onclick="disableOption('. $reg['idPreguntaOpcion'] . ')"
                    //onchange="updOptionQuestion('. $reg['idPreguntaOpcion'] .', `POSICION`)"
                    //onchange="updOptionQuestion('. $reg['idPreguntaOpcion'] .', `OPCION`)"
                }
            }

        } else {
            $regs = [];
        }        
        
        echo json_encode($regs);
    }

    function getConditionsRegs($idPregunta = '') {

        if( !empty($idPregunta) ) {

            $contador = 1;

            $regs = $this->view_service->searchByModel('viewModel', ['idPregunta' => $idPregunta], ['imprimirSQL' => 0, 'orderBy' => 'pc.idPreguntaCondicion ASC'], 'getConditionsQuestion');
            
            if ( $regs ) {
            
                foreach ( $regs as &$reg ) {
                    $reg['pregunta'] = '<input title="'. $reg['pregunta'] .'" disabled type="text" class="form-control text-center" min="1" value="'. $reg['pregunta'] .'" >' ;
                    $reg['opcion'] = '<input title="'. $reg['opcion'] .'" disabled type="text" class="form-control text-center" value="'. $reg['opcion'] .'" >' ;
                    $reg['opciones'] = '<a onclick="edit_condition_question('. $reg['idPreguntaCat'] . ', '. $reg['idPreguntaOpcionCat'] .', '. $reg['idPregunta'] .', '. $reg['idFormularioPadre'] .', '. $reg['idPreguntaCondicion'] .')" href="javascript:void(0);" title="Editar"><i class="fa fa-edit text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_condition_question(this,'. $reg['idPreguntaCondicion'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                    $reg['idPreguntaCondicion'] = $contador;
                    
                    $contador++;
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

        if( isset($reg['longitud']) && empty($reg['longitud']) ) unset($reg['longitud']);

        if( isset($reg['posicion']) ) unset($reg['posicion']);

        if( isset($reg['opcion']) ) unset($reg['opcion']);

        if( isset($reg['idPreguntaCondicion']) ) unset($reg['idPreguntaCondicion']);

        if( isset($reg['idPreguntaOpcion']) ) unset($reg['idPreguntaOpcion']);

        if( $reg['idTipoCampo'] != 'DATE' && isset($reg['formato']) ) unset($reg['formato']);

        if( isset($reg['idPregunta']) ) {
            $idPregunta = $reg['idPregunta'];
            unset($reg['idPregunta']);
        }

        if( isset($reg['idRol']) ) {
            $roles = $reg['idRol'];
            unset($reg['idRol']);
        }
        //$this->imprimir($idPregunta);
        //$this->imprimir($reg,1);
        $result = $this->form_service->saveQuestion($reg, !empty($idPregunta) ? $idPregunta : NULL);

        if( $result['error'] == 0 ) {
            $result['idPregunta'] = !empty($idPregunta) ? $idPregunta : $result['id'];
            $result['idFormulario'] = $reg['idFormulario'];

            if( !empty($roles) ){
                foreach ($roles as $key => $rol) {
                    $valid = current( $this->form_service->search_questionRol(['idPregunta' => !empty($idPregunta) ? $idPregunta : $result['id'], 'idRol' => $rol, 'borrado' => 0]) );

                    if( empty($valid) )
                        $result = $this->form_service->saveQuestionRol(['idPregunta' => !empty($idPregunta) ? $idPregunta : $result['id'], 'idRol' => $rol], NULL);	
                    else 
                        return $this->msg_error("Combinaci\xf3n Pregunta/rol ya registrado, verifique.");
                }  
            }
        }

        echo json_encode($result);
    }

    function saveOptionsQuestion() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

		$user_data = $this->session->userdata();

        if( isset($reg['idPregunta']) && empty($reg['idPregunta']) )
            $reg['idPregunta'] = !empty($user_data['idPregunta']) ? $user_data['idPregunta'] : '';

        if( isset($reg['idPreguntaOpcion']) && !empty($reg['idPreguntaOpcion']) ) {
            $idPreguntaOpcion = $reg['idPreguntaOpcion'];
            unset($reg['idPreguntaOpcion']);
        }

        $result = $this->form_service->saveOptionQuestion($reg, !empty($idPreguntaOpcion) ? $idPreguntaOpcion : NULL);

        echo json_encode($result);
    }

    function saveQuestionCondition() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        if( isset($reg['idPreguntaCondicion']) && !empty($reg['idPreguntaCondicion']) ) {
            $idPreguntaCondicion = $reg['idPreguntaCondicion'];
            unset($reg['idPreguntaCondicion']);
        }

        $result = $this->form_service->saveQuestionCondition($reg, !empty($idPreguntaCondicion) ? $idPreguntaCondicion : NULL);

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
        
        $conditions = $this->form_service->indexed_search_conditionQuestion(['idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

        $showFieldsQuestion = $this->view_service->indexedSearchByModel(
            'viewModel',
            ['idPregunta', 'idPreguntaOpcion', 'idPreguntaMostrar'],
            [],
            ['imprimirSQL' => 0],
            FALSE,
            'showFieldsQuestion'
        );
        
        //$this->imprimir($showFieldsQuestion,1);

        $html .= '  <div class="row mb-2">';

        foreach( $questions as $key => $q ) {
            
            $html .= ' <div id="p_'. $q['idPregunta'] .'" style="display:'. ((isset($conditions[$q['idPregunta']]) && !empty($conditions[$q['idPregunta']])) ? 'none' : '') .';" class="col-md-8 offset-md-2 mb-2">
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

            } elseif( $q['cveField'] == 'CHECKBOX') {//!empty($showFieldsQuestion[$q['idPregunta']]) ?  current($showFieldsQuestion[$q['idPregunta']])['idPreguntaMostrar'] : ''

                
            } elseif( $q['cveField'] == 'LIST') {

                $html .= '<select id="s_'. $q['idPregunta'] .'" class="form-select form-control" name="flexRadioDefault" onChange="showFieldPreview(this , '. $q['idPregunta'] .')">';

                $html .= '<option value=""> - Elige - </option>';

                foreach( $options as $optKey => $optValue ) {

                    if( !empty($optValue[$q['idPregunta']]) ) {                        
                        $html .= '<option data-cond="'. (!empty($showFieldsQuestion[$q['idPregunta']][$optValue[$q['idPregunta']]['idPreguntaOpcion']]) ? current($showFieldsQuestion[$q['idPregunta']][$optValue[$q['idPregunta']]['idPreguntaOpcion']])['idPreguntaMostrar'] : '' ) .'"  value="'. $optValue[$q['idPregunta']]['idPreguntaOpcion'] .'">'. $optValue[$q['idPregunta']]['opcion'] .'</option>';
                    }            
                }

                $html .= '</select>';


                
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

    function configuration($idFormulario = '') {

        $configuracion = [];
        $restricciones = '';

        if( !empty($idFormulario) ){
            
            $formulario = current( $this->form_service->search(['id' => $idFormulario]) );
            
            if( !$formulario ) redirect('formulario');
            
            $preguntas = $this->view_service->indexedSearchByModel(
                'viewModel',
                ['idPregunta'],
                ['idFormulario' => $idFormulario],
                ['imprimirSQL' => 0, 'orderBy' => 'p.consecutivo ASC'],
                FALSE,
                'getQuestions'
            );

            $options = $this->form_service->indexed_search_OptionForm(['idPreguntaOpcion','idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

            $conditions = $this->form_service->indexed_search_conditionQuestion(['idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

            $showFieldsQuestion = $this->view_service->indexedSearchByModel(
                'viewModel',
                ['idPreguntaMostrar'],
                [],
                ['imprimirSQL' => 0],
                FALSE,
                'showFieldsQuestion'
            );

            $listaPreguntas = [];

            foreach($preguntas as &$pregunta){

                foreach( $options as $optKey => $optValue ) {
                    if( !empty($optValue[$pregunta['idPregunta']]) )
                        $pregunta['opciones'][$optValue[$pregunta['idPregunta']]['idPreguntaOpcion']] =  $optValue[$pregunta['idPregunta']]['opcion'];
                }

                $atributos = !empty($showFieldsQuestion[$pregunta['idPregunta']]) ? $showFieldsQuestion[$pregunta['idPregunta']] : [];
                     
                $listaPreguntas[$pregunta['idPregunta']] = $pregunta;
                $listaPreguntas[$pregunta['idPregunta']]['condicion'] = !empty($atributos) ? [$atributos['idPreguntaOpcion'] => $atributos['idPreguntaCondicion']] : [];
                                           
                if( !empty($atributos) ){
                    
                    $restriccion = [];
                    
                    foreach( $listaPreguntas[$pregunta['idPregunta']]['condicion'] as $cond ){

                        $datos = current(  $this->view_service->searchByModel('viewModel', ['idPreguntaCondicion' => $cond], ['imprimirSQL' => 0], 'showFieldsQuestion') );
                        $comparacion = ($datos['igual'] == 1) ? '==' : '!=';
                        $atributos_preg2 = $preguntas[$datos['idPregunta']];

                        if( $atributos_preg2['cveField'] == 'LIST' || $atributos_preg2['cveField'] == 'LIST_MULTIPLE' )
                            $restriccion[] = "validar_grupo_pregunta( \"select[name^='reg\\[".$datos['idPregunta']."\\]']\" , '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        else
                            $restriccion[] = "validar_grupo_pregunta( \"input[name^='reg\\[".$datos['idPregunta']."\\]']:checked\", '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        
                        $listaPreguntas[$datos['idPregunta']]['change'][] = "validar_".$pregunta['idPregunta']."();";
                        $listaPreguntas[$pregunta['idPregunta']]['dependencias'][] = "div_dep_".$datos['idPregunta'];

                    }

                    $restricciones .= "function validar_".$pregunta['idPregunta'] ."(){ ";

                    $restricciones .= "if( ". implode(" && ", $restriccion) . "){";
                    $restricciones .= "$( '.div_preg_".$pregunta['idPregunta']."' ).show('fast');";
                    $restricciones .= '}else{';
                    $restricciones .= "ocultar_campo('".$pregunta['idPregunta']."');";
                    $restricciones .= '}';

                    $restricciones .= " } ";

                    $listaPreguntas[$pregunta['idPregunta']]['display'] = 'none';

                } else {
                    $listaPreguntas[$pregunta['idPregunta']]['display'] = 'block';
                }                
            }

            $configuracion['lista_preguntas'] = $listaPreguntas;
                     
            $configuracion['restricciones'] = $restricciones;
        }


        //$this->imprimir($configuracion,1);            

        return $configuracion;
    }

    function preview($idFormulario = 0) {

        /*if( $idFormulario !== 0 ) 
            $html = $this->getQuestionsHTML($idFormulario, 0); 
        else
            $html = '';*/
        
        $configuration = $this->configuration($idFormulario);
        $configuration['title'] = 'Previsualización de Formulario';
        $configuration['idFormulario'] = $idFormulario;

        $data['fileToLoad']  = ['formulario/js/preview.js'];
        $data['main_content']  = $this->load->view('formulario/preview.html', /*[
            'questions' => $html,
            'idFormulario' => $idFormulario,
            'title' => 'Previsualización de Formulario',
        ]*/$configuration, TRUE);
        
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
            
        $this->load->view('formulario/modal-add-question.html', [
            'posicion' => $numQuestion + 1,
            'posicionOption' => 1,
            'idFormulario' => $reg['idFormulario'],
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

    function getPositionOptionQuestion() {

        $data = [];
        $reg = $this->input->post();

        $getOptionsQuestion = count($this->form_service->search_OptionForm(['idPregunta' => $reg['idPregunta'], 'vigente' => '1'], ['orderBy' => 'posicion ASC']) );

        for($i=1; $i <= (isset($reg['posicion']) ? $getOptionsQuestion : ($getOptionsQuestion + 1)); $i++){
            $data[] = ['id' => $i, 'valor' => $i];
        }
                
        $options = $this->catalogo_service->get_list_to_select(
            [
                'index_id' => 'id',
                'id_reg' => isset($reg['posicion']) ? $reg['posicion'] : ($getOptionsQuestion + 1),
                'index_desc' => 'valor',
                'regs' => $data,
                'con_etiqueta' => false,
            ]
        );

        echo $options;
    }

    function cat_positionsOptionsQuestion(){

        $reg = $this->input->post();

        $questionOptions = $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario'], 'idPreguntaNot' => $reg['idPreguntaPadre'], 'option_IN' => "'LIST', 'LIST_MULTIPLE', 'RADIO', 'CHECKBOX'"], ['imprimirSQL' => 0], 'getQuestions');
        
        $listaPregunta = $this->form_service->get_list_to_select(
            [
                'index_id' => 'idPregunta',
                'id_reg' => $reg['idPregunta'],
                'index_desc' => 'etiqueta',
                'regs' => $questionOptions,
				'etiqueta' => ' - Opción - ',
            ]
        );

        echo $listaPregunta;
    }

    function cat_questions(){

        $reg = $this->input->post();

        $questionOptions = $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario'], 'idPreguntaNot' => $reg['idPreguntaPadre'], 'option_IN' => "'LIST', 'LIST_MULTIPLE', 'RADIO', 'CHECKBOX'"], ['imprimirSQL' => 0], 'getQuestions');
        
        $listaPregunta = $this->form_service->get_list_to_select(
            [
                'index_id' => 'idPregunta',
                'id_reg' => $reg['idPregunta'],
                'index_desc' => 'etiqueta',
                'regs' => $questionOptions,
				'etiqueta' => ' - Opción - ',
            ]
        );

        echo $listaPregunta;
    }

    function cat_optionsQuestion(){

        $reg = $this->input->post();

		$getOptionsQuestion = $this->form_service->search_OptionForm(['idPregunta' => $reg['idPregunta'], 'vigente' => '1'], ['orderBy' => 'posicion ASC']);
        
        $options = $this->form_service->get_list_to_select(
            [
                'index_id' => 'idPreguntaOpcion',
                'id_reg' => !empty($reg['idPreguntaOpcion']) ? $reg['idPreguntaOpcion'] : '',
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
        $questionOptions = $this->view_service->searchByModel('viewModel', ['idFormulario' => $reg['idFormulario'], 'idPreguntaNot' => $question['idPregunta'], 'option_IN' => "'LIST', 'LIST_MULTIPLE', 'RADIO', 'CHECKBOX'"], ['imprimirSQL' => 0], 'getQuestions');
        //$this->imprimir($numQuestion,1);    
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

    function delete($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->form_service->delete($idRegistro, NULL, $reg, 'DELETE');

        echo json_encode($result);  
	}

    function deleteQuestion($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->form_service->deleteQuestion($idRegistro, NULL, $reg, 'DELETE_QUESTION');

        echo json_encode($result);  
	}

    function deleteOptionQuestion($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->form_service->deleteOptionQuestion($idRegistro, NULL, $reg, 'DELETE_OPTION_QUESTION');

        echo json_encode($result);  
	}

    function deleteConditionQuestion($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->form_service->deleteConditionQuestion($idRegistro, NULL, $reg, 'DELETE_CONDITION_QUESTION');

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

    function configLevelQuestion() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        if( $reg['context'] == 'UP' && $reg['posicion'] == 1 ) 
            return $this->msg_error("No puede cambiar de posici\xF3n la pregunta.");

        if( $reg['context'] == 'UP' ) {
            
            $positionSearch = $reg['posicion'] - 1;
            
            $position = current( $this->form_service->search_questions(['consecutivo' => $positionSearch, 'idFormulario' => $reg['idFormulario']]) );

            $this->form_service->saveQuestion(['consecutivo' => $position['consecutivo'] + 1], $position['idPregunta']);

            $result = $this->form_service->saveQuestion(['consecutivo' => $positionSearch], $reg['idPregunta']); 
       
        } else {

            $positionSearch = $reg['posicion'] + 1;
            
            $position = current( $this->form_service->search_questions(['consecutivo' => $positionSearch, 'idFormulario' => $reg['idFormulario']]) );

            if( !$position )
                return $this->msg_error('No hay posiciones disponibles.');

            $this->form_service->saveQuestion(['consecutivo' => $position['consecutivo'] - 1], $position['idPregunta']);

            $result = $this->form_service->saveQuestion(['consecutivo' => $positionSearch], $reg['idPregunta']); 

        }

        echo json_encode($result);        
    }

    function configLevelOption() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        if( $reg['context'] == 'UP' && $reg['posicion'] == 1 ) 
            return $this->msg_error("No puede cambiar de posici\xF3n la opci\xF3n.");

        if( $reg['context'] == 'UP' ) {
            
            $positionSearch = $reg['posicion'] - 1;
            
            $position = current( $this->form_service->search_OptionForm(['posicion' => $positionSearch, 'idPregunta' => $reg['idPregunta']], ['imprimirSQL' => 0]) );

            //$this->imprimir($position,1);

            $this->form_service->saveOptionQuestion(['posicion' => $position['posicion'] + 1], $position['idPreguntaOpcion']);

            $result = $this->form_service->saveOptionQuestion(['posicion' => $positionSearch], $reg['idPreguntaOpcion']); 
       
        } else {

            $positionSearch = $reg['posicion'] + 1;
            
            $position = current( $this->form_service->search_OptionForm(['posicion' => $positionSearch, 'idPregunta' => $reg['idPregunta']]) );

            if( !$position )
                return $this->msg_error('No hay posiciones disponibles.');

            $this->form_service->saveOptionQuestion(['posicion' => $position['posicion'] - 1], $position['idPreguntaOpcion']);

            $result = $this->form_service->saveOptionQuestion(['posicion' => $positionSearch], $reg['idPreguntaOpcion']); 

        }

        echo json_encode($result);        
    }

    function updateOptionQuestion() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        //$this->imprimir($reg,1);

        if( isset($reg['idPreguntaOpcion']) ) {
            $idRegistro = $reg['idPreguntaOpcion'];
            unset($reg['idPreguntaOpcion']);
        }

        $result = $this->form_service->saveOptionQuestion($reg, $idRegistro);

        echo json_encode($result);
    }
    
}
