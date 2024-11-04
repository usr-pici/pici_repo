<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_CLASS_SERVICE;

class View_Service extends Class_Service {

    public function __construct() {

        parent::__construct();

        $this->modelToLoad = array(
            'viewModel' => 'view_model'
        );

        $this->loadModel();
    }

    function search($condicion = array(), $extras = array()) {

        return;
    }

    function indexed_search($indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {

        return;
    }
    
    function searchByMethod($method, $condicion = array(), $extras = array()) {

        return $this->searchByModel('view', $condicion, $extras, $method);
    }
    
    function indexedSearchByMethod($method, $indexes = NULL, $condicion = NULL, $extras = NULL, $multiply = FALSE) {

        return $this->indexedSearchByModel('view', $indexes, $condicion, $extras, $multiply, $method);
    }

    function save($reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL) {

        return;
    }

    function delete($id = NULL, $cond = NULL, $reg = []) {

        return;
    }
}
