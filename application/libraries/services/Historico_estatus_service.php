<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

/**
 * Description of usuario_service
 *
 * @author Felipe Avila
 */
class Historico_estatus_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'statusHistory' => 'historico_estatus_model',
         );

        $this->loadModel(); 
         
    }
    
    function search($condicion = array(), $extras = array()) {
        return $this->CI->historico_estatus_model->buscar($condicion, $extras);
    }
    
    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->historico_estatus_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
    
	function buscar_estatus($filtros = array(), $extras = array()) {
        
        return $this->CI->cat_estatus_model->buscar($filtros, $extras);
	}
	
	function indexed_search_estatus($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {
        
        return $this->CI->cat_estatus_model->indexed_search($indexes, $condicion, $extras, $multiply);
    }
	    
    function saveDirect($reg) {
        
        if ( empty($reg['idUsuario']) )
            $reg['idUsuario'] = $this->CI->session->userdata('idUsuario');
        
        if ( empty($reg['fecha']) )
            $reg['fecha'] = date('Y-m-d H:i:s');
        
        if ( empty($reg['comentario']) )
            $reg['comentario'] = NULL;
        
        return $this->action_on_reg(
            $this->CI->historico_estatus_model, 
            $reg
        );
    }

	function save($reg = array(), $entitie = '', $idEstatus = '', $regOrigin = '', $id = '', $name_reg = 'reg', $contactType = '') {

		if( $entitie == 'contacto' )
			$reg['tabla'] = 'medio_' . $entitie . 'x' . $contactType;
		else
			$reg['tabla'] = $entitie;

		if ( $entitie === 'productSchedule' || $entitie === 'companySchedule' )
			$entitie = 'horario';
	
		$entitie = 'id' .  ucfirst($entitie);
	
		$tableFields = ['tabla', 'idRegistro', 'idEstatus', 'idUsuario', 'fecha', $entitie, 'comentario'];
		foreach ($reg as $key => $dato) {
			if(!in_array($key, $tableFields))
			unset($reg[$key]);
		}
		//$reg['idRegistro'] = $reg[$entitie];
		$reg['idEstatus'] = $idEstatus;
		
		if($entitie != 'idUsuario')
		    unset($reg[$entitie]);

        $rules = $this->CI->historico_estatus_model->get_rules($reg, $name_reg);
		
        $action = empty($id) ? 'add' : 'update';

        $user_data = $this->CI->session->userdata();

		$reg['idUsuario'] = $user_data['idUsuario'];
		$reg['idRol'] = $user_data['rol']['idRol'];
		$reg['fecha'] = date('Y-m-d H:i:s');
		
        $result = $this->validar_form($reg, $rules, $this->CI->historico_estatus_model, $action, $action === 'add' ? NULL : "idRegistro = '{$id}'", [], $id);
	
		return $result;
	}

    function saveAux($reg = array(), $entitie = '', $idEstatus = '', $regOrigin = '', $id = '', $name_reg = 'reg', $contactType = '') {

		$user_data = $this->CI->session->userdata();
		
		$reg['idUsuario'] = isset($user_data['idUsuario']) ? $user_data['idUsuario'] : null;
		$reg['idRol'] = isset($user_data['rol']) ? $user_data['rol']['idRol'] : null;
		
        $rules = $this->CI->historico_estatus_model->get_rules($reg, $name_reg);
		
        $action = empty($id) ? 'add' : 'update';

		$reg['fecha'] = date('Y-m-d H:i:s');
		
        $result = $this->validar_form($reg, $rules, $this->CI->historico_estatus_model, $action, $action === 'add' ? NULL : "idRegistro = '{$id}'", [], $id);
	
		return $result;
	}

    function saveEcomm($reg = array(), $id = '', $name_reg = 'reg') {

		$user_data = $this->CI->session->userdata();

		$reg['idUsuario'] = $user_data['idUsuario'];
		$reg['idRol'] = $user_data['rol']['idRol'];

        $rules = $this->CI->historico_estatus_model->get_rules($reg, $name_reg);
		
        $action = empty($id) ? 'add' : 'update';
        
		$reg['fecha'] = date('Y-m-d H:i:s');
		
        $result = $this->validar_form($reg, $rules, $this->CI->historico_estatus_model, $action, $action === 'add' ? NULL : "idRegistro = '{$id}'", [], $id);
	
		return $result;
	}

	function save_batch($reg = array()) {
    
		$result = $this->CI->historico_estatus_model->addBatch($reg);

        return $result;
    }
    
    function delete($id = NULL, $cond = NULL) {
        
        return $this->action_on_reg($this->CI->producto_model, ['borrado' => 1], 'update', ['idVacante' => $id]);
    }
    
    function update($reg = [], $id = 0) {
        
        return $this->action_on_reg($this->CI->historico_estatus_model, $reg, 'update', ['idHistorico' => $id]);
    }

    function getHistoricOrder($order_id = 0) {

		$historic_idx = [];

		$historic = $this->indexed_search('idEstatus', ['tabla' => 'pedido', 'idRegistro' => $order_id], ['orderBy' => 'idHistorico DESC', 'imprimirSQL' => 0]);

		$status_ids = implode(',', array_unique(array_column($historic, 'idEstatus')) );

		$status = $this->CI->catalogo_service->indexed_search('status', 'idEstatus', ['id_IN' => $status_ids], ['imprimirSQL' => 0]);

		foreach($historic as &$hist) {
			$hist['status'] = $status[$hist['idEstatus']]['clave'];
		}

		foreach($historic as &$histFixed) {
			$historic_idx[$histFixed['status']] = $histFixed;
		}

		return $historic_idx;
	}

    function getBoucherDateGeneration($order_id = 0) {
		
		$status_req_act = current( $this->CI->catalogo_service->search('status', ['clave' => 'REQUIRES_ACTION'], ['imprimirSQL' => 0]) );

		$historic = current( $this->search(['idEstatus' => $status_req_act['idEstatus'], 'idRegistro' => $order_id, 'tabla' => 'pedido'], ['orderBy' => 'idHistorico DESC', 'imprimirSQL' => 0]) );
		
		return $historic['fecha'];
	}
}
