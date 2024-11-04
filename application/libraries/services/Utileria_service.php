<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class Utileria_Service extends Class_Service {

    public function __construct() {
        
        parent::__construct();

        $this->modelToLoad = array(            
            'viewModel' => 'view_model'
        );

        $this->loadModel();
    }

    function search($condicion = array(), $extras = array()) { return; }

    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) { return; }

    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL){ return; }
    
    function delete($id = NULL, $cond = NULL){ return; }

    function searchAttribute($condicion = array(), $extras = array()) {

        return $this->CI->cat_atributo_model->buscar($condicion, $extras);
    }

    function searchValueAttribute($condicion = array(), $extras = array()) {
        
        return $this->CI->atributo_valor_model->buscar($condicion, $extras);
    }
        
	function getIdx($table) {
        
        $reg = ['fecha' => date('Y-m-d H:i:s'),'tabla' => $table];

        return $this->action_on_reg($this->CI->idx_model, $reg);
    }

    function saveValueAttribute(array $reg, int $id = NULL) : array {
        
        $result = $this->saveByModel('valorAtributo', $reg, $id, $cond = NULL);

        return $result;
    }

    function getPermissionScreen($controlador = '') {

        $result = FALSE;

        $relations = [
            "READ" => "validRead",
            "ADD" => "validAdd",
            "EDIT" => "validEdit",
            "DELETE" => "validDelete"
        ];

        $user_data = $this->CI->session->userdata();

        if( isset($user_data['isRoot']) && $user_data['isRoot'] == '0'){

            $controller = current( $this->CI->seguridad_service->searchController(['clave' => strtoupper($controlador != NULL ? $controlador : 'welcome' ), 'idApp' => !empty($user_data['app']['idApp']) ? $user_data['app']['idApp'] : '']) );
            
            if( !empty($user_data['app']['idApp']) && !empty($user_data['rol']['idRol']) )
                $data = $this->searchByModel('viewModel', ['idApp' => $user_data['app']['idApp'], 'idRol' => $user_data['rol']['idRol'], 'idController' => !empty($controller['idController']) ? $controller['idController'] : '', 'idUsuario' => $user_data['idUsuario']], ['imprimirSQL' => 0], 'getPrivilege') ;
            
            //Recorrer relations con data
            if( !empty($data) ){

                foreach ($relations as $key => $value) {
                    $result[$value] = FALSE;
                    foreach ($data as $key2 => $value2) {
                        if( $value2['clavePermiso'] == $key )
                            $result[$value] = TRUE;
                    }
                }

            }

        } else {
                
            foreach ($relations as $key => $value) {
                $result[$value] = TRUE;
            }
        }

        return $result;
    }     
    
    function openCypher($action = 'encrypt', $string = false){

        $this->CI->load->library('encryption');

		$action = trim($action);
		
		$output = false;

		if ( $action && ($action == 'encrypt' || $action == 'decrypt') && $string ){

			if ( $action == 'encrypt' ) {
				$output = $this->CI->encryption->encrypt($string);
			}

			if ( $action == 'decrypt' ) {
				$output = $this->CI->encryption->decrypt($string);
			}
		}

		return $output;
	}

    function addImgsProducts($prods = [], $bandera = '0') {

        $inventoryMargin = current( $this->searchByModel('viewModel', ['clave' => 'INVENTORY_MARGIN'], ['imprimirSQL' => 0], 'getAttribute') );

        $inventoryMargin = $inventoryMargin ? $inventoryMargin['valor'] : 0;
        
        $inventoryMargin = (int)$inventoryMargin / 100;


		$idxProds = implode(',', array_filter(array_column($prods, 'idx')));

        if( !empty($idxProds) ) {
            $idxs = explode(",", $idxProds);
			$uniqueIdx = array_unique($idxs);
			$idxIN = implode(",", $uniqueIdx);
			$filesAll = $this->CI->file_service->indexed_search(['idx','idArchivo'],['idx_IN' => $idxIN ,'activo' => 1, 'borrado' => 0], ['orderBy' => 'orden ASC', 'imprimirSQL' => 0]);
        }

         //Agregar evaluacion
         $idCveArticulos = implode("','", array_unique(array_filter(array_column($prods, 'clave'))));
         $evaluation = $this->indexedSearchByModel('viewModel', 'idProducto', ['cve_articulo_IN' => $idCveArticulos], ['imprimirSQL' => 0], FALSE, 'getEvaluationProduct');

        $evaluationBranch = $this->indexedSearchByModel('viewModel', 'idSucursal', ['esEvaluacionEntrega' => 0, 'idProducto_NULL' => 1], ['imprimirSQL' => 0], FALSE, 'getEvaluationBranch');

        foreach($prods as &$reg){

            if( isset($reg['existencias']) && $reg['existencias'] > 0 )
                $reg['existencias'] = floor(((int)$reg['existencias']) - ((int)$reg['existencias'] * $inventoryMargin));

            if( $bandera == '0' ) {

			    $reg['evaluacion'] = !empty($evaluation[$reg['idProducto']]['promedioDecimal']) ? $evaluation[$reg['idProducto']]['promedioDecimal'] : '0.0';

                if( !empty($reg['idSucursal']) ) {
                    $reg['evaluacionBranch'] = !empty($evaluationBranch[$reg['idSucursal']]['promedioDecimal']) ? $evaluationBranch[$reg['idSucursal']]['promedioDecimal'] : '0.0';
                    $reg['numComentBranch'] = !empty($evaluationBranch[$reg['idSucursal']]['totalComentario']) ? $evaluationBranch[$reg['idSucursal']]['totalComentario'] : '0';
                }

            }

            if( !empty($reg['idx']) ){

				$archivo = !empty($filesAll[$reg['idx']]) ? $filesAll[$reg['idx']] : '';
				
                if(!empty($archivo)){
                    foreach ($archivo as &$arc) {
                        $reg['foto'][] = URL_PORTAL_ADMINISTRACION.'files/' . $arc['nombreFS'];				
                    }
                } else {
                    $reg['foto'][] = URL_PORTAL_ADMINISTRACION.'assets/images/product_img1.jpg';																
                }

			} else {
                $reg['foto'][] = URL_PORTAL_ADMINISTRACION.'assets/images/product_img1.jpg';																
			}

        }

        return $prods;
    }
}