<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Patient extends MY_Controller {
    
    public function __construct() {

        parent::__construct();
       
        $this->methodByPrivilege = [
            'READ' => [],
            'ADD' => [],
            'EDIT' => [],
            'DELETE' => []            
        ];
        
        $this->validar_acceso([]);

        $this->load->library('services/patient_service');
        $this->load->library('services/historico_estatus_service');
        $this->load->library('services/form_service');
        $this->load->library('services/view_service');
    }

    public function index() {
		    
        $data['fileToLoad']  = ['patient/js/patient.js'];
        $data['main_content']  = $this->load->view('patient/patient.html', [
            'title' => 'Búsqueda de Pacientes',
        ], TRUE);
        $this->loadTemplate($data);
    }

    function ajustarParametros($filtros = []){

		if( isset($filtros['fechaIni']) && isset($filtros['fechaFin']) ){
			$fechaInicio = $this->formato_fecha_bd($filtros['fechaIni']);
			$fechaFin = $this->formato_fecha_bd($filtros['fechaFin']);

			$filtros['fechaOrdenPar'] = "'{$fechaInicio}'"." AND "."'{$fechaFin}'";
			unset($filtros['fechaIni'], $filtros['fechaFin']);
		}

		if( isset($filtros['fechaIni']) ){
			$fechaInicio = $this->formato_fecha_bd($filtros['fechaIni']);
			$filtros['fechaIniSolo'] = "'{$fechaInicio}'";
			unset($filtros['fechaIni']);
		}

        if( isset($filtros['fechaFin']) ){
			$fechaFin = $this->formato_fecha_bd($filtros['fechaFin']);
			$filtros['fechaFinSolo'] = "'{$fechaFin}'";
			unset($filtros['fechaFin']);
		}

		return $filtros;
	}

    function get_regs() {

		$user_data = $this->session->userdata();

        parse_str($this->input->post('filtros'), $filtros);

		foreach ($filtros as $idF => $f) {
			if (empty($f)) unset($filtros[$idF]);
		}

        if( !empty($filtros['idGenero']) && $filtros['idGenero'] == 'ALL' )
            unset($filtros['idGenero']);

        if( isset($user_data['idClues']) && !empty($user_data['idClues']) )
            $filtros['idClues'] = $user_data['idClues'];

        $filtros = $this->ajustarParametros($filtros);

        $regs = $this->view_service->searchByModel('viewModel', $filtros, ['imprimirSQL' => 0], 'getPatients');
        
        if ( $regs ) {
            foreach ( $regs as &$reg ) {
                $reg['nombre'] = $reg['nombre'] . ' ' . $reg['apellidos'];
                $reg['fechaNacimiento'] = $this->formato_fecha_pantalla($reg['fechaNacimiento']);
                $reg['opciones'] = ' <a href="' . URL_SITE . 'patient/edit/' . $reg['idPaciente'] . '"><i title="Editar" class="fa fa-edit text-primary"></i></a>';
                $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg(this,'. $reg['idPaciente'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
            }
        }
        
        echo json_encode($regs);
    }

    function get_regs_phone($idPaciente = 0) {

        if( !empty($idPaciente) ) {
            
            $regs = $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getContacts');
            
            if ( $regs ) {
                foreach ( $regs as &$reg ) {
                    $reg['principal'] = $reg['principal'] == 1 ? 'Si' : 'No';
                    $reg['opciones'] = ' <a href="javascript:void(0);" onclick="showModalContact('. $reg['idContacto'] . ')"><i title="Editar" class="fa fa-edit text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg_contact(this,'. $reg['idContacto'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                }
            }
        } else {
            $regs = [];
        }
        
        echo json_encode($regs);

    }

    function get_regs_responsible($idPaciente = 0) {

        if( !empty($idPaciente) ) {
            
            $regs = $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getResponsible');
            
            if ( $regs ) {
                foreach ( $regs as &$reg ) {
                    $reg['opciones'] = ' <a href="javascript:void(0);" onclick="showModalResponsible('. $reg['idResponsable'] . ')"><i title="Editar" class="fa fa-edit text-primary"></i></a>';
                    $reg['opciones'] .= ' <span>|</span> <a href="javascript:void(0);" onclick="delete_reg_responsible(this,'. $reg['idResponsable'] . ')"><i title="Eliminar" class="fa fa-trash-alt text-danger"></i></a>';
                }
            }
        } else {
            $regs = [];
        }
        
        echo json_encode($regs);
    }

    function get_visits($idPaciente = 0) {

        if( !empty($idPaciente) ) {

            $regs = $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 1, 'orderBy' => 'f.idFormulario ASC'], 'getVisits');
            $response = $this->patient_service->indexed_search_response(['idFormulario','idVisita','idRespuesta'],['idPaciente' => $idPaciente, 'borrado' => 0], ['imprimirSQL' => 0]);   
            $forms = $this->form_service->indexed_search(['idFormulario'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);   
            
            if ( $regs ) {
                foreach ( $regs as &$reg ) {

                    $countQuestion = count( $this->form_service->search_questions(['idFormulario' => $reg['idFormulario'], 'activo' => 1, 'borrado' => 0], [ 'imprimirSQL' => 0]) );
                    $searxhIds = !empty($response[$reg['idFormulario']][$reg['idVisita']]) ? $response[$reg['idFormulario']][$reg['idVisita']] : [];
                    $countResponse = $forms[$reg['idFormulario']]['clave'] == 'DT-DEMOGRAFICOS' ? (count($searxhIds) + 6) :  count($searxhIds);

                    $reg['avance'] = '<span class="avance">' . number_format((($countResponse / $countQuestion) * 100)) . '%' . '</span>';

                    if( !empty($searxhIds) ) {

                        $responses_ids = implode(',', array_column($searxhIds, 'idRespuesta'));
                        $historic = current( $this->historico_estatus_service->search(['tabla' => 'respuesta', 'idRegistro_IN' => $responses_ids], ['orderBy' => 'fecha DESC', 'imprimirSQL' => 0]) );   
                        $user = current( $this->usuario_service->search(['id' => $historic['idUsuario']], [ 'imprimirSQL' => 0]) );   
                        $person = current( $this->person_service->search(['id' => $user['idPersona']], [ 'imprimirSQL' => 0]) );
                        $reg['usuario'] = $person['nombre'] . ' ' . $person['apellidos'];  
                        
                        $fecha = $this->formato_fecha_pantalla($historic['fecha'], 10);
                        $hora = substr($historic['fecha'], 11, 5);
                        $reg['fechaHora'] = $fecha . ' ' . $hora;
                        $reg['fechaHoraPlus'] = $historic['fecha'];
                    } else {
                        $reg['usuario'] = '';
                        $reg['fechaHora'] = '';
                        $reg['fechaHoraPlus'] = '';
                    }
                    
                    $reg['opciones'] = ' <a href="' . URL_SITE . 'patient/editForm/' . $reg['idFormulario'] . '/'. $reg['idVisita'] .'/'. $reg['idPaciente'] .'"><i title="Editar" class="fa fa-edit text-primary"></i></a>';
                }
            }
        } else {
            $regs = [];
        }
        
        echo json_encode($regs);
    }

    function get_regs_binnacle($idPaciente = 0) {

        if( !empty($idPaciente) ) {

            $responses = $this->patient_service->search_response(['idPaciente' => $idPaciente, 'borrado' => '0']);
            
            $response_ids = implode(',', array_column($responses, 'idRespuesta'));

            $regsRegistered = $this->view_service->searchByModel('viewModel', ['idRegistro_IN' => $response_ids, 'cveEstatus' => 'REGISTERED_RESPONSE'], ['imprimirSQL' => 0, 'getBy' => '1'], 'get_regs_binnacle');
            
            $regs = $this->view_service->searchByModel('viewModel', ['idRegistro_IN' => $response_ids, 'comentarioISNULL' => '1'], ['imprimirSQL' => 0], 'get_regs_binnacle');

            $data = array_merge($regsRegistered, $regs);
      
            if ( $data ) {
                foreach ( $data as &$reg ) {

                    $fecha = $this->formato_fecha_pantalla($reg['fecha'], 10);
                    $hora = substr($reg['fecha'], 11, 5);
                    $reg['fechaHora'] = $fecha . ' ' . $hora;
                    $reg['observacion'] = $reg['comentario'];
                }
            }

        } else {
            $data = [];
        }
       
        echo json_encode($data);
    }

    function saveContact() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        if( isset($reg['idContacto']) && !empty($reg['idContacto']) ) {
            $idContacto = $reg['idContacto'];
            unset($reg['idContacto']);
        } else {
            if( empty($reg['idContacto']) )
                unset($reg['idContacto']);
        }

        if( empty($idContacto) ) {
            $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $reg['idPaciente']], ['imprimirSQL' => 0], 'getPatients') );
            $reg['idx'] = $patient['idx'];
        }
        unset($reg['idPaciente']);
        //cveContact
        $contactMedioId = current( $this->catalogo_service->search('cat_medio_contacto', ['clave' => $reg['cveContact']]) );
        $reg['idMedioContacto'] = $contactMedioId['idMedioContacto'];
        $reg['etiqueta'] = $contactMedioId['nombre'];
        unset($reg['cveContact']);

        $result = $this->patient_service->saveContact($reg, !empty($idContacto) ? $idContacto : NULL);

        echo json_encode($result);
    }

    function saveResponsible() {

        if(!empty($this->input->post('reg'))){
            $reg = $this->input->post('reg');
        } else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
        }

        if( isset($reg['idResponsable']) && !empty($reg['idResponsable']) ) {
            $idResponsable = $reg['idResponsable'];
            unset($reg['idResponsable']);
        }
        
        if( empty($idResponsable) ) {
            $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $reg['idPaciente']], ['imprimirSQL' => 0], 'getPatients') );
            $reg['idx'] = $patient['idx'];
        }

        unset($reg['idPaciente']);

        $result = $this->patient_service->saveResponsible($reg, !empty($idResponsable) ? $idResponsable : NULL);

        echo json_encode($result);
    }

    function add($idPaciente = 0, $tab_initial = ''){ 

        $demograficos = current( $this->form_service->search(['clave' => 'DT-DEMOGRAFICOS', 'vigente' => '1']) );
        $mod_ap = current( $this->form_service->search(['clave' => 'MOD-AP', 'vigente' => '1']) );

        if( $idPaciente != 0 && $tab_initial != '' ) {
            $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getPatients') );

            if( !empty($demograficos) ) {
                $configurationDemo = $this->configurationEdit($demograficos['idFormulario'], $idPaciente);
                $configurationDemo['idFormulario'] = $demograficos['idFormulario'];
                $configurationDemo['context'] = 'dataDemograficos';
                $configurationDemo['flag'] = '1';
                $configurationDemo['patient'] = $patient;
                $configurationDemo['action'] = 'formDemograficosEdit';
                $configurationDemo['reg'] = ['idPaciente' => $idPaciente];
            } else {
                $configurationDemo = array();
            }
    
            if( !empty($mod_ap) ) {
                $configurationAP = $this->configurationEdit($mod_ap['idFormulario'], $idPaciente);
                $configurationAP['idFormulario'] = $mod_ap['idFormulario'];
                $configurationAP['context'] = $mod_ap['clave'];
                $configurationAP['flag'] = '1';
                $configurationAP['patient'] = $patient;
            } else {
                $configurationAP = array();
            }

        } else {

            if( !empty($demograficos) ) {
                $configurationDemo = $this->configuration($demograficos['idFormulario']);
                $configurationDemo['idFormulario'] = $demograficos['idFormulario'];
                $configurationDemo['context'] = 'dataDemograficos';
                $configurationDemo['flag'] = '1';
                $configurationDemo['action'] = 'formDemograficosAdd';
            } else {
                $configurationDemo = array();
            }
    
            if( !empty($mod_ap) ) {
                $configurationAP = $this->configuration($mod_ap['idFormulario']);
                $configurationAP['idFormulario'] = $mod_ap['idFormulario'];
                $configurationAP['context'] = $mod_ap['clave'];
                $configurationAP['flag'] = '1';
            } else {
                $configurationAP = array();
            }

        }
        
        $data['fileToLoad']  = ['patient/js/add.js', 'formulario/js/preview.js'];
        $data['main_content']  = $this->load->view('patient/add.html', [
            'title' => $idPaciente == 0 ? 'Registro de Paciente' : 'Paciente: ' . $patient['nombre'] . ' ' . $patient['apellidos'],
            'patient' => !empty($patient) ? $patient : [],
            'tab_initial' => $tab_initial,
            'idPaciente' => $idPaciente,
            'demograficos' => !empty($configurationDemo) ? $this->load->view('formulario/preview.html', $configurationDemo, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>',
            'mod_ap' => !empty($configurationAP) ? $this->load->view('formulario/preview.html', $configurationAP, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>'
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    /*function edit($idPaciente = 0, $tab_initial = ''){ 

        $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getPatients') );

        $demograficos = current( $this->form_service->search(['clave' => 'DT-DEMOGRAFICOS', 'vigente' => '1']) );
        $mod_ap = current( $this->form_service->search(['clave' => 'MOD-AP', 'vigente' => '1']) );

        if( !empty($demograficos) ) {
            $configurationDemo = $this->configurationEdit($demograficos['idFormulario'], $idPaciente);
            $configurationDemo['idFormulario'] = $demograficos['idFormulario'];
            $configurationDemo['context'] = 'dataDemograficos';
            $configurationDemo['flag'] = '1';
            $configurationDemo['patient'] = $patient;
            $configurationDemo['action'] = 'formDemograficosEdit';
            $configurationDemo['reg'] = ['idPaciente' => $idPaciente];
        } else {
            $configurationDemo = array();
        }

        if( !empty($mod_ap) ) {
            $configurationAP = $this->configurationEdit($mod_ap['idFormulario'], $idPaciente);
            $configurationAP['idFormulario'] = $mod_ap['idFormulario'];
            $configurationAP['context'] = $mod_ap['clave'];
            $configurationAP['flag'] = '1';
            $configurationAP['patient'] = $patient;
        } else {
            $configurationAP = array();
        }
        
        $data['fileToLoad']  = ['patient/js/edit.js', 'formulario/js/preview.js'];
        $data['main_content']  = $this->load->view('patient/edit.html', [
            'title' => 'Paciente: ' . $patient['nombre'] . ' ' . $patient['apellidos'],
            'patient' => $patient,
        	'tab_initial' => $tab_initial,
            'demograficos' => !empty($configurationDemo) ? $this->load->view('formulario/preview.html', $configurationDemo, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>',
            'mod_ap' => !empty($configurationAP) ? $this->load->view('formulario/preview.html', $configurationAP, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>'
        ], TRUE);
        
        $this->loadTemplate($data);
    }*/

    function newVisit($idPaciente = 0, $actionAD = ''){ 

        $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getPatients') );

        $newVisit = current( $this->form_service->search(['clave' => 'DATA_AD', 'vigente' => '1']) );
        
        $numVisit = count( $this->patient_service->search_visit(['idPaciente' => $idPaciente]) );

        $numVisit = $numVisit + 1;

        if( !empty($newVisit) ) {
            $configurationVisit = $this->configuration($newVisit['idFormulario']);
            $configurationVisit['idFormulario'] = $newVisit['idFormulario'];
            $configurationVisit['idPaciente'] = $idPaciente;
            $configurationVisit['context'] = $newVisit['clave'];
            $configurationVisit['flag'] = '1';
            $configurationVisit['return'] = $actionAD;
            $configurationVisit['dataForm'] = ['nombre' => 'Visita ' . $numVisit . ': ' . $newVisit['nombre']];
            $configurationVisit['action'] = 'addAD';
            $configurationVisit['actionAD'] = $actionAD;
        } else {
            $configurationVisit = array();
        }
        
        $data['fileToLoad']  = ['patient/js/newVisit.js', 'formulario/js/preview.js'];
        $data['main_content']  = $this->load->view('patient/newVisit.html', [
            'title' => 'Paciente: ' . $patient['nombre'] . ' ' . $patient['apellidos'],
            'idPaciente' => $idPaciente,
            'newVisit' => !empty($configurationVisit) ? $this->load->view('formulario/preview.html', $configurationVisit, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>',
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function editForm($idFormulario = 0, $idVisita = 0, $idPaciente = 0){ 

        $form = current( $this->form_service->search(['id' => $idFormulario, 'vigente' => '1']) );
        
        $visit = current( $this->patient_service->search_visit(['id' => $idVisita, 'vigente' => '1']) );

        $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getPatients') );

        if( !empty($form) ) {
            $configurationVisitEdit = $this->configurationEdit($form['idFormulario'], $idPaciente, $idVisita);
            $configurationVisitEdit['idFormulario'] = $form['idFormulario'];
            $configurationVisitEdit['idPaciente'] = $idPaciente;
            $configurationVisitEdit['context'] = $form['clave'];
            $configurationVisitEdit['flag'] = '1';
            $configurationVisitEdit['return'] = '1';
            $configurationVisitEdit['dataForm'] = ['nombre' => 'Visita ' . $visit['numVisita'] . ': ' . $form['nombre']];
            $configurationVisitEdit['idVisita'] = $idVisita;
            $configurationVisitEdit['action'] = 'editForm';
        } else {
            $configurationVisitEdit = array();
        }
        
        $data['fileToLoad']  = ['patient/js/edit.js', 'formulario/js/preview.js'];
        $data['main_content']  = $this->load->view('patient/editForm.html', [
            'title' => 'Paciente: ' . $patient['nombre'] . ' ' . $patient['apellidos'],
            'editForm' => !empty($configurationVisitEdit) ? $this->load->view('formulario/preview.html', $configurationVisitEdit, TRUE) : '<h3 class="text-center mt-3">Formulario No disponible</h3>',
        ], TRUE);
        
        $this->loadTemplate($data);
    }

    function configuration($idFormulario = '') {

        $configuracion = [];
        $restricciones = '';

        if( !empty($idFormulario) ){
            
            $formulario = current( $this->form_service->search(['id' => $idFormulario]) );
            
            if( !$formulario ) redirect('formulario');
            
            $preguntas = $this->view_service->indexedSearchByModel('viewModel', ['idPregunta'],['idFormulario' => $idFormulario], ['imprimirSQL' => 0, 'orderBy' => 'p.consecutivo ASC'], FALSE, 'getQuestions');

            $options = $this->form_service->indexed_search_OptionForm(['idPreguntaOpcion','idPregunta'],['activo' => 1, 'borrado' => 0], ['orderBy' => 'posicion ASC', 'imprimirSQL' => 0]);

            $conditions = $this->form_service->indexed_search_conditionQuestion(['idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

            $questions_ids = implode(',', array_column($preguntas, 'idPregunta'));

            $showFieldsQuestion = $this->view_service->indexedSearchByModel('viewModel', ['idPreguntaOpcion'], ['idPregunta_IN' => $questions_ids], ['imprimirSQL' => 0], FALSE, 'showFieldsQuestion');

            $listaPreguntas = [];

            foreach($preguntas as &$pregunta){

                foreach( $options as $optKey => $optValue ) {
                    if( !empty($optValue[$pregunta['idPregunta']]) )
                        $pregunta['opciones'][$optValue[$pregunta['idPregunta']]['idPreguntaOpcion']] =  $optValue[$pregunta['idPregunta']]['opcion'];
                }

                $atributos = $this->view_service->searchByModel('viewModel', ['idPregunta' => $pregunta['idPregunta']], ['imprimirSQL' => 0], 'showFieldsQuestion');
                     
                $listaPreguntas[$pregunta['idPregunta']] = $pregunta;
                
                foreach( $atributos as $atr ) {
                    $listaPreguntas[$pregunta['idPregunta']]['condicion'][] = !empty($atributos) ? [$atr['idPreguntaOpcion'] => $atr['idPreguntaCondicion']] : [];
                }
                                           
                if( !empty($atributos) ){
                    
                    $restriccion = [];
                    
                    foreach( $listaPreguntas[$pregunta['idPregunta']]['condicion'] as $cond ){

                        $datos = current(  $this->view_service->searchByModel('viewModel', ['idPreguntaCondicion' => current($cond)], ['imprimirSQL' => 0], 'showFieldsQuestion') );
                        //$this->imprimir($datos,1);
                        $comparacion = ($datos['igual'] == 1) ? '==' : '!=';
                        $atributos_preg2 = !empty($preguntas[$datos['idPregunta']]) ? $preguntas[$datos['idPregunta']] : [];

                        if( isset($atributos_preg2['cveField']) && $atributos_preg2['cveField'] == 'LIST' || isset($atributos_preg2['cveField']) && $atributos_preg2['cveField'] == 'LIST_MULTIPLE' )
                            $restriccion[] = "validar_grupo_pregunta( \"select[name^='reg\\[".$datos['idPregunta']."\\]']\" , '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        else
                            $restriccion[] = "validar_grupo_pregunta( \"input[name^='reg\\[".$datos['idPregunta']."\\]']:checked\", '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        
                        $listaPreguntas[$datos['idPregunta']]['change'][] = "validar_".$pregunta['idPregunta']."();";
                        $listaPreguntas[$pregunta['idPregunta']]['dependencias'][] = "div_dep_".$datos['idPregunta'];
                    }

                    $restricciones .= "function validar_".$pregunta['idPregunta'] ."(){ ";
                    $restricciones .= "if( ". implode(" || ", $restriccion) . "){";
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

            //$this->imprimir($listaPreguntas,1);            
            $configuracion['lista_preguntas'] = $listaPreguntas;
            $configuracion['restricciones'] = $restricciones;
        }

        //$this->imprimir($configuracion,1);            

        return $configuracion;
    }

    function configurationEdit($idFormulario = '', $idPaciente = NULL, $idVisita = NULL) {

        $configuracion = [];
        $restricciones = '';

        if( !empty($idPaciente) )
            $patient = current( $this->view_service->searchByModel('viewModel', ['idPaciente' => $idPaciente], ['imprimirSQL' => 0], 'getPatients') );

        if( !empty($idFormulario) ){
            
            $formulario = current( $this->form_service->search(['id' => $idFormulario]) );
            
            if( !$formulario ) redirect('formulario');
            
            $preguntas = $this->view_service->indexedSearchByModel('viewModel', ['idPregunta'],['idFormulario' => $idFormulario], ['imprimirSQL' => 0, 'orderBy' => 'p.consecutivo ASC'], FALSE, 'getQuestions');

            $options = $this->form_service->indexed_search_OptionForm(['idPreguntaOpcion','idPregunta'],['activo' => 1, 'borrado' => 0], ['orderBy' => 'posicion ASC', 'imprimirSQL' => 0]);

            $conditions = $this->form_service->indexed_search_conditionQuestion(['idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);

            $questions_ids = implode(',', array_column($preguntas, 'idPregunta'));

            $showFieldsQuestion = $this->view_service->indexedSearchByModel('viewModel', ['idPreguntaOpcion'], ['idPregunta_IN' => $questions_ids], ['imprimirSQL' => 0], FALSE, 'showFieldsQuestion');

            $responses = $this->patient_service->indexed_search_response(['idPregunta'],['idFormulario' => $idFormulario, 'idPaciente' => $idPaciente, 'idVisita' => $idVisita, 'borrado' => 0], ['imprimirSQL' => 0]);

            $listaPreguntas = [];

            foreach($preguntas as &$pregunta){

                foreach( $options as $optKey => $optValue ) {
                    if( !empty($optValue[$pregunta['idPregunta']]) )
                        $pregunta['opciones'][$optValue[$pregunta['idPregunta']]['idPreguntaOpcion']] =  $optValue[$pregunta['idPregunta']]['opcion'];
                }

                $atributos = $this->view_service->searchByModel('viewModel', ['idPregunta' => $pregunta['idPregunta']], ['imprimirSQL' => 0], 'showFieldsQuestion');
                     
                $listaPreguntas[$pregunta['idPregunta']] = $pregunta;

                if( $pregunta['etiqueta'] == 'Nombre' && !empty($patient) )
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $patient['nombre'];

                if( $pregunta['etiqueta'] == 'Apellido' && !empty($patient) )
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $patient['apellidos'];

                if( trim($pregunta['etiqueta']) == 'Fecha nacimiento' && !empty($patient) )
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $patient['fechaNacimiento'];

                if( trim($pregunta['etiqueta']) == 'Tipo documento' && !empty($patient) ) {
                    $clasificacion = current( $this->catalogo_service->search('clasificacion', ['id' => $patient['idTipoDocumento'], 'activo' => 1,'borrado' => 0], ['imprimirSQL' => 0]) );
                    $optionValue = current( $this->form_service->search_OptionForm(['opcion' => $clasificacion['nombre'], 'vigente' => '1']) );
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $optionValue['idPreguntaOpcion'];
                }

                if( trim($pregunta['etiqueta']) == 'Número documento' && !empty($patient) )
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $patient['numDocumento'];

                if( trim($pregunta['etiqueta']) == 'Lugar de residencia' && !empty($patient) ) {
                    $optionValue = current( $this->form_service->search_OptionForm(['opcion' => $patient['direccion'], 'vigente' => '1']) );
                    $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $optionValue['idPreguntaOpcion'];
                }

                if( trim($pregunta['etiqueta']) == 'CLUES' && !empty($patient) ) {

                    $responseClues = current( $this->patient_service->search_response(['idPregunta' => $pregunta['idPregunta'], 'idPaciente' => $idPaciente, 'borrado' => '0']) );
                    $cluesData = current( $this->catalogo_service->search('clues', ['id' => $responseClues['idPreguntaOpcion'], 'activo' => 1,'borrado' => 0], ['imprimirSQL' => 0]) );

                    $configuracion['cluesId'] = $cluesData['idClues'];
                    $configuracion['cluesName'] = $cluesData['nombre'];
                }

                //Adjuntar respuestas
                if( !empty($responses[$pregunta['idPregunta']]) ) {

                    if(  !empty($responses[$pregunta['idPregunta']]['respuesta']) )
                        $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $responses[$pregunta['idPregunta']]['respuesta'];
                    else
                        $listaPreguntas[$pregunta['idPregunta']]['respuesta'] = $responses[$pregunta['idPregunta']]['idPreguntaOpcion'];
                }

                foreach( $atributos as $atr ) {
                    $listaPreguntas[$pregunta['idPregunta']]['condicion'][] = !empty($atributos) ? [$atr['idPreguntaOpcion'] => $atr['idPreguntaCondicion']] : [];
                }
                                           
                if( !empty($atributos) ){
                    
                    $restriccion = [];
                    
                    foreach( $listaPreguntas[$pregunta['idPregunta']]['condicion'] as $cond ){

                        $datos = current(  $this->view_service->searchByModel('viewModel', ['idPreguntaCondicion' => current($cond)], ['imprimirSQL' => 0], 'showFieldsQuestion') );
                        //$this->imprimir($datos,1);
                        $comparacion = ($datos['igual'] == 1) ? '==' : '!=';
                        $atributos_preg2 = !empty($preguntas[$datos['idPregunta']]) ? $preguntas[$datos['idPregunta']] : [];

                        if( isset($atributos_preg2['cveField']) && $atributos_preg2['cveField'] == 'LIST' || isset($atributos_preg2['cveField']) && $atributos_preg2['cveField'] == 'LIST_MULTIPLE' )
                            $restriccion[] = "validar_grupo_pregunta( \"select[name^='reg\\[".$datos['idPregunta']."\\]']\" , '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        else
                            $restriccion[] = "validar_grupo_pregunta( \"input[name^='reg\\[".$datos['idPregunta']."\\]']:checked\", '".$comparacion."', ".$datos['idPreguntaOpcion'].")";
                        
                        $listaPreguntas[$datos['idPregunta']]['change'][] = "validar_".$pregunta['idPregunta']."();";
                        $listaPreguntas[$pregunta['idPregunta']]['dependencias'][] = "div_dep_".$datos['idPregunta'];
                    }

                    $restricciones .= "function validar_".$pregunta['idPregunta'] ."(){ ";
                    $restricciones .= "if( ". implode(" || ", $restriccion) . "){";
                    $restricciones .= "$( '.div_preg_".$pregunta['idPregunta']."' ).show('fast');";
                    $restricciones .= '}else{';
                    $restricciones .= "ocultar_campo('".$pregunta['idPregunta']."');";
                    $restricciones .= '}';
                    $restricciones .= " } ";

                    if( empty($responses[$pregunta['idPregunta']]) )
                        $listaPreguntas[$pregunta['idPregunta']]['display'] = 'none';

                } else {
                    $listaPreguntas[$pregunta['idPregunta']]['display'] = 'block';
                }              
            }

            //$this->imprimir($listaPreguntas,1);            
            $configuracion['lista_preguntas'] = $listaPreguntas;
            $configuracion['restricciones'] = $restricciones;
        }

        //$this->imprimir($configuracion,1);            

        return $configuracion;
    }

    function addPhone() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        if( !empty($reg['idContacto']) ) {
            $contact = current( $this->patient_service->search_response_contacto(['id' => $reg['idContacto']]) );

            $contactMedioCve = current( $this->catalogo_service->search('cat_medio_contacto', ['id' => $contact['idMedioContacto']]) );

            $contact['clave'] = $contactMedioCve['clave'];

        } else
            $contact = [];       
            
        $this->load->view('patient/modal-add.html', [
            'contact' => $contact
        ]);
    }

    function addResponsible() {

        if( !empty($this->input->post('reg')) ) 
            $reg = $this->input->post('reg');
        else {
            $reg = $this->input->post();
            $_POST['reg'] = $reg;
		}

        if( !empty($reg['idResponsable']) )
            $responsible = current( $this->patient_service->search_response_responsable(['id' => $reg['idResponsable']]) );
        else
            $responsible = [];
  
        $this->load->view('patient/modal-add-resp.html', [
            'reg' => $reg,
            'responsible' => $responsible
        ]);
    }

    function delete($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->patient_service->delete($idRegistro, NULL, $reg, 'DELETE_PATIENT');

        echo json_encode($result);  
	}

    function deleteResponsible($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->patient_service->deleteResponsible($idRegistro, NULL, $reg);

        echo json_encode($result);  
	}

    function deleteContact($idRegistro = 0) {

        $reg = ['borrado' => 1];

        $result = $this->patient_service->deleteContact($idRegistro, NULL, $reg);

        echo json_encode($result);  
	}    
}
