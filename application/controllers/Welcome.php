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

    function saveTempOrderProduct($products = [], $idOrden = '') {

        $user_data = $this->session->userdata();
        $productsByBranch = [];
        //Separar productos de acuerdo a la clave de la sucursal
        foreach ($products as $key => $product) {
            $productsByBranch[$product['idSucursal']][] = $product;
        }

		$existOrderBranch = $this->view_service->indexedSearchByModel('view', 'idSucursal', ['idUsuario' => $user_data['idUsuario'], 'status' => 'REGISTERED'], ['imprimirSQL' => 0], FALSE, 'getOrderCurrent');
        $existProductOrder = $this->view_service->indexedSearchByModel('view', ['idSucursal','idProducto', 'idProductoSucursal'], ['idUsuario' => $user_data['idUsuario'], 'status' => 'REGISTERED'], ['imprimirSQL' => 0], FALSE, 'getProductsOrder');
        
        if( empty($idOrden) && !empty($user_data['idOrden']) )
            $idOrden = $user_data['idOrden'];
        
        //$this->imprimir($productsByBranch,1);
        foreach ($productsByBranch as $key => $products) {
            //Validar que pedido de la sucursal exista, sino existe crearlo
            if( empty($existOrderBranch[$key]) ) {

                $saveOrder = $this->pedido_service->save(['status' => 'REGISTERED', 'idSucursal' => $key]);

                foreach ($products as $key => $product) {
                    
                    $this->pedido_service->saveProductOrder(['idPedido' => $saveOrder['id'], 
                                                             'idProducto' => $product['idProducto'], 
                                                             'nombreProducto' => strtoupper($product['nombre']), 
                                                             'cantidad' => $product['cantidad'], 
                                                             'precio' => $product['precio'], 
                                                             'idProductoSucursal' => $product['idProductoSucursal'], 
                                                             'descuento' => !empty($product['descuento']) ? $product['descuento'] : 0, 
                                                             'subtotal' => $product['precio'] * $product['cantidad']]);
                }
                
            } else {

                //Validar el producto exista el producto_x_pedido con una sucursal existente
                foreach ($products as $key => $product) {
                    //Si existe aumentar la cantidad del producto_x_pedido y si no existe agregarlo
                    if( !empty($existProductOrder[$product['idSucursal']][$product['idProducto']][$product['idProductoSucursal']]) ) {
   
                        $pxp = $existProductOrder[$product['idSucursal']][$product['idProducto']][$product['idProductoSucursal']];
                        $cant = $pxp['cantidad'] + $product['cantidad'];
                        $this->pedido_service->saveProductOrder(['cantidad' => $cant, 
                                                                 'precio' => $product['precio'], 
                                                                 'descuento' => !empty($product['descuento']) ? $product['descuento'] : 0, 
                                                                 'subtotal' => $product['precio'] * $cant], $pxp['idProductoPedido']);
                    
                    } else {
                        //Validar que el pedido exista por la idSucursal
                        //No existe en la sucursal
                        if( empty($existOrderBranch[$product['idSucursal']]) ) {

                            $saveOrder = $this->pedido_service->save(['status' => 'REGISTERED', 'idSucursal' => $product['idSucursal']]);
                            
                            $this->pedido_service->saveProductOrder(['idPedido' => $saveOrder['id'], 
                                                                     'idProducto' => $product['idProducto'], 
                                                                     'nombreProducto' => strtoupper($product['nombre']), 
                                                                     'cantidad' => $product['cantidad'], 
                                                                     'precio' => $product['precio'], 
                                                                     'subtotal' => $product['precio'] * $product['cantidad'], //$product['subtotal']
                                                                     'idProductoSucursal' => $product['idProductoSucursal'], 
                                                                     'descuento' => $product['descuento']]);
                        } else {
                            //Existe en la sucursal pero diferente idProductoSucursal                           
                            $this->pedido_service->saveProductOrder(['idPedido' => $existOrderBranch[$product['idSucursal']]['idPedido'], 
                                                                     'idProducto' => $product['idProducto'], 
                                                                     'nombreProducto' => strtoupper($product['nombre']), 
                                                                     'cantidad' => $product['cantidad'], 
                                                                     'precio' => $product['precio'], 
                                                                     'subtotal' => $product['precio'] * $product['cantidad'],
                                                                     'idProductoSucursal' => $product['idProductoSucursal'], 
                                                                     'descuento' => !empty($product['descuento']) ? $product['descuento'] : 0]);
                        }
                    }
                }
            }
        }

        $this->session->unset_userdata('products');
        
        if( isset($user_data['urlGoCartCheckout'] ) && $user_data['urlGoCartCheckout'] == 'order' ) {
            $this->session->unset_userdata('urlGoCartCheckout');
            redirect('order');
        }
    }
    
 	function save() {
		
		$result = ['error' => 0, 'msg' => 'Test add Controlador Welcome'];
		
		echo json_encode($result);
	}

	function update() {
		
		$result = ['error' => 0, 'msg' => 'Test edit Controlador Welcome'];
		
		echo json_encode($result);
	}

	function delete() {
		
		$result = ['error' => 0, 'msg' => 'Test delete Controlador Welcome'];
		
		echo json_encode($result);
	}

	function getRegs() {
		
		$result = ['error' => 0, 'msg' => 'Test getRegs Controlador Welcome'];
		
		echo json_encode($result);
	}

	function searchTest() {

        $result = ['error' => 0, 'msg' => 'Test searchTest'];

        echo json_encode($result);
    }

    function noExists() {
            
            $result = ['error' => 0, 'msg' => 'Test noExists'];
    
            echo json_encode($result);
    }

    function contact() {

        echo "<h1>Contacto</h1>";
    }

    function terminos() {
                
        echo "<h1>Terminos</h1>";
    }

    function testMap() {

        $data['fileToLoad']  = ['js/clases/Maps.js'];
        $data['main_content']  = $this->load->view('home/testMap.html', [], TRUE);
        
        $this->loadTemplate($data);

    }

    function privacyPolicies() {
        		    
        $data['fileToLoad']  = [''];

        $data['main_content']  = $this->load->view('elements/politicas-privacidad.html', [], TRUE);
        
        $this->loadTemplate($data);
    }

    function help() {
        		    
        //$data['fileToLoad']  = [''];

        $data['main_content']  = $this->load->view('home/help.html', [], TRUE);
        
        $this->loadTemplate($data);
    }

}