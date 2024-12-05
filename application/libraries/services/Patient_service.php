<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Patient_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'viewModel' => 'view_model',
            'paciente' => 'patient_model',
            'respuesta' => 'respuesta_model',
            'medioContact' => 'medio_contacto_model',
            'responsible' => 'cat_responsable_model',
            'visit' => 'visita_model',
         );

        $this->loadModel(); 

		$this->CI->load->library('Excel');

        $this->CI->load->library('services/historico_estatus_service');
    }
    
    function search($condicion = array(), $extras = array()) {
        
        return $this->CI->patient_model->buscar($condicion, $extras);
    }

    function search_response($condicion = array(), $extras = array()) {
        
        return $this->CI->respuesta_model->buscar($condicion, $extras);
    }

    function search_response_contacto($condicion = array(), $extras = array()) {
        
        return $this->CI->medio_contacto_model->buscar($condicion, $extras);
    }

    function search_response_responsable($condicion = array(), $extras = array()) {
        
        return $this->CI->cat_responsable_model->buscar($condicion, $extras);
    }

    function search_visit($condicion = array(), $extras = array()) {
        
        return $this->CI->visita_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->patient_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }

    function indexed_search_response($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->respuesta_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
                
    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL, $statusUpdate = '') {
            
		$rules = $this->CI->patient_model->get_rules($reg, $varPostIndex);

        $action = empty($id) ? 'add' : 'update';         
              			
        $result = $this->validar_form($reg, $rules, $this->CI->patient_model, $action, $action === 'add' ? NULL : "idPaciente = '{$id}'");
                
        return $result;
    }
    
    function delete($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->patient_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->patient_model, $reg, $action, $cond ? $cond : "idPaciente = '{$id}'");
    }

    function deleteResponsible($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->cat_responsable_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->cat_responsable_model, $reg, $action, $cond ? $cond : "idResponsable = '{$id}'");
    }

    function deleteContact($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->medio_contacto_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->medio_contacto_model, $reg, $action, $cond ? $cond : "idContacto = '{$id}'");
    }

    function deleteResponse($id = NULL, $cond = NULL, $reg = [], $statusDelete = ''){

        $action = 'update';

        if ( $statusDelete ) {
            
            $this->CI->respuesta_model->setVar('cveStatusUpdate', $statusDelete);
        }

        return $this->action_on_reg($this->CI->respuesta_model, $reg, $action, $cond ? $cond : "idRespuesta = '{$id}'");
    }

    function saveResponse(array $reg, int $id = NULL) : array {

		$changedReg = 0;
        $cveFieldOption = '';

        if( isset($reg['cveFieldOption']) ) {
            $cveFieldOption = $reg['cveFieldOption'];
            unset($reg['cveFieldOption']);
        }

        $action = empty($id) ? 'add' : 'update';
		
        if($action == 'update') {

			$registroOrigen = current( $this->search_response(['id' => $id, 'borrado' => 0]) );
            
            if( !empty($registroOrigen['idVisita']))
			    $visit = current( $this->search_visit(['id' => $registroOrigen['idVisita']], ['imprimirSQL' => 0]) );
            else
                $visit = NULL;

            $changedReg = $this->checkDiferencias($reg, $registroOrigen);
            $question = current( $this->CI->form_service->search_questions(['id' => $registroOrigen['idPregunta']]) );

            $user_data = $this->CI->session->userdata();

            $status = current( $this->CI->catalogo_service->search('estatus', ['clave' => 'UPDATE_RESPONSE']) );

            $data['idUsuario'] = $user_data['idUsuario'];
            $data['idRol'] = $user_data['idRol'];
            $data['idRegistro'] = $id;
            $data['fecha'] = date('Y-m-d H:i:s');

            if( !empty($changedReg['flag']) && $question['etiqueta'] != 'CLUES' ) {
                if( $cveFieldOption == '1' ){
                    $optionAnterior = current($this->CI->form_service->search_OptionForm(['id' => $changedReg['anterior']]) );
                    $changedReg['anterior'] = $optionAnterior['opcion'];
                    $optionActual = current($this->CI->form_service->search_OptionForm(['id' => $changedReg['actual']]) );
                    $changedReg['actual'] = $optionActual['opcion'];
                }

                $data['comentario'] = 'Actualizó "'. $question['etiqueta'] .'" de "'. $changedReg['anterior'] .'" a "'. $changedReg['actual'] .'" '. (!empty($visit['numVisita']) ? ('en la visita número ' . $visit['numVisita']) : '' ) .''; 
            }

            //$this->CI->imprimir($question);
            //$this->CI->imprimir($changedReg,1);
		}

        $result = $this->saveByModel('respuesta', $reg, $id, $cond = NULL);

        if( $result['error'] == 0 && $action == 'update' && !empty($changedReg['flag']) ) {
        	$this->CI->historico_estatus_service->save($data, 'respuesta', $status['idEstatus'], '');
        }


        return $result;
    }

    function checkDiferencias($reg = [], $registroOrigen = []) {
		
		foreach ($registroOrigen as $key => $regOrigin) {
			
			if(!empty($reg[$key])) {
				if($reg[$key] != $regOrigin)
					return ['actual' => $reg[$key], 'anterior' => $regOrigin, 'flag' => 1];
			}
		}
	}

    function saveContact(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('medioContact', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveResponsible(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('responsible', $reg, $id, $cond = NULL);

        return $result;
    }

    function saveVisit(array $reg, int $id = NULL) : array {

        $result = $this->saveByModel('visit', $reg, $id, $cond = NULL);

        return $result;
    }

    function exportar_excel($filtros = []) {

        $objPHPExcel = new PHPExcel();
        $codPaciente = '';
        $questionsAP = $this->CI->form_service->search_questions(['idFormulario' => 2, 'activo' => 1, 'borrado' => 0]);
        $questions = $this->CI->form_service->indexed_search_question(['idFormulario','idPregunta'],['activo' => 1, 'borrado' => 0], ['imprimirSQL' => 0]);
        $optionsQuestion = $this->CI->form_service->indexed_search_OptionForm(['idPreguntaOpcion'],['activo' => 1, 'borrado' => 0]);   
        $responses = $this->indexed_search_response(['idFormulario', 'idPaciente', 'idPregunta', 'idRespuesta'],['borrado' => 0], ['imprimirSQL' => 0]);
        //Pestaña Datos
        $objPHPExcel->getActiveSheet()->setTitle('Datos Demográficos');
        //Pestaña Antecedentes
        $sheet = $objPHPExcel->createSheet();
        $sheet->setTitle('Antecedentes Personales');
                    
        foreach( range('A','H') as $letra ){ //Recorremos las letras que iran en nuestro titulo
            $objPHPExcel->getActiveSheet()->getColumnDimension($letra)->setAutoSize(true);
        }
        
        //Seteas los titulos
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'ID Paciente');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Género');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Fecha Nacimiento');   
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Estado Civil');   
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'CLUES');   
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Raza');   
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Lugar de Residencia');   
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Ocupación');  
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '5fa322')
                )
            )
        );

        //Valores de los datos demograficos
		$contadorValueDemo = 1;
        $patients = $this->searchByModel('viewModel', [], ['imprimirSQL' => 0], 'getPatients');
        //$this->CI->imprimir($patients,1);
        if ( $patients ) {
            foreach ( $patients as &$reg ) {
                $reg['fechaNacimiento'] = $this->CI->formato_fecha_pantalla($reg['fechaNacimiento']);

                $contadorValueDemo++;
                $codPaciente = $reg['numPaciente'];
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("A{$contadorValueDemo}", $reg['numPaciente'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue("B{$contadorValueDemo}", $reg['genero']);
                $objPHPExcel->getActiveSheet()->setCellValue("C{$contadorValueDemo}", $reg['fechaNacimiento']);
                $objPHPExcel->getActiveSheet()->setCellValue("D{$contadorValueDemo}", $reg['estadoCivil']);
                $objPHPExcel->getActiveSheet()->setCellValue("E{$contadorValueDemo}", $reg['clues']);
                $objPHPExcel->getActiveSheet()->setCellValue("F{$contadorValueDemo}", $reg['raza']);
                $objPHPExcel->getActiveSheet()->setCellValue("G{$contadorValueDemo}", $reg['direccion']);
                $objPHPExcel->getActiveSheet()->setCellValue("H{$contadorValueDemo}", $reg['ocupacion']);

            }
        }
        
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'ID Paciente');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $ultima_letra = chr(ord('B') + count($questionsAP) - 1);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '5fa322')
                )
            )
        );

        $contadorAP = 1;
        if ( $patients ) {
            foreach ( $patients as &$reg ) {
                $contadorAP++;
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("A{$contadorAP}", $reg['numPaciente'], PHPExcel_Cell_DataType::TYPE_STRING);
                
                foreach( $questionsAP as $key => $question ) {
                    $letra = chr(ord('B') + $key);
                    $objPHPExcel->getActiveSheet()->SetCellValue($letra.'1', $question['etiqueta']);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($letra)->setAutoSize(true);
                    //$this->CI->imprimir($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']],1);
                    $resp = !empty($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']]) ? ( !empty(current($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']])['respuesta'] ) ? current($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']])['respuesta'] : ( !empty($optionsQuestion[current($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']])['idPreguntaOpcion']]) ? $optionsQuestion[current($responses[$question['idFormulario']][$reg['idPaciente']][$question['idPregunta']])['idPreguntaOpcion']]['opcion'] : ''  )) : '';
                    $celda = $letra.$contadorAP;
                    $objPHPExcel->getActiveSheet()->setCellValue($celda, $resp);
                }
            }
        }

        $sheets = $this->searchByModel('viewModel', [], ['imprimirSQL' => 0, 'orderBy' => 'v.numVisita, f.idFormulario ASC', 'groupBy' => 'v.idEstudioClues, v.idVisita, f.idFormulario', 'campos' => ' DISTINCT(f.nombre) AS formulario, f.idFormulario, CONCAT(CONCAT("v",v.numVisita), "-", f.nombre) AS sheets'], 'getVisits');
        //$dataVisits = $this->searchByModel('viewModel', [], ['imprimirSQL' => 0, 'orderBy' => 'f.idFormulario ASC', 'groupBy' => 'v.idVisita, f.idFormulario'], 'getVisits');
        $dataVisits = $this->indexedSearchByModel(
            'viewModel',
            ['idPaciente', 'idVisita', 'idFormulario'],
            [],
            ['imprimirSQL' => 0, 'orderBy' => 'f.idFormulario, v.idVisita ASC', 'groupBy' => 'v.idVisita, f.idFormulario'],
            FALSE,
            'getVisits'
        );

        $contador = 2;
        //$this->CI->imprimir($sheets,1);
        foreach( $sheets as $s ) {
            $responsesSheets = $this->indexed_search_response(['idFormulario', 'idPregunta', 'idVisita'],['idVisitaIsNotNull' => '1', 'borrado' => 0], ['imprimirSQL' => 0]);
            //$this->CI->imprimir($responsesSheets,1);
            $sheet = $objPHPExcel->createSheet();
            $sheet->setTitle($s['sheets']);

            $objPHPExcel->setActiveSheetIndex($contador);
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'ID Paciente');
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $ultima_letra = chr(ord('B') + count($questions[$s['idFormulario']]) - 1);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultima_letra.'1')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '5fa322')
                    )
                )
            );

            $contadorForm = 1;
            if ( $patients ) {
                foreach ( $patients as &$reg ) {
                    $contadorForm++;
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("A{$contadorForm}", $reg['numPaciente'], PHPExcel_Cell_DataType::TYPE_STRING);

                    $cont = 0;
                    foreach( $questions[$s['idFormulario']] as $q ) {
                        $letraForm = chr(ord('B') + $cont);
                        $objPHPExcel->getActiveSheet()->SetCellValue($letraForm.'1', $q['etiqueta']);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($letraForm)->setAutoSize(true);
                        
                        //$this->CI->imprimir($dataVisits[$reg['idPaciente']],1);
                        foreach( $dataVisits[$reg['idPaciente']] as $visitId => $dv ) {

                            //$this->CI->imprimir($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']],1);
                            $resp = !empty($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']]) ? ( !empty(current($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']])['respuesta'] ) ? current($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']])['respuesta'] : ( !empty($optionsQuestion[current($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']])['idPreguntaOpcion']]) ? $optionsQuestion[current($responsesSheets[$dv[$s['idFormulario']]['idFormulario']][$q['idPregunta']][$dv[$s['idFormulario']]['idVisita']])['idPreguntaOpcion']]['opcion'] : ''  )) : '';
                            $celda = $letraForm.$contadorForm;
                            $objPHPExcel->getActiveSheet()->setCellValue($celda, $resp);
                        }     
                        
                        $cont++;
                    }
                }

            }

            $contador++;
        }

		header('Content-Type: application/vnd.ms-excel');         
		header('Content-Disposition: attachment;filename=Pacientes.xlsx');
		header('Cache-Control: max-age=0'); //no cache         
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');					
	}

}
