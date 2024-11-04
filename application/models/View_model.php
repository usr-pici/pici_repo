<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of estatus_model
 *
 * @author 
 */
class View_model extends MY_Model {
   
    function  __construct() {

        parent::__construct();
        
        $this->title = "Tabla para simulaci\xF3n de vistas";
    }
    
    function promotion($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['id']) )
            $condicion[] = "prom.idPromocion = '{$filtros['id']}'";
            
        if ( isset($filtros['id_IN']) )
            $condicion[] = "prom.idPromocion IN ({$filtros['id_IN']})";
            
        if ( isset($filtros['tipo']) )
            $condicion[] = "prom.tipo = '{$filtros['tipo']}'";
            
        if ( isset($filtros['clave_IN']) )
            $condicion[] = "prom.clave IN ({$filtros['clave_IN']})";
            
        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "prom.idEmpresa = '{$filtros['idEmpresa']}'";
            
        if ( isset($filtros['idSucursal']) )
            $condicion[] = "psuc.idSucursal = '{$filtros['idSucursal']}'";
            
        if ( isset($filtros['idSucursal_IN']) )
            $condicion[] = "psuc.idSucursal IN ({$filtros['idSucursal_IN']})";
            
        if ( isset($filtros['activo']) )
            $condicion[] = "prom.activo = '{$filtros['activo']}'";
            
        if ( !empty($filtros['idProd_idSuc_IN']) )
            $condicion[] = "CONCAT_WS('_', pprod.idProducto, psuc.idSucursal) IN ( {$filtros['idProd_idSuc_IN']} )";
            
        if ( !empty($filtros['idProm_idProd_IN']) )
            $condicion[] = "CONCAT_WS('_', prom.idPromocion, pprod.idProducto) IN ( {$filtros['idProm_idProd_IN']} )";
        
        if ( !empty($filtros['vigente']) )
            $condicion[] = "prom.activo = '1' AND ( NOW() BETWEEN prom.vigenciaInicio AND prom.vigenciaFin )";
            
        $condicion[] = "prom.borrado IN (" . ( isset($filtros['borrado']) ? $filtros['borrado'] : 0 ) . ")";
        $condicion[] = "psuc.borrado IN (" . ( isset($filtros['borradoSuc']) ? $filtros['borradoSuc'] : 0 ) . ")";
        $condicion[] = "psel.borrado IN (" . ( isset($filtros['borradoSel']) ? $filtros['borradoSel'] : 0 ) . ")";
            
        if ( !empty($extras['limit']) && $extras['limit'] == -1 ) {
            
            $extras['campos'] = "COUNT(*) AS total";
        }
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
            prom.*,
            psuc.idPromSuc,
            psuc.idSucursal,
            psel.idPromSel,
            psel.categoria,
            psel.subcategoria,
            psel.marca,
            psel.producto,
            pdet.idPromDet,
            pdet.cantidadMinCompra, 
            pdet.montoMinCompra,
            pdet.porcentajeDescuento, 
            pdet.montoDescuento,
            pdet.precioPromocion,
            pprod.idPromProd,
            pprod.idProducto
        ';        

        $sql = "
            SELECT 
                    {$campos}
                FROM promocion prom 
                    INNER JOIN promocion_sucursal psuc ON ( psuc.idPromocion = prom.idPromocion )
                    INNER JOIN promocion_seleccion psel ON ( psel.idPromocion = prom.idPromocion )
                    INNER JOIN promocion_detalle pdet ON ( pdet.idPromSuc = psuc.idPromSuc AND pdet.idPromSel = psel.idPromSel )
                    INNER JOIN promocion_producto pprod ON ( pprod.idPromSel = psel.idPromSel )
                    
        ";

        return $this->execute_view($sql, $condicion, $extras);
    }
    
    function product($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['id']) )
            $condicion[] = "cp.idProducto = '{$filtros['id']}'";
            
        if ( isset($filtros['id_IN']) )
            $condicion[] = "cp.idProducto IN ({$filtros['id_IN']})";
            
        if ( isset($filtros['clave_IN']) )
            $condicion[] = "cp.clave IN ({$filtros['clave_IN']})";
            
        if ( isset($filtros['nombre_LIKE']) )
            $condicion[] = "cp.nombre LIKE '%{$filtros['nombre_LIKE']}%'";
            
        if ( isset($filtros['clave_LIKE']) )
            $condicion[] = "cp.clave LIKE '%{$filtros['clave_LIKE']}%'";
            
        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "cp.idEmpresa = '{$filtros['idEmpresa']}'";
            
        if ( isset($filtros['idMarca_IN']) )
            $condicion[] = "cp.idMarca IN ({$filtros['idMarca_IN']})";
            
        if ( isset($filtros['idMarca']) )
            $condicion[] = "cp.idMarca = '{$filtros['idMarca']}'";
            
        if ( isset($filtros['idServicio']) )
            $condicion[] = "cp.idServicio = '{$filtros['idServicio']}'";
            
        if ( isset($filtros['activo']) )
            $condicion[] = "cp.activo = '{$filtros['activo']}'";
            
        if ( !empty($filtros['idCategoriaPadre_NULL']) )
            $condicion[] = "cc.idCategoria IS NOT NULL AND cc.idCategoriaPadre IS NULL";
        
        if ( isset($filtros['idSubCategoria_IN']) )
            $condicion[] = "csc.idCategoria IN ({$filtros['idSubCategoria_IN']})";
            
        if ( isset($filtros['idCategoriaPadre_IN']) )
            $condicion[] = "cc.idCategoria IN ({$filtros['idCategoriaPadre_IN']})";
            
        if ( isset($filtros['promSel_IN']) )
            $condicion[] = "cp.idProducto IN ( SELECT idProducto FROM promocion_producto WHERE idPromSel IN ( {$filtros['promSel_IN']} ) )";
            
        $condicion[] = "cp.borrado = " . ( isset($filtros['borrado']) ? "'{$filtros['borrado']}'" : "'0'" );
        $condicion[] = "IFNULL(pxs.borrado, '0') = " . ( isset($filtros['borradoSucursal']) ? "'{$filtros['borradoSucursal']}'" : "'0'" );
            
        if ( !empty($extras['limit']) && $extras['limit'] == -1 ) {
            
            $extras['campos'] = "COUNT(DISTINCT cp.idProducto) AS total";
        }
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
            cp.*,
            e.nombreComercial AS empresa,
            cm.clave AS claveMarca,
            cm.nombre AS marca,
            cm.idx AS idxMarca,
            cs.clave AS claveServicio,
            cs.nombre AS servicio,
            cc.clave AS claveCategoria,
            cc.nombre AS categoria,
            cc.idx AS idxCategoria,
            csc.clave AS claveSubcategoria,
            csc.nombre AS subcategoria,
            csc.idx AS idxSubcategoria,
            pxs.idSucursal,
            suc.clave AS claveSucursal,
            pxs.precio,
            pxs.almacen,
            pxs.existencias,
            pxs.activo AS activoSucursal
        ';        

        $sql = "
            SELECT 
                    {$campos}
                FROM cat_producto cp 
                    INNER JOIN empresa e ON ( e.idEmpresa = cp.idEmpresa )
                    LEFT JOIN cat_marca cm ON ( cm.idMarca = cp.idMarca )
                    LEFT JOIN cat_servicio cs ON ( cs.idServicio = cp.idServicio )
                    LEFT JOIN cat_categoria cc ON ( cc.idCategoria = cp.idCategoria )
                    LEFT JOIN cat_categoria csc ON ( csc.idCategoria = cp.idSubCategoria )
                    LEFT JOIN producto_x_sucursal pxs ON ( pxs.idProducto = cp.idProducto )
                    LEFT JOIN cat_sucursal suc ON ( suc.idSucursal = pxs.idSucursal )
        ";

        return $this->execute_view($sql, $condicion, $extras);
    }
    
    function productAttribute($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['id']) )
            $condicion[] = "cp.idProducto = '{$filtros['id']}'";
            
        if ( isset($filtros['id_IN']) )
            $condicion[] = "cp.idProducto IN ({$filtros['id_IN']})";
            
        if ( isset($filtros['clave_IN']) )
            $condicion[] = "cp.clave IN ({$filtros['clave_IN']})";
            
        if ( isset($filtros['claveAtributo_IN']) )
            $condicion[] = "ca.clave IN ({$filtros['claveAtributo_IN']})";
            
        if ( isset($filtros['idSucursal_IN']) )
            $condicion[] = "pxs.idSucursal IN ({$filtros['idSucursal_IN']})";
            
        if ( !empty($filtros['conExistencias']) )
            $condicion[] = "IFNULL(pxs.existencias, 0) > 0";
            
        $condicion[] = "cp.borrado IN (" . ( isset($filtros['borrado_IN']) ? $filtros['borrado_IN'] : "0" ) . ")";
        $condicion[] = "pxs.borrado IN (" . ( isset($filtros['borradoSucursal_IN']) ? $filtros['borradoSucursal_IN'] : "0" ) . ")";
        $condicion[] = "pxs.activo IN (" . ( isset($filtros['activoSucursal_IN']) ? $filtros['activoSucursal_IN'] : "1" ) . ")";
        $condicion[] = "av.borrado IN (" . ( isset($filtros['borradoValor_IN']) ? $filtros['borradoValor_IN'] : "0" ) . ")";
        $condicion[] = "ca.borrado IN (" . ( isset($filtros['borradoAtributo_IN']) ? $filtros['borradoAtributo_IN'] : "0" ) . ")";
        $condicion[] = "ao.borrado IN (" . ( isset($filtros['borradoOpcion_IN']) ? $filtros['borradoOpcion_IN'] : "0" ) . ")";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
            cp.*,
            pxs.idProductoSucursal,
            pxs.idSucursal,
            pxs.precio,
            pxs.existencias,
            pxs.almacen,
            av.idAtributoValor,
            av.idAtributo,
            av.idAtributoOpcion,
            ca.clave AS claveAtributo,
            ca.etiqueta AS etiquetaAtributo,
            ao.clave AS claveOpcion,
            ao.nombre AS opcion,
            ao.abreviatura AS abreviaturaOpcion,
            ao.valor AS valorOpcion
        ';        

        $sql = "
            SELECT 
                    {$campos}
                FROM cat_producto cp
                    INNER JOIN producto_x_sucursal pxs ON ( pxs.idProducto = cp.idProducto )
                    LEFT JOIN atributo_valor av ON ( av.idProductoSucursal = pxs.idProductoSucursal )
                    LEFT JOIN cat_atributo ca ON ( ca.idAtributo = av.idAtributo )
                    LEFT JOIN atributo_opcion ao ON ( ao.idAtributoOpcion = av.idAtributoOpcion )
        ";

        return $this->execute_view($sql, $condicion, $extras);
    }
    
    function personContact($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['idPersona']) )
            $condicion[] = "p.idPersona IN ({$filtros['idPersona']})";

        if ( isset($filtros['borrado']) )
            $condicion[] = "p.borrado = '{$filtros['borrado']}'";
            
        if ( isset($filtros['clave_IN']) )
            $condicion[] = "cmc.clave IN ({$filtros['clave_IN']})";

        if ( isset($filtros['clave']) )
            $condicion[] = "cmc.clave = '{$filtros['clave']}'"; 
            
        if ( isset($filtros['idContacto']) )
            $condicion[] = "mc.idContacto = '{$filtros['idContacto']}'"; 

        if ( isset($filtros['idx']) )
            $condicion[] = "mc.idx = '{$filtros['idx']}'"; 
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
            p.idPersona,
            p.idx as idxPersona,
			CONCAT(p.nombre, " ", p.apellidos) as nombreCompleto,
            mc.*,
            cmc.clave,
            cmc.nombre
        ';        

        $sql = "
            SELECT 
                    {$campos}
                FROM persona p LEFT JOIN medio_contacto mc
				ON ( mc.idx = p.idx ) LEFT JOIN cat_medio_contacto cmc 
				ON ( cmc.idMedioContacto = mc.idMedioContacto )
        ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getPrivilege($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion[] = "pxr.activo = 1 AND pxr.borrado = 0 AND aru.borrado = 0 AND user.borrado = 0";

        if ( isset($filtros['idUsuario']) )
            $condicion[] = "user.idUsuario = '{$filtros['idUsuario']}'";

        if ( isset($filtros['idApp']) )
            $condicion[] = "aru.idApp = '{$filtros['idApp']}'";

        if ( !empty($filtros['idRol']) )
            $condicion[] = "aru.idRol = '{$filtros['idRol']}'";

        if ( !empty($filtros['idController']) )
            $condicion[] = "pxr.idController = '{$filtros['idController']}'";

        if ( !empty($filtros['idController_IN']) )
            $condicion[] = "pxr.idController IN (" . $filtros['idController_IN'] . ")";

        if ( !empty($filtros['clavePermiso']) )
            $condicion[] = "cp.clave = '{$filtros['clavePermiso']}'";

        if ( !empty($filtros['searchCatalog']) )
            $condicion[] = "cm.clave = '{$filtros['searchCatalog']}'";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                user.idUsuario,
                person.idPersona,
                CONCAT(person.nombre, " ", person.apellidos) as nombreCompleto,
                aru.idApp,
                ca.clave as claveApp,
                ca.nombre AS aplicacion,
                aru.idRol,
                cr.clave as claveRol,
                cr.nombre AS Rol,
                pxr.idPermiso,
                cp.clave as clavePermiso,
                cp.nombre AS permiso,
                pxr.idController,
                cc.clave as claveController,
                cc.nombre AS controlador,
                arm.idAppRolMenu,
                arm.idMenu,
                cm.clave AS claveMenu,
                cm.nombre AS menu,
                arm.activo AS activoMenu,
                arm.borrado AS borradoMenu
        ';        

        $sql = "
                SELECT {$campos}
                FROM usuario user INNER JOIN persona person
                ON (user.idPersona = person.idPersona) LEFT JOIN app_rol_usuario aru
                ON (user.idUsuario = aru.idUsuario) LEFT JOIN permisoxrol pxr
                ON (aru.idRol = pxr.idRol) INNER JOIN cat_app ca
                ON (aru.idApp = ca.idApp AND ca.activo = 1 AND ca.borrado = 0) INNER JOIN cat_controller cc
                ON (pxr.idController = cc.idController AND cc.activo = 1 AND cc.borrado = 0) INNER JOIN cat_permiso cp
                ON (pxr.idPermiso = cp.idPermiso AND cp.activo = 1 AND cp.borrado = 0) INNER JOIN cat_rol cr
                ON (aru.idRol = cr.idRol AND cr.activo = 1 AND cr.borrado = 0) INNER JOIN app_rol_menu arm
                ON (pxr.idRol = arm.idRol AND pxr.activo = 1 AND pxr.borrado = 0 AND arm.activo = 1 AND arm.borrado = 0) INNER JOIN cat_menu cm
                ON (arm.idMenu = cm.idMenu AND arm.idApp = cm.idApp AND pxr.idController = cm.idController AND cm.activo = 1 AND cm.borrado = 0)
        ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getForms($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["borrado = 0"];

        if ( !empty($filtros['idFormulario']) )
            $condicion[] = "idFormulario = '{$filtros['idFormulario']}'";

        if ( !empty($filtros['clave']) )
            $condicion[] = "clave LIKE '%{$filtros['clave']}%'";

        if ( !empty($filtros['nombre']) )
            $condicion[] = "nombre LIKE '%{$filtros['nombre']}%'";

        if ( isset($filtros['idEstatus']) )
            $condicion[] = "activo = '{$filtros['idEstatus']}'";

        if ( isset($filtros['fechaOrdenPar']) )
			$condicion[] = "DATE(vigenciaIni) between " . $filtros['fechaOrdenPar'];

        if ( isset($filtros['fechaIniSolo']) )
			$condicion[] = "vigenciaIni >= " . $filtros['fechaIniSolo'];

        if ( isset($filtros['fechaFinSolo']) )
			$condicion[] = "vigenciaFin <= " . $filtros['fechaFinSolo'];

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    *
                ';        

        $sql = "
                SELECT {$campos}
                FROM formulario
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getQuestions($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["p.borrado = 0"];

        if ( !empty($filtros['idFormulario']) )
            $condicion[] = "p.idFormulario = '{$filtros['idFormulario']}'";

        if ( !empty($filtros['idPregunta']) )
            $condicion[] = "p.idPregunta = '{$filtros['idPregunta']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    p.*,
                    ctc.clave AS cveField,
                    ctc.nombre AS nameField
                ';        

        $sql = "
                SELECT {$campos}
                FROM pregunta p INNER JOIN cat_tipo_campo ctc
                ON (p.idTipoCampo = ctc.idTipoCampo)

            ";

            if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function getOptionsQuestion($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["borrado = 0"];

        if ( !empty($filtros['idPregunta']) )
            $condicion[] = "idPregunta = '{$filtros['idPregunta']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    *
                ';        

        $sql = "
                SELECT {$campos}
                FROM pregunta_opcion
            ";

            if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function getDataCompany($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "emp.idEmpresa = '{$filtros['idEmpresa']}'";

        if ( isset($filtros['isRoot']) )
            $condicion[] = "emp.isRoot = '{$filtros['isRoot']}'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                    emp.idEmpresa,
                    emp.nombreComercial,
                    emp.emiteFactura,
                    emp.idx,
                    emp.isRoot,
                    emp.idPaqueteria,
                    r.idRFC,
                    r.tipoPersona,
                    r.rfc,
                    r.razonSocial,
                    r.idRegimenFiscal,
                    mc.idContacto as idContactoFacturacion,
                    mc.valor as emailFacturacion,
                    ce.idContactoEmpresa,
                    ce.idPersona,
                    ce.representante,
                    p.nombre as nombrePrimario,
                    p.apellidos as apellidosPrimario,
                    mcpe.idContacto as idEmailPrimario,
                    mcpe.valor as emailPrimario,
                    mcpc.idContacto as idCelularPrimario,
                    mcpc.valor as celularPrimario,
                    cd.idDireccion,
                    cd.direccion,
                    cd.calle,
                    cd.numeroExterior,
                    cd.numeroInterior,
                    cd.latitud,
                    cd.longitud,
                    cd.cp,
                    cd.referencias,
                    cd.idLocalidad,
                    cl.nombre AS localidad,
                    cl.idMunicipio,
                    cm.nombre AS municipio,
                    cm.idEstado,
                    ced.nombre AS estado,
                    pContact.idPersona as idPersonContact,
                    pContact.nombre as nombreContacto,
                    mcContact.idContacto as idContactoTelefonoContacto,
                    mcContact.valor as telefonoContacto,
                    arc.idClasificacion,
                    CONCAT_WS('', arc.ruta, arc.nombreFS) AS fotoEmpresa,
                    emp.idPasarelaPago,
                    cc.clave AS cvePasarelaPago,
                    cc.nombre AS pasarelaPago,
                    ccPaq.clave AS cvePaqueteria,
                    ccPaq.nombre AS paqueteria,
                    av.idAtributoValor  AS idAtributoApiKeyPaymentPublic,
                    av.valor as valueKeyPub_gatepay,
                    avs.idAtributoValor  AS idAtributoApiKeyPaymentSecret,
                    avs.valor as valueKeySecret_gatepay,
                    avt.idAtributoValor  AS idAtributoTokenAccess,
                    avt.valor as valorTokenAccess,
                    cs.clave AS cveSucursal,
                    cs.nombre AS sucursal,
                    emp.activo
                ";        

        $sql = "
                SELECT {$campos}
                FROM empresa emp LEFT JOIN rfc r
                ON (emp.idRFC = r.idRFC AND r.borrado = 0) LEFT JOIN medio_contacto mc
                ON (emp.idx = mc.idx AND mc.borrado = 0) INNER JOIN cat_direccion cd
                ON (emp.idx = cd.idx) LEFT JOIN archivo arc
                ON (emp.idx = arc.idx) LEFT JOIN contacto_empresa ce
                ON (emp.idEmpresa = ce.idEmpresa) LEFT JOIN persona p
                ON (ce.idPersona = p.idPersona) INNER JOIN medio_contacto mcpe
                ON (p.idx = mcpe.idx AND mcpe.etiqueta = 'email') INNER JOIN medio_contacto mcpc
                ON (p.idx = mcpc.idx AND mcpc.etiqueta = 'celular') LEFT JOIN persona pContact
                ON (cd.idPersonaContacto = pContact.idPersona) LEFT JOIN medio_contacto mcContact
                ON (pContact.idx = mcContact.idx) INNER JOIN cat_localidad cl
                ON (cd.idLocalidad = cl.idLocalidad) INNER JOIN cat_municipio cm
                ON (cl.idMunicipio = cm.idMunicipio) INNER JOIN cat_estado ced
                ON (cm.idEstado = ced.idEstado) LEFT JOIN atributo_valor av
                ON (emp.idx = av.idx AND av.borrado = 0 AND av.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'KEYPUB_GATEPAY')) LEFT JOIN atributo_valor avs
                ON (emp.idx = avs.idx AND avs.borrado = 0 AND avs.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'KEYSECRET_GATEPAY')) LEFT JOIN atributo_valor avt
                ON (emp.idx = avt.idx AND avt.borrado = 0 AND avt.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'TOKEN_SKYDROPX')) LEFT JOIN cat_clasificacion cc
                ON (cc.idClasificacion = emp.idPasarelaPago) LEFT JOIN cat_sucursal cs
                ON (emp.idEmpresa = cs.idEmpresa AND cs.ecommerce = 1 AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_clasificacion ccPaq 
                ON (ccPaq.idClasificacion = emp.idPaqueteria)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getCards($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["t.borrado = 0"];

        if ( isset($filtros['idUsuario']) )
            $condicion[] = "t.idUsuario = '{$filtros['idUsuario']}'";

        if ( isset($filtros['idTarjeta']) )
            $condicion[] = "t.idTarjeta = '{$filtros['idTarjeta']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    t.*,
                    av.valor AS stripeCardId,
                    avm.valor AS paymentMethodId
                ';        

        $sql = "
                SELECT {$campos}
                FROM tarjeta t INNER JOIN atributo_valor av
                ON (t.idx = av.idx AND av.borrado = 0 AND av.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'CARD_STRIPEID')) INNER JOIN atributo_valor avm
                ON (t.idx = avm.idx AND avm.borrado = 0 AND avm.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'PAYMENT_METHODID'))
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }


    function getDataBranch($filtros = array(), $extras = array()) {
        
        $condicion = [];
        
        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "emp.idEmpresa = '{$filtros['idEmpresa']}'";

        if ( isset($filtros['isRoot']) )
            $condicion[] = "emp.isRoot = '{$filtros['isRoot']}'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                    -- cs.idSucursal,
                    -- cs.idx,
                    cs.clave,
                    cs.nombre,
                    -- cs.descripcion,
                    -- cs.idEmpresa,
                    cs.horario,
                    -- emp.nombreComercial as empresa,
                    cs.ecommerce,
                    -- cs.activo,
                    -- mc.idContacto as idContactoTelefono,
                    mc.valor as telefonoSucursal,
                    -- cd.idDireccion,
                    cd.direccion,
                    CONCAT_WS('', fil.ruta, fil.nombreFS) AS fotoSucursal
                    -- cd.calle,
                    -- cd.numeroExterior,
                    -- cd.numeroInterior,
                    -- cd.latitud,
                    -- cd.longitud,
                    -- cd.cp,
                    -- cd.referencias,
                    -- cd.idLocalidad,
                    -- cl.nombre AS localidad,
                    -- cl.idMunicipio,
                    -- cm.nombre AS municipio,
                    -- cm.idEstado,
                    -- ced.nombre AS estado,
                    -- pContact.idPersona as idPersonContact,
                    -- pContact.nombre as nombreContacto,
                    -- mcContact.idContacto as idContactoTelefonoContacto,
                    -- mcContact.valor as telefonoContacto
                ";        

        $sql = "
                SELECT {$campos}
                FROM cat_sucursal cs INNER JOIN empresa emp
                ON (cs.idEmpresa = emp.idEmpresa AND cs.borrado = 0 AND cs.activo = 1) INNER JOIN cat_direccion cd
                ON (cs.idx = cd.idx) LEFT JOIN persona pContact
                ON (cd.idPersonaContacto = pContact.idPersona) LEFT JOIN medio_contacto mcContact
                ON (pContact.idx = mcContact.idx)  LEFT JOIN medio_contacto mc
                ON (cs.idx = mc.idx AND mc.borrado = 0) INNER JOIN cat_localidad cl
                ON (cd.idLocalidad = cl.idLocalidad) INNER JOIN cat_municipio cm
                ON (cl.idMunicipio = cm.idMunicipio) INNER JOIN cat_estado ced
                ON (cm.idEstado = ced.idEstado) LEFT JOIN archivo fil
                ON (cs.idx = fil.idx AND fil.idArchivo = (SELECT idArchivo FROM archivo WHERE idx = cs.idx ORDER BY idArchivo DESC limit 1))
            ";

        return $this->execute_view($sql, $condicion, $extras);

    }

    function searchProducts($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1'"];

        if( empty($extras['groupBy']) )
		    $extras['groupBy'] = ['pxs.idSucursal, pxs.idProducto'];

        if ( isset($filtros['descripcion']) ) $match = trim($filtros['descripcion']);
        
        if ( !empty($match) ) $condicion[] = "cp.nombre LIKE '%{$match}%'";

		if ( isset($filtros['cveOnly']) ) $condicion[] = " cp.clave = '{$filtros['cveOnly']}' ";

        if ( isset($filtros['branch']) ) $condicion[] = " branch.clave = '{$filtros['branch']}' ";

        if ( isset($filtros['idProducto']) ) $condicion[] = " cp.idProducto = '{$filtros['idProducto']}' ";

        if ( isset($filtros['idSucursal']) ) $condicion[] = " branch.idSucursal = '{$filtros['idSucursal']}' ";

        if ( isset($filtros['idProductoSucursal']) ) $condicion[] = " pxs.idProductoSucursal = '{$filtros['idProductoSucursal']}' ";
        
        if ( isset($filtros['idProductoSucursal_IN']) ) $condicion[] = " pxs.idProductoSucursal IN ('{$filtros['idProductoSucursal_IN']}')";
            
        if ( isset($filtros['descripcionLIKE'])) $condicion[] = "cp.nombre LIKE '%{$filtros['descripcionLIKE']}%'";

        if ( isset($filtros['cveMarca_IN']) ) $condicion[] = "cm.clave IN ('{$filtros['cveMarca_IN']}')";
        
        if ( isset($filtros['cveVendor_IN']) ) $condicion[] = "branch.clave IN ('{$filtros['cveVendor_IN']}')";
        
        if ( isset($filtros['variant_IN']) ) $condicion[] = "ao.clave IN ('{$filtros['variant_IN']}')";

        if ( isset($filtros['precingBETWEEN']) ) $condicion[] = "pxs.precio between " . $filtros['precingBETWEEN'];

        if ( isset($filtros['category']) ) $condicion[] = " cc.clave = '{$filtros['category']}' ";

        if ( isset($filtros['cveCategory_IN']) ) $condicion[] = "cc.clave IN ('{$filtros['cveCategory_IN']}')";
        
        if ( isset($filtros['subcategory']) ) $condicion[] = " ccSub.clave = '{$filtros['subcategory']}' ";
                    
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cp.idCategoria,
                        cs.nombre AS servicio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cc.clave AS cveCategoria,
                        cc.nombre AS categoria,
                        ccSub.clave AS cveSubcategoria,
                        ccSub.nombre AS subcategoria,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        ccUnit.nombre AS unidadMedida,
                        emp.nombreComercial AS empresa,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        branch.descripcion AS descSucursal,
                        mcRep.valor AS emailRep,
                        branch.horario,
                        branch.ecommerce,
                        mcBranch.valor AS telefonoBranch,
                        cd.direccion,
                        ao.clave AS cveVariante,
			            CONCAT(a.ruta, '', a.nombreFS) as archivo,
                        CONCAT(aBranch.ruta, '', aBranch.nombreFS) as archivoSucursal
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_producto cp INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria AND ccSub.activo = 1 AND ccSub.borrado = 0) LEFT JOIN cat_clasificacion ccUnit
            ON (ccUnit.idClasificacion = cp.idUnidadMedida AND ccUnit.activo = 1 AND ccUnit.borrado = 0) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio AND cs.activo = 1 AND cs.borrado = 0) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa AND emp.activo = 1 AND emp.borrado = 0) INNER JOIN contacto_empresa ce
            ON (emp.idEmpresa = ce.idEmpresa) INNER JOIN persona p
            ON (p.idPersona = ce.idPersona) INNER JOIN medio_contacto mcRep
            ON (mcRep.idx = p.idx AND mcRep.etiqueta = 'email') INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) INNER JOIN cat_direccion cd
            ON (branch.idx = cd.idx AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN archivo a
            ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN ( SELECT idx, ruta, nombreFS, activo, borrado, MAX(idArchivo) AS latest FROM archivo GROUP BY idArchivo ) aBranch
            ON (aBranch.idx = branch.idx AND aBranch.activo = 1 AND aBranch.borrado = 0) LEFT JOIN medio_contacto mcBranch
            ON (mcBranch.idx = branch.idx AND mcBranch.activo = 1 AND mcBranch.borrado = 0) LEFT JOIN atributo_combinacion ac
            ON (cp.idProducto = ac.idProducto AND ac.idProducto = pxs.idProducto AND ac.activo = 1 AND ac.borrado = 0) LEFT JOIN atributo_combinacion_detalle acd
            ON (ac.idAtributoCombinacion = acd.idAtributoCombinacion AND acd.activo = 1 AND acd.borrado = 0) LEFT JOIN atributo_valor av
            ON (av.idAtributo = acd.idAtributo AND av.activo = 1 AND av.borrado = 0 AND cp.idx = av.idx) LEFT JOIN atributo_opcion ao
            ON (av.idAtributo = ao.idAtributo AND av.idAtributoOpcion = ao.idAtributoOpcion AND ao.activo = 1 AND ao.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function searchProductsPromotion($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1'"];

        if( empty($extras['groupBy']) )
		    $extras['groupBy'] = ['pxs.idSucursal, pxs.idProducto'];

        if ( isset($filtros['descripcion']) ) $match = trim($filtros['descripcion']);
        
        if ( !empty($match) ) $condicion[] = "cp.nombre LIKE '%{$match}%'";

		if ( isset($filtros['cveOnly']) ) $condicion[] = " cp.clave = '{$filtros['cveOnly']}' ";

        if ( isset($filtros['branch']) ) $condicion[] = " branch.clave = '{$filtros['branch']}' ";

        if ( isset($filtros['idProducto']) ) $condicion[] = " cp.idProducto = '{$filtros['idProducto']}' ";

        if ( isset($filtros['idProducto_IN']) ) $condicion[] = "cp.idProducto IN ({$filtros['idProducto_IN']})";

        if ( isset($filtros['idSucursal']) ) $condicion[] = " branch.idSucursal = '{$filtros['idSucursal']}' ";

        if ( isset($filtros['idProductoSucursal']) ) $condicion[] = " pxs.idProductoSucursal = '{$filtros['idProductoSucursal']}' ";
        
        if ( isset($filtros['idProductoSucursal_IN']) ) $condicion[] = " pxs.idProductoSucursal IN ('{$filtros['idProductoSucursal_IN']}')";
            
        if ( isset($filtros['descripcionLIKE'])) $condicion[] = "cp.nombre LIKE '%{$filtros['descripcionLIKE']}%'";

        if ( isset($filtros['cveMarca_IN']) ) $condicion[] = "cm.clave IN ('{$filtros['cveMarca_IN']}')";
        
        if ( isset($filtros['cveVendor_IN']) ) $condicion[] = "branch.clave IN ('{$filtros['cveVendor_IN']}')";
        
        if ( isset($filtros['variant_IN']) ) $condicion[] = "ao.valor IN ('{$filtros['variant_IN']}')";

        if ( isset($filtros['precingBETWEEN']) ) $condicion[] = "pxs.precio between " . $filtros['precingBETWEEN'];

        if ( isset($filtros['category']) ) $condicion[] = " cc.clave = '{$filtros['category']}' ";
        
        if ( isset($filtros['subcategory']) ) $condicion[] = " ccSub.clave = '{$filtros['subcategory']}' ";
        
        if ( isset($filtros['promocion']) ) $condicion[] = " prom.tipo = '{$filtros['promocion']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cp.idCategoria,
                        cs.nombre AS servicio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cc.clave AS cveCategoria,
                        cc.nombre AS categoria,
                        ccSub.clave AS cveSubcategoria,
                        ccSub.nombre AS subcategoria,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        ccUnit.nombre AS unidadMedida,
                        emp.nombreComercial AS empresa,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        branch.descripcion AS descSucursal,
                        mcRep.valor AS emailRep,
                        branch.horario,
                        branch.ecommerce,
                        mcBranch.valor AS telefonoBranch,
                        cd.direccion,
			            CONCAT(a.ruta, '', a.nombreFS) as archivo,
                        CONCAT(aBranch.ruta, '', aBranch.nombreFS) as archivoSucursal,
                        prom.tipo AS promocion
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_producto cp INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria AND ccSub.activo = 1 AND ccSub.borrado = 0) LEFT JOIN cat_clasificacion ccUnit
            ON (ccUnit.idClasificacion = cp.idUnidadMedida AND ccUnit.activo = 1 AND ccUnit.borrado = 0) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio AND cs.activo = 1 AND cs.borrado = 0) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa AND emp.activo = 1 AND emp.borrado = 0) INNER JOIN contacto_empresa ce
            ON (emp.idEmpresa = ce.idEmpresa) INNER JOIN persona p
            ON (p.idPersona = ce.idPersona) INNER JOIN medio_contacto mcRep
            ON (mcRep.idx = p.idx AND mcRep.etiqueta = 'email') INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) INNER JOIN cat_direccion cd
            ON (branch.idx = cd.idx AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN archivo a
            ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN ( SELECT idx, ruta, nombreFS, activo, borrado, MAX(idArchivo) AS latest FROM archivo GROUP BY idArchivo ) aBranch
            ON (aBranch.idx = branch.idx AND aBranch.activo = 1 AND aBranch.borrado = 0) LEFT JOIN medio_contacto mcBranch
            ON (mcBranch.idx = branch.idx AND mcBranch.activo = 1 AND mcBranch.borrado = 0) LEFT JOIN atributo_combinacion ac
            ON (cp.idProducto = ac.idProducto AND ac.idProducto = pxs.idProducto AND ac.activo = 1 AND ac.borrado = 0) LEFT JOIN atributo_combinacion_detalle acd
            ON (ac.idAtributoCombinacion = acd.idAtributoCombinacion AND acd.activo = 1 AND acd.borrado = 0) LEFT JOIN atributo_valor av
            ON (av.idAtributo = acd.idAtributo AND av.activo = 1 AND av.borrado = 0 AND cp.idx = av.idx) LEFT JOIN atributo_opcion ao
            ON (av.idAtributo = ao.idAtributo AND av.idAtributoOpcion = ao.idAtributoOpcion AND ao.activo = 1 AND ao.borrado = 0) LEFT JOIN promocion_producto pp
            ON (pp.idProducto = cp.idProducto) LEFT JOIN promocion_seleccion ps
            ON (ps.idPromSel = pp.idPromSel) LEFT JOIN promocion prom
            ON (prom.idPromocion = ps.idPromocion) LEFT JOIN promocion_sucursal promxs
            ON (promxs.idPromocion = prom.idPromocion AND promxs.idSucursal = pxs.idSucursal)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function searchProductsVariants($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1'"];
		$extras['groupBy'] = [' ao.idAtributoOpcion, cp.idProducto'];

        if ( isset($filtros['descripcion']) ) $match = trim($filtros['descripcion']);
        
        if ( !empty($match) ) $condicion[] = "cp.nombre LIKE '%{$match}%'";

		if ( isset($filtros['cveOnly']) ) $condicion[] = " cp.clave = '{$filtros['cveOnly']}' ";

        if ( isset($filtros['branch']) ) $condicion[] = " branch.clave = '{$filtros['branch']}' ";

        if ( isset($filtros['idProducto']) ) $condicion[] = " cp.idProducto = '{$filtros['idProducto']}' ";

        if ( isset($filtros['idSucursal']) ) $condicion[] = " branch.idSucursal = '{$filtros['idSucursal']}' ";

        if ( isset($filtros['idProductoSucursal']) ) $condicion[] = " pxs.idProductoSucursal = '{$filtros['idProductoSucursal']}' ";
            
        if ( isset($filtros['descripcionLIKE'])) $condicion[] = "cp.nombre LIKE '%{$filtros['descripcionLIKE']}%'";

        if ( isset($filtros['idProducto_IN']) ) $condicion[] = "cp.idProducto IN ('{$filtros['idProducto_IN']}')";

        if ( isset($filtros['cveMarca_IN']) ) $condicion[] = "cm.clave IN ('{$filtros['cveMarca_IN']}')";
        
        if ( isset($filtros['variant_IN']) ) $condicion[] = "ao.clave IN ('{$filtros['variant_IN']}')";

        if ( isset($filtros['cveCategory_IN']) ) $condicion[] = "cc.clave IN ('{$filtros['cveCategory_IN']}')";

        if ( isset($filtros['category']) ) $condicion[] = " cc.clave = '{$filtros['category']}' ";
        
        if ( isset($filtros['subcategory']) ) $condicion[] = " ccSub.clave = '{$filtros['subcategory']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cs.nombre AS servicio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        emp.nombreComercial AS empresa,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        cd.direccion,
			            CONCAT(a.ruta, '', a.nombreFS) as archivo,
                        ca.clave AS cveAtributo,
                        ca.nombre AS nombreAtributo,
                        ao.idAtributoOpcion,
                        ao.clave AS cveVariante,
                        ao.valor AS valorVariante,
                        ao.nombre AS variantes   
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_producto cp INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria AND ccSub.activo = 1 AND ccSub.borrado = 0) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio AND cs.activo = 1 AND cs.borrado = 0) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa AND emp.activo = 1 AND emp.borrado = 0) INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) INNER JOIN cat_direccion cd
            ON (branch.idx = cd.idx AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN archivo a
            ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN atributo_combinacion ac
            ON (cp.idProducto = ac.idProducto AND ac.activo = 1 AND ac.borrado = 0) LEFT JOIN atributo_combinacion_detalle acd
            ON (ac.idAtributoCombinacion = acd.idAtributoCombinacion AND acd.activo = 1 AND acd.borrado = 0) LEFT JOIN cat_atributo ca
            ON (ca.idAtributo = acd.idAtributo AND ca.activo = 1 AND ca.borrado = 0) LEFT JOIN atributo_opcion ao
            ON (ca.idAtributo = ao.idAtributo AND ao.activo = 1 AND ao.borrado = 0) INNER JOIN atributo_valor av
            ON (av.idAtributoOpcion = ao.idAtributoOpcion AND av.activo = 1 AND av.borrado = 0 AND cp.idx = av.idx)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function searchProductsPredictive($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1'"];
		$extras['groupBy'] = ['pxs.idProducto'];

        if ( isset($filtros['descripcion']) ) $match = trim($filtros['descripcion']);
        
        if ( !empty($match) ) $condicion[] = "cp.nombre LIKE '%{$match}%'";
            
        if ( isset($filtros['descripcionLIKE'])) $condicion[] = "cp.nombre LIKE '%{$filtros['descripcionLIKE']}%'";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_producto cp INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function getContactUser($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['email']) )
            $condicion[] = "mc.valor LIKE '%{$filtros['email']}%'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    mc.*,
                    p.idPersona,
                    user.idUsuario
                ';        

        $sql = "
                SELECT {$campos}
                FROM medio_contacto mc INNER JOIN persona p
                ON (mc.idx = p.idx AND mc.activo = 1 AND mc.borrado = 0 AND p.activo = 1 AND p.borrado = 0) INNER JOIN usuario user
                ON (p.idPersona = user.idPersona AND user.activo = 1 AND user.borrado = 0)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getContactUserInactive($filtros = array(), $extras = array()) {

        $condicion = [];
        
        if ( isset($filtros['email']) )
            $condicion[] = "mc.valor LIKE '%{$filtros['email']}%'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    mc.*,
                    p.idPersona,
                    user.idUsuario
                ';        

        $sql = "
                SELECT {$campos}
                FROM medio_contacto mc INNER JOIN persona p
                ON (mc.idx = p.idx AND mc.activo = 1 AND mc.borrado = 0 AND mc.idClasificacion IS NULL AND p.activo = 1 AND p.borrado = 0) LEFT JOIN usuario user
                ON (p.idPersona = user.idPersona AND user.activo = 1 AND user.borrado = 0)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getWishlistProducts($filtros = array(), $extras = array()) {

		$condicion = array();
				
		if ( isset($filtros['idUsuario']) )
			$condicion[] = "prodWish.idUsuario = '" . $filtros['idUsuario'] . "'";

        if ( isset($filtros['idProducto']) )
			$condicion[] = "prodWish.idProducto = '" . $filtros['idProducto'] . "'";

        if ( isset($filtros['idProductoSucursal']) )
			$condicion[] = "prodWish.idProductoSucursal = '" . $filtros['idProductoSucursal'] . "'";

        if ( isset($filtros['idProductoLista']) )
			$condicion[] = "prodWish.idProductoLista = '" . $filtros['idProductoLista'] . "'";

		$condicion[] = "prodWish.borrado = '0'";
        $extras['groupBy'] = "prodWish.idProductoSucursal";
			
		$sql = "SELECT 
					prodWish.*,
					cp.nombre AS product,
					cp.clave AS cveArticulo,
                    cp.idx,
                    branch.clave AS claveSucursal
                FROM producto_x_lista_deseos prodWish
                INNER JOIN cat_producto cp
                ON (prodWish.idProducto = cp.idProducto AND cp.borrado = 0) INNER JOIN producto_x_sucursal pxs
                ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
                ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)
            ";
								

		if ( $condicion )
			$sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
	}

    function getOrder($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        $extras['groupBy'] = "pxp.idProducto";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.*,
                        ped.idx AS idxOrder,
                        ord.idMetodoPago,
                        ord.idTarjeta,
                        cd.direccion AS customerAddress,
                        cd.cp AS cpCustomer,
                        cd.referencias AS referencesDelivery,
                        ceCustomer.nombre AS customerState,
                        cmCustomer.nombre AS customerCity,
                        cc.clave AS keyDelivery,
                        cc.nombre AS deliveryName,
                        ccPayment.clave AS keyPayment,
                        ccPayment.nombre AS paymentName,
                        vue.clave AS claveEstatusRegistro,
                        vue.estatus AS nombreEstatusRegistro,
                        CONCAT(p.nombre, '', p.apellidos) AS nombreReferencia,
                        mcPersonUserTel.valor AS phoneReference,
                        mcPersonUserMail.valor AS email,
                        mcPersonUserCel.idContacto AS idContactoCelular,
                        mcPersonUserCel.valor AS celular,
                        pOrder.nombre AS nombreCliente,
                        pOrder.apellidos AS apellidosCliente,
                        CONCAT(pOrder.nombre, ' ', pOrder.apellidos) AS customerName,
                        card.cardNum AS customerCard,
                        card.funding AS customerCardFunding,
                        card.brand AS customerCardBrand,
                        r.razonSocial AS customerRazonSocial,
                        r.rfc AS customerRFC,
                        av.valor AS stripeCardId,
                        avPMethod.valor AS paymentMethodId,
                        avStripeId.valor AS stripeId,
                        cs.clave AS cveSucursal,
                        cs.nombre AS sucursal,
                        cs.idEmpresa,
                        mcBranchTel.valor AS phoneBranch,
                        cdBranch.cp AS cpSucursal,
                        cdBranch.direccion AS branchAddress,
                        ceBranch.nombre AS estadoBranch,
                        cmBranch.nombre AS ciudadBranch,
                        he.fecha AS fechaRegistro,
                        heConfirm.fecha AS fechaConfirmacion,
                        heP.fecha AS fechaPago,
                        hePend.fecha AS fechaPendiente,
                        heCancel.fecha AS fechaCancelado,
                        heCancel.comentario AS comentarioCancelado,
                        heCancel.idUsuario AS idUsuarioCancelo,
                        heCancelRemb.fecha AS fechaCanceladoRemb,
                        hePOxxo.fecha AS fechaPagoOxxo,
                        heDelivered.fecha AS fechaEntregado,
                        heReturn.fecha AS fechaDevolucion,
                        pxp.nombreProducto,
                        pxp.descuento,
                        cp.idx,
                        ord.barcode,
                        ord.boucherExpiresAfter,
                        ord.paymentIntentId
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN cat_clasificacion cc
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_direccion cd
            ON (ped.idDireccionEntrega = cd.idDireccion AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN persona p
            ON (cd.idPersonaContacto = p.idPersona) LEFT JOIN medio_contacto mcPersonUserTel
            ON (p.idx = mcPersonUserTel.idx AND mcPersonUserTel.etiqueta = 'telefono') LEFT JOIN cat_clasificacion ccPayment
            ON (ord.idMetodoPago = ccPayment.idClasificacion AND ccPayment.activo = 1 AND ccPayment.borrado = 0) INNER JOIN usuario user
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN medio_contacto mcPersonUserMail
            ON (pOrder.idx = mcPersonUserMail.idx AND mcPersonUserMail.etiqueta = 'email') LEFT JOIN medio_contacto mcPersonUserCel
            ON (pOrder.idx = mcPersonUserCel.idx AND mcPersonUserCel.etiqueta = 'celular') LEFT JOIN tarjeta card
            ON (ord.idTarjeta = card.idTarjeta AND card.borrado = 0) LEFT JOIN rfc r
            ON (ped.idRFC = r.idRFC AND r.activo = 1 AND r.borrado = 0) LEFT JOIN atributo_valor av
            ON (card.idx = av.idx AND av.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'CARD_STRIPEID')) LEFT JOIN atributo_valor avStripeId
            ON (pOrder.idx = avStripeId.idx AND avStripeId.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'USER_STRIPEID')) LEFT JOIN atributo_valor avPMethod
            ON (card.idx = avPMethod.idx AND avPMethod.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'PAYMENT_METHODID')) INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN historico_estatus he
            ON (he.tabla = 'pedido' AND he.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'REGISTERED') AND he.idRegistro = ped.idPedido) INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
            ON (pxp.idProducto = cp.idProducto AND cp.borrado = 0) INNER JOIN cat_direccion cdBranch
            ON (cs.idx = cdBranch.idx AND cdBranch.activo = 1 AND cdBranch.borrado = 0 ) LEFT JOIN cat_localidad clBranch
            ON (cdBranch.idLocalidad = clBranch.idLocalidad) LEFT JOIN cat_municipio cmBranch
            ON (clBranch.idMunicipio = cmBranch.idMunicipio) LEFT JOIN cat_estado ceBranch
            ON (cmBranch.idEstado = ceBranch.idEstado) LEFT JOIN medio_contacto mcBranchTel
            ON (cs.idx = mcBranchTel.idx AND mcBranchTel.etiqueta = 'telefono' AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_localidad clCustomer
            ON (cd.idLocalidad = clCustomer.idLocalidad) LEFT JOIN cat_municipio cmCustomer
            ON (clCustomer.idMunicipio = cmCustomer.idMunicipio) LEFT JOIN cat_estado ceCustomer
            ON (cmCustomer.idEstado = ceCustomer.idEstado) LEFT JOIN historico_estatus heP
            ON (heP.tabla = 'pedido' AND heP.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('SUCCESSFULL')) AND heP.idRegistro = ped.idPedido) LEFT JOIN historico_estatus hePend
            ON (hePend.tabla = 'pedido' AND hePend.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('REQUIRES_ACTION')) AND hePend.idRegistro = ped.idPedido) LEFT JOIN historico_estatus hePOxxo
            ON (hePOxxo.tabla = 'pedido' AND hePOxxo.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('SUCCEEDED')) AND hePOxxo.idRegistro = ped.idPedido) LEFT JOIN historico_estatus heDelivered
            ON (heDelivered.tabla = 'pedido' AND heDelivered.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('DELIVERED') AND idClasificacion = (SELECT idClasificacion FROM cat_clasificacion WHERE clave IN ('ORDER'))) AND heDelivered.idRegistro = ped.idPedido) LEFT JOIN historico_estatus heReturn
            ON (heReturn.tabla = 'pedido' AND heReturn.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('RETURN_REQUEST') AND idClasificacion = (SELECT idClasificacion FROM cat_clasificacion WHERE clave IN ('ORDER'))) AND heReturn.idRegistro = ped.idPedido) LEFT JOIN historico_estatus heCancel
            ON (heCancel.tabla = 'pedido' AND heCancel.idRegistro = ped.idPedido AND heCancel.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('CANCELLED') AND idClasificacion = (SELECT idClasificacion FROM cat_clasificacion WHERE clave IN ('ORDER')))) LEFT JOIN historico_estatus heConfirm
            ON (heConfirm.tabla = 'pedido' AND heConfirm.idRegistro = ped.idPedido AND (heConfirm.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'REQUIRES_ACTION') OR heConfirm.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'SUCCESSFULL')) ) LEFT JOIN historico_estatus heCancelRemb
            ON (heCancelRemb.tabla = 'pedido' AND heCancelRemb.idRegistro = ped.idPedido AND heCancelRemb.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave IN ('CANCELLED_REMB') AND idClasificacion = (SELECT idClasificacion FROM cat_clasificacion WHERE clave IN ('ORDER'))))

        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getProductsOrder($filtros = array(), $extras = array()) {

		$condicion = array();
				
		if ( isset($filtros['idUsuario']) )
			$condicion[] = "ped.idUsuario = '" . $filtros['idUsuario'] . "'";

        if ( isset($filtros['idPedido']) )
			$condicion[] = "pxp.idPedido = '" . $filtros['idPedido'] . "'";

        if ( isset($filtros['idPedido_IN']) )
            $condicion[] = "pxp.idPedido IN (" . $filtros['idPedido_IN'] . ")";

        if ( isset($filtros['status']) )
			$condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) )
			$condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['branch']) )
			$condicion[] = "branch.clave = '" . $filtros['branch'] . "'";

        if ( isset($filtros['cveProducto']) )
			$condicion[] = "cp.clave = '" . $filtros['cveProducto'] . "'";

        if ( isset($filtros['idProductoSucursal']) ) 
            $condicion[] = " pxs.idProductoSucursal = '{$filtros['idProductoSucursal']}' ";
        
		$condicion[] = "ped.borrado = '0'";
        //$extras['groupBy'] = "pxp.idProducto";
        //$extras['groupBy'] = "pxs.idSucursal, pxs.idProducto";
        $extras['groupBy'] = "pxp.idProductoPedido";

        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                                    ped.idPedido,
                                    ped.idUsuario,
                                    ped.numOrden,
                                    ped.idSucursal,
                                    ped.total,
                                    ord.idOrden,
                                    ord.numOrden AS numOrdenOrder,
                                    ord.idMetodoPago,
                                    ord.idTarjeta,
                                    ped.idModalidadEntrega,
                                    ped.idDireccionEntrega,
                                    ped.requiereFactura,
                                    ped.idUsoCFDI,
                                    ped.idRFC,
                                    ped.costoEnvio,
                                    ped.subtotal AS subtotalOrder,
                                    ped.provider,
                                    ped.service_level_code,
                                    cp.idProducto,
                                    cp.clave,
                                    cp.nombre,
                                    cp.nombre AS producto,
                                    cp.idx,
                                    cp.longitud AS x,
                                    cp.altura AS y,
                                    cp.profundidad AS z,
                                    cp.peso,
                                    cm.clave AS marca,
                                    CONCAT(a.ruta, '', a.nombreFS) as archivo,
                                    branch.clave AS sucursal,
                                    branch.nombre AS branchName,
                                    pxp.*,
                                    vue.clave as claveEstatusRegistro,
                                    vue.estatus as nombreEstatusRegistro,
                                    pxs.idProductoSucursal,
                                    pxs.existencias,
                                    pxs.precio AS precioBranch,
                                    pxs.almacen AS almacenBranch
                        ";
			
		$sql = "SELECT 
                        {$campos}
                FROM pedido ped LEFT JOIN orden ord
                ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) INNER JOIN producto_x_pedido pxp
                ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
                ON (pxp.idProducto = cp.idProducto AND cp.activo = 1 AND cp.borrado = 0) LEFT JOIN archivo a
                ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
                ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido ) INNER JOIN producto_x_sucursal pxs
                ON (cp.idProducto = pxs.idProducto AND pxp.idProductoSucursal = pxs.idProductoSucursal AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
                ON (pxs.idSucursal = branch.idSucursal AND ped.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) LEFT JOIN cat_marca cm
                ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0)
            ";
								

		if ( $condicion )
			$sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
	}

    function getOrderCurrent($filtros = array(), $extras = array()) {

		$condicion = array();
				
		if ( isset($filtros['idUsuario']) )
			$condicion[] = "ped.idUsuario = '" . $filtros['idUsuario'] . "'";

        if ( isset($filtros['status']) )
			$condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['branch']) )
			$condicion[] = "branch.clave = '" . $filtros['branch'] . "'";

        if ( isset($filtros['branchKey']) )
			$condicion[] = "ped.idSucursal = '" . $filtros['branchKey'] . "'";

		$condicion[] = "ped.borrado = '0'";
			
		$sql = "SELECT  ped.idUsuario,
                        ped.idPedido,
                        ped.idSucursal,
                        ped.numOrden,
                        ord.numOrden AS numOrdenOrder,
                        branch.clave AS sucursal,
                        vue.clave as claveEstatusRegistro,
                        vue.estatus as nombreEstatusRegistro
                FROM pedido ped LEFT JOIN orden ord
                ON (ped.numOrden = ord.numOrden AND ord.borrado = 0)  LEFT JOIN view_ultimo_estatus vue 
                ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido ) INNER JOIN cat_sucursal branch
                ON (ped.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)
            ";
								

		if ( $condicion )
			$sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
	}

    function getBrands($filtros = array(), $extras = array()) {

		$condicion = array();
		$condicion[] = "activo = '1' AND borrado = '0'";
				
		if ( isset($filtros['idMarca_IN']) ) $condicion[] = "idMarca IN ('{$filtros['idMarca_IN']}')";
			
		$sql = "SELECT DISTINCT(idMarca) AS idMarca, clave, nombre FROM cat_marca";
								
		if ( $condicion )
			$sql .= " WHERE " . implode(' AND ', $condicion);

        return parent::execute_query($sql, $extras);
	}

    function getOrderHistory($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        $extras['groupBy'] = "ped.idPedido";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";

        if ( isset($filtros['archivado']) ) 
            $condicion[] = " ped.archivado = '{$filtros['archivado']}' ";
        else 
            $condicion[] = " ped.archivado = '0' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        ped.idx,
                        ped.numOrden,
                        ped.estatusPaqueteria,
                        ped.claveEstatusPaqueteria,
                        ped.requiereFactura,
                        ped.tracking_url_provider,
                        ped.total,
                        he.fecha AS fechaConfirmacion,
                        cd.direccion AS customerAddress,
                        r.razonSocial AS customerRazonSocial,
                        r.rfc AS customerRFC,
                        cc.clave AS keyDelivery,
                        cc.nombre AS deliveryName,
                        ccPayment.clave AS keyPayment,
                        ccPayment.nombre AS paymentName,
                        vue.clave AS claveEstatusRegistro,
                        vue.estatus AS nombreEstatusRegistro,
                        CONCAT(p.nombre, '', p.apellidos) AS nombreReferencia,
                        mcPersonUserTel.valor AS phoneReference,
                        mcPersonUserCel.idContacto AS idContactoCelular,
                        mcPersonUserCel.valor AS celular,
                        CONCAT(pOrder.nombre, ' ', pOrder.apellidos) AS customerName,
                        cs.clave AS cveSucursal,
                        cs.nombre AS sucursal,
                        mcBranchTel.valor AS phoneBranch,
                        cdBranch.direccion AS branchAddress,
                        pxp.nombreProducto,
                        pxp.idProducto,
                        pxp.idProductoSucursal,
                        cp.clave AS cveProduct,
                        cp.idx AS idxProduct,
                        cm.clave AS marca,
                        ord.barcode
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN cat_clasificacion cc
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_direccion cd
            ON (ped.idDireccionEntrega = cd.idDireccion AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN persona p
            ON (cd.idPersonaContacto = p.idPersona) LEFT JOIN medio_contacto mcPersonUserTel
            ON (p.idx = mcPersonUserTel.idx AND mcPersonUserTel.etiqueta = 'telefono') LEFT JOIN cat_clasificacion ccPayment
            ON (ord.idMetodoPago = ccPayment.idClasificacion AND ccPayment.activo = 1 AND ccPayment.borrado = 0) INNER JOIN usuario user
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN medio_contacto mcPersonUserCel
            ON (pOrder.idx = mcPersonUserCel.idx AND mcPersonUserCel.etiqueta = 'celular') INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN historico_estatus he
            ON (he.tabla = 'pedido' AND he.idRegistro = ped.idPedido AND (he.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'REQUIRES_ACTION') OR he.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'SUCCESSFULL')) ) INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
            ON (cp.idProducto = pxp.idProducto AND cp.borrado = 0) INNER JOIN cat_direccion cdBranch
            ON (cs.idx = cdBranch.idx AND cdBranch.activo = 1 AND cdBranch.borrado = 0 ) LEFT JOIN cat_localidad clBranch
            ON (cdBranch.idLocalidad = clBranch.idLocalidad) LEFT JOIN medio_contacto mcBranchTel
            ON (cs.idx = mcBranchTel.idx AND mcBranchTel.etiqueta = 'telefono' AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_localidad clCustomer
            ON (cd.idLocalidad = clCustomer.idLocalidad) LEFT JOIN rfc r
            ON (ped.idRFC = r.idRFC AND r.activo = 1 AND r.borrado = 0) LEFT JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getOrderData($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        $extras['groupBy'] = "pxp.idProducto";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";

        if ( isset($filtros['paymentIntentId']) ) 
            $condicion[] = " ord.paymentIntentId = '{$filtros['paymentIntentId']}' ";

        if ( isset($filtros['idProductoPedido']) ) 
            $condicion[] = " pxp.idProductoPedido = '{$filtros['idProductoPedido']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        ped.idUsuario,
                        ped.idx AS idxOrder,
                        ped.idModalidadEntrega,
                        ped.requiereFactura,
                        ord.idOrden,
                        ord.numOrden,
                        ord.idMetodoPago,
                        ord.idTarjeta,
                        ord.barcode,
                        ped.idRFC,
                        ped.idUsoCFDI,
                        ped.idDireccionEntrega,
                        ped.costoEnvio,
                        ped.total,
                        ped.service_level_code,
                        ped.provider,
                        cd.direccion AS customerAddress,
                        cd.cp AS cpCustomer,
                        cd.referencias AS referencesDelivery,
                        ceCustomer.nombre AS customerState,
                        cmCustomer.nombre AS customerCity,
                        cc.clave AS keyDelivery,
                        cc.nombre AS deliveryName,
                        ccPayment.clave AS keyPayment,
                        ccPayment.nombre AS paymentName,
                        vue.clave AS claveEstatusRegistro,
                        vue.estatus AS nombreEstatusRegistro,
                        CONCAT(p.nombre, '', p.apellidos) AS nombreReferencia,
                        mcPersonUserMail.valor AS email,
                        mcPersonUserCel.idContacto AS idContactoCelular,
                        mcPersonUserCel.valor AS celular,
                        pOrder.nombre AS nombreCliente,
                        pOrder.apellidos AS apellidosCliente,
                        CONCAT(pOrder.nombre, ' ', pOrder.apellidos) AS customerName,
                        card.cardNum AS customerCard,
                        card.funding AS customerCardFunding,
                        card.brand AS customerCardBrand,
                        r.razonSocial AS customerRazonSocial,
                        r.rfc AS customerRFC,
                        cs.clave AS cveSucursal,
                        cs.nombre AS sucursal,
                        mcBranchTel.valor AS phoneBranch,
                        cdBranch.cp AS cpSucursal,
                        cdBranch.direccion AS branchAddress,
                        ceBranch.nombre AS estadoBranch,
                        cmBranch.nombre AS ciudadBranch,
                        he.fecha AS fechaRegistro,
                        pxp.nombreProducto,
                        cp.idx AS idxProduct
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN cat_clasificacion cc
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_direccion cd
            ON (ped.idDireccionEntrega = cd.idDireccion AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN persona p
            ON (cd.idPersonaContacto = p.idPersona) LEFT JOIN cat_clasificacion ccPayment
            ON (ord.idMetodoPago = ccPayment.idClasificacion AND ccPayment.activo = 1 AND ccPayment.borrado = 0) INNER JOIN usuario user
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN medio_contacto mcPersonUserMail
            ON (pOrder.idx = mcPersonUserMail.idx AND mcPersonUserMail.etiqueta = 'email') LEFT JOIN medio_contacto mcPersonUserCel
            ON (pOrder.idx = mcPersonUserCel.idx AND mcPersonUserCel.etiqueta = 'celular') LEFT JOIN tarjeta card
            ON (ord.idTarjeta = card.idTarjeta AND card.borrado = 0) LEFT JOIN rfc r
            ON (ped.idRFC = r.idRFC AND r.activo = 1 AND r.borrado = 0) INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN historico_estatus he
            ON (he.tabla = 'pedido' AND he.idEstatus = (SELECT idEstatus FROM cat_estatus WHERE clave = 'REGISTERED') AND he.idRegistro = ped.idPedido) INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
            ON (pxp.idProducto = cp.idProducto AND cp.activo = 1 AND cp.borrado = 0) INNER JOIN cat_direccion cdBranch
            ON (cs.idx = cdBranch.idx AND cdBranch.activo = 1 AND cdBranch.borrado = 0 ) LEFT JOIN cat_localidad clBranch
            ON (cdBranch.idLocalidad = clBranch.idLocalidad) LEFT JOIN cat_municipio cmBranch
            ON (clBranch.idMunicipio = cmBranch.idMunicipio) LEFT JOIN cat_estado ceBranch
            ON (cmBranch.idEstado = ceBranch.idEstado) LEFT JOIN medio_contacto mcBranchTel
            ON (cs.idx = mcBranchTel.idx AND mcBranchTel.etiqueta = 'telefono' AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_localidad clCustomer
            ON (cd.idLocalidad = clCustomer.idLocalidad) LEFT JOIN cat_municipio cmCustomer
            ON (clCustomer.idMunicipio = cmCustomer.idMunicipio) LEFT JOIN cat_estado ceCustomer
            ON (cmCustomer.idEstado = ceCustomer.idEstado) INNER JOIN servicio_x_sucursal sxs
            ON (sxs.idSucursal = cs.idSucursal AND sxs.activo = 1 AND sxs.borrado = 0) INNER JOIN cat_servicio cServ
            ON (cServ.idServicio = sxs.idServicio AND cServ.activo = 1 AND cServ.borrado = 0)

        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getOrderDataOpt($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        $extras['groupBy'] = "pxp.idProducto";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";

        if ( isset($filtros['numOrden']) ) 
            $condicion[] = " ped.numOrden = '{$filtros['numOrden']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['numOrden_NOT_NULL']) ) 
            $condicion[] = " ord.numOrden IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        ped.idUsuario,
                        ped.idx AS idxOrder,
                        ped.idModalidadEntrega,
                        ped.requiereFactura,
                        ord.idMetodoPago,
                        ord.idTarjeta,
                        ped.idRFC,
                        ped.idUsoCFDI,
                        ped.idDireccionEntrega,
                        ped.costoEnvio,
                        ped.total,
                        ped.service_level_code,
                        ped.provider
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN cat_clasificacion cc
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_direccion cd
            ON (ped.idDireccionEntrega = cd.idDireccion AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN persona p
            ON (cd.idPersonaContacto = p.idPersona) LEFT JOIN cat_clasificacion ccPayment
            ON (ord.idMetodoPago = ccPayment.idClasificacion AND ccPayment.activo = 1 AND ccPayment.borrado = 0) INNER JOIN usuario user
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN medio_contacto mcPersonUserMail
            ON (pOrder.idx = mcPersonUserMail.idx AND mcPersonUserMail.etiqueta = 'email') LEFT JOIN medio_contacto mcPersonUserCel
            ON (pOrder.idx = mcPersonUserCel.idx AND mcPersonUserCel.etiqueta = 'celular') LEFT JOIN tarjeta card
            ON (ord.idTarjeta = card.idTarjeta AND card.borrado = 0) LEFT JOIN rfc r
            ON (ped.idRFC = r.idRFC AND r.activo = 1 AND r.borrado = 0) INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
            ON (pxp.idProducto = cp.idProducto AND cp.activo = 1 AND cp.borrado = 0) INNER JOIN cat_direccion cdBranch
            ON (cs.idx = cdBranch.idx AND cdBranch.activo = 1 AND cdBranch.borrado = 0 ) LEFT JOIN cat_localidad clBranch
            ON (cdBranch.idLocalidad = clBranch.idLocalidad) LEFT JOIN cat_municipio cmBranch
            ON (clBranch.idMunicipio = cmBranch.idMunicipio) LEFT JOIN cat_estado ceBranch
            ON (cmBranch.idEstado = ceBranch.idEstado) LEFT JOIN medio_contacto mcBranchTel
            ON (cs.idx = mcBranchTel.idx AND mcBranchTel.etiqueta = 'telefono' AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_localidad clCustomer
            ON (cd.idLocalidad = clCustomer.idLocalidad) LEFT JOIN cat_municipio cmCustomer
            ON (clCustomer.idMunicipio = cmCustomer.idMunicipio) LEFT JOIN cat_estado ceCustomer
            ON (cmCustomer.idEstado = ceCustomer.idEstado) LEFT JOIN servicio_x_sucursal sxs
            ON (sxs.idSucursal = cs.idSucursal AND sxs.activo = 1 AND sxs.borrado = 0) LEFT JOIN cat_servicio cServ
            ON (cServ.idServicio = sxs.idServicio AND cServ.activo = 1 AND cServ.borrado = 0) LEFT JOIN atributo_valor avPMethod
            ON (card.idx = avPMethod.idx AND avPMethod.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'PAYMENT_METHODID')) 
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getOrdersCurrent($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        //$extras['groupBy'] = "pxp.idProducto";
        $extras['groupBy'] = "ped.idPedido, ped.idSucursal";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        ped.idUsuario,
                        ped.idx AS idxOrder,
                        ped.idModalidadEntrega,
                        ped.requiereFactura,
                        ord.idMetodoPago,
                        ord.idTarjeta,
                        ped.idRFC,
                        ped.idUsoCFDI,
                        ped.idDireccionEntrega,
                        ped.costoEnvio,
                        ped.total,
                        ped.service_level_code,
                        ped.provider
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN cat_clasificacion cc
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_direccion cd
            ON (ped.idDireccionEntrega = cd.idDireccion AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN persona p
            ON (cd.idPersonaContacto = p.idPersona) LEFT JOIN cat_clasificacion ccPayment
            ON (ord.idMetodoPago = ccPayment.idClasificacion AND ccPayment.activo = 1 AND ccPayment.borrado = 0) INNER JOIN usuario user
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN medio_contacto mcPersonUserMail
            ON (pOrder.idx = mcPersonUserMail.idx AND mcPersonUserMail.etiqueta = 'email') LEFT JOIN medio_contacto mcPersonUserCel
            ON (pOrder.idx = mcPersonUserCel.idx AND mcPersonUserCel.etiqueta = 'celular') LEFT JOIN tarjeta card
            ON (ord.idTarjeta = card.idTarjeta AND card.borrado = 0) LEFT JOIN rfc r
            ON (ped.idRFC = r.idRFC AND r.activo = 1 AND r.borrado = 0) INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN cat_direccion cdBranch
            ON (cs.idx = cdBranch.idx AND cdBranch.activo = 1 AND cdBranch.borrado = 0 ) LEFT JOIN cat_localidad clBranch
            ON (cdBranch.idLocalidad = clBranch.idLocalidad) LEFT JOIN cat_municipio cmBranch
            ON (clBranch.idMunicipio = cmBranch.idMunicipio) LEFT JOIN cat_estado ceBranch
            ON (cmBranch.idEstado = ceBranch.idEstado) LEFT JOIN medio_contacto mcBranchTel
            ON (cs.idx = mcBranchTel.idx AND mcBranchTel.etiqueta = 'telefono' AND cs.activo = 1 AND cs.borrado = 0) LEFT JOIN cat_localidad clCustomer
            ON (cd.idLocalidad = clCustomer.idLocalidad) LEFT JOIN cat_municipio cmCustomer
            ON (clCustomer.idMunicipio = cmCustomer.idMunicipio) LEFT JOIN cat_estado ceCustomer
            ON (cmCustomer.idEstado = ceCustomer.idEstado) INNER JOIN servicio_x_sucursal sxs
            ON (sxs.idSucursal = cs.idSucursal AND sxs.activo = 1 AND sxs.borrado = 0) INNER JOIN cat_servicio cServ
            ON (cServ.idServicio = sxs.idServicio AND cServ.activo = 1 AND cServ.borrado = 0) LEFT JOIN atributo_valor avPMethod
            ON (card.idx = avPMethod.idx AND avPMethod.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'PAYMENT_METHODID')) INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) 
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getOrderPaymentCard($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];
        $extras['groupBy'] = "pxp.idProducto";

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        if ( isset($filtros['status']) ) 
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) ) 
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['idDireccionEntrega_NOT_NULL']) ) 
            $condicion[] = " ped.idDireccionEntrega IS NOT NULL";

        if ( isset($filtros['registrationDate']) ) 
            $condicion[] = " he.fecha >= '{$filtros['registrationDate']}' ";

        if ( isset($filtros['nameProduct']) )
			$condicion[] = "(ped.numOrden LIKE '%{$filtros['nameProduct']}%' OR pxp.nombreProducto LIKE '%{$filtros['nameProduct']}%' OR cp.clave LIKE '%{$filtros['nameProduct']}%' )";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        ped.numOrden
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped LEFT JOIN orden ord
            ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) LEFT JOIN cat_clasificacion cc 
            ON (ped.idModalidadEntrega = cc.idClasificacion AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido) INNER JOIN usuario user 
            ON (ped.idUsuario = user.idUsuario AND user.activo = 1 AND user.borrado = 0) INNER JOIN persona pOrder 
            ON (user.idPersona = pOrder.idPersona) LEFT JOIN tarjeta card 
            ON (ord.idTarjeta = card.idTarjeta AND card.borrado = 0) LEFT JOIN atributo_valor av 
            ON (card.idx = av.idx AND av.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'CARD_STRIPEID')) LEFT JOIN atributo_valor avStripeId 
            ON (pOrder.idx = avStripeId.idx AND avStripeId.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'USER_STRIPEID')) LEFT JOIN atributo_valor avPMethod 
            ON (card.idx = avPMethod.idx AND avPMethod.idAtributo = (SELECT idAtributo FROM cat_atributo WHERE clave = 'PAYMENT_METHODID')) INNER JOIN producto_x_pedido pxp 
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) 
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getOrderAmount($filtros = array(), $extras = array()) {

        $condicion = array();
                
        if ( isset($filtros['idUsuario']) )
            $condicion[] = "ped.idUsuario = '" . $filtros['idUsuario'] . "'";

        if ( isset($filtros['idPedido']) )
            $condicion[] = "pxp.idPedido = '" . $filtros['idPedido'] . "'";
            
        if ( isset($filtros['paymentIntentId']) ) 
            $condicion[] = " ord.paymentIntentId = '{$filtros['paymentIntentId']}' ";

        if ( isset($filtros['idPedido_IN']) )
            $condicion[] = "pxp.idPedido IN (" . $filtros['idPedido_IN'] . ")";

        if ( isset($filtros['status']) )
            $condicion[] = "vue.clave = '" . $filtros['status'] . "'";

        if ( isset($filtros['status_NOT']) )
            $condicion[] = "vue.clave != '" . $filtros['status_NOT'] . "'";

        if ( isset($filtros['branch']) )
            $condicion[] = "branch.clave = '" . $filtros['branch'] . "'";

        if ( isset($filtros['cveProducto']) )
            $condicion[] = "cp.clave = '" . $filtros['cveProducto'] . "'";

        $condicion[] = "ped.borrado = '0'";
        $extras['groupBy'] = "ped.idPedido";
            
        $sql = "SELECT 
                        ped.idPedido,
                        ped.idUsuario,
                        ped.idSucursal,
                        ped.total
                FROM pedido ped LEFT JOIN orden ord
                ON (ped.numOrden = ord.numOrden AND ord.borrado = 0) INNER JOIN producto_x_pedido pxp
                ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
                ON (pxp.idProducto = cp.idProducto AND cp.activo = 1 AND cp.borrado = 0) LEFT JOIN archivo a
                ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN view_ultimo_estatus vue 
                ON ( vue.tabla = 'pedido' AND vue.idRegistro = ped.idPedido ) INNER JOIN producto_x_sucursal pxs
                ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
                ON (pxs.idSucursal = branch.idSucursal AND ped.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)
            ";
                                
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getProdsOrderEvaluation($filtros = array(), $extras = array()) {
		
		$condicion = array();

		if ( isset($filtros['idPedido']) )
			$condicion[] = "prodOrd.idPedido = " . $filtros['idPedido'];

		$condicion[] = "prodOrd.borrado = 0";

		$sql = " SELECT 
					prodOrd.*,
                    prod.*,
                    exp.idEvaluacionProducto,
                    exp.evaluacion,
                    exp.idPedido AS idPedidoEva,
                    exp.idProducto AS idProductoEva,
                    exp.comentario AS comentario,
					prod.clave AS cveArticulo,
					prodOrd.precio AS checkoutPrice
				FROM producto_x_pedido AS prodOrd
				INNER JOIN cat_producto AS prod
				ON (prodOrd.idProducto = prod.idProducto) LEFT JOIN evaluacion_x_producto AS exp
				ON (exp.idProducto = prodOrd.idProducto) AND exp.idPedido = prodOrd.idPedido
			";			

		if ( $condicion )
			$sql .= " WHERE " . implode(' AND ', $condicion);

		return parent::execute_query($sql, $extras);
	}

    function getEvaluationOrder($filtros = array(), $extras = array()) {
        
        $condicion = array();
        $extras['groupBy'] = 'ped.idPedido';
        
        if ( isset($filtros['idPedido']) )
            $condicion[] = "ped.idPedido = '{$filtros['idPedido']}'";    
			
		if ( isset($filtros['idUsuario']) )
            $condicion[] = "ped.idUsuario = '{$filtros['idUsuario']}'";    
        
        $sql = "SELECT IFNULL(avg(evaluacion), 0) as evaluacion,
		               ped.idPedido
			    FROM pedido ped INNER JOIN evaluacion_x_producto exp
			    ON ( exp.idPedido = ped.idPedido )
        ";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);

		if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );
                
        return parent::execute_query($sql, $extras);
    }

    function getEvaluationProduct($filtros = array(), $extras = array()) {
        
        $condicion = array();
        $extras['groupBy'] = 'cp.idEmpresa';
        
        if ( isset($filtros['cve_articulo']) )
            $condicion[] = "cp.clave = '{$filtros['cve_articulo']}'";

        if ( isset($filtros['esEvaluacionEntrega']) )
            $condicion[] = "exp.esEvaluacionEntrega = '{$filtros['esEvaluacionEntrega']}'";

		if (isset($filtros['cve_articulo_IN']))
		    $condicion[] = " cp.clave IN ('{$filtros['cve_articulo_IN']}') ";

        if ( !empty($filtros['idProducto_NULL']) )
            $condicion[] = "exp.idProducto IS NULL";
                                
        $sql = "
					SELECT cp.idProducto,
                           cp.clave, 
                           ped.idSucursal,
						   FLOOR(IFNULL(avg(exp.evaluacion), 0)) as promedio,
						   IFNULL(avg(exp.evaluacion), 0) as promedioDecimal,
						   COUNT(exp.idProducto) as totalProductos
					FROM evaluacion_x_producto exp LEFT JOIN cat_producto cp
					ON (exp.idProducto = cp.idProducto) INNER JOIN pedido ped
                    ON (exp.idPedido = ped.idPedido)
        		";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
        
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );
        
        if ( !empty($extras['orderBy']) )
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );
        
        if ( !empty($extras['limit']) ) {
            
            $offset = empty($extras['offset']) ? 0 : $extras['offset'];
            $sql .= " LIMIT {$offset}, {$extras['limit']}";
        }
        
        return parent::execute_query($sql, $extras);
    }

    function getEvaluationBranch($filtros = array(), $extras = array()) {
        
        $condicion = array();
        $extras['groupBy'] = 'ped.idSucursal';
        
        if ( isset($filtros['esEvaluacionEntrega']) )
            $condicion[] = "exp.esEvaluacionEntrega = '{$filtros['esEvaluacionEntrega']}'";

        if ( !empty($filtros['idProducto_NULL']) )
            $condicion[] = "exp.idProducto IS NULL";
                                
        $sql = "
                    SELECT ped.idSucursal,
                        FLOOR(IFNULL(avg(exp.evaluacion), 0)) as promedio,
                        IFNULL(avg(exp.evaluacion), 0) as promedioDecimal,
                        COUNT(exp.comentario) as totalComentario
                    FROM evaluacion_x_producto exp INNER JOIN pedido ped
                    ON (exp.idPedido = ped.idPedido)
        		";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
        
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );
                
        return parent::execute_query($sql, $extras);
    }

    function getReviewsProduct($filtros = array(), $extras = array()) {
        
        $condicion = array();
        
        if ( isset($filtros['cve_articulo']) )
            $condicion[] = "cp.clave = '{$filtros['cve_articulo']}'";

		if ( isset($filtros['idSucursal']) )
            $condicion[] = "branch.idSucursal = '{$filtros['idSucursal']}'";

		if ( isset($filtros['validado']) )
            $condicion[] = "exp.validado = '{$filtros['validado']}'";
                                
        $sql = "SELECT exp.*,
				       cp.clave,
				       CONCAT_WS(' ', client.nombre, client.apellidos) AS nombreCliente,
				       client.idx as IdRepositorioFoto,
       			       vue.fecha
				FROM evaluacion_x_producto exp INNER JOIN cat_producto cp
				ON (exp.idProducto = cp.idProducto) LEFT JOIN usuario AS user
				ON (exp.idUsuario = user.idUsuario) LEFT JOIN persona AS client
				ON (user.idPersona = client.idPersona) LEFT JOIN view_ultimo_estatus vue 
				ON (vue.tabla = 'evaluacion_x_producto' AND vue.idRegistro = exp.idEvaluacionProducto ) LEFT JOIN producto_x_sucursal pxs
                ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) LEFT JOIN cat_sucursal branch
                ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)
        	";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
        
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );
        
        if ( !empty($extras['orderBy']) )
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );
        
        if ( !empty($extras['limit']) ) {
            
            $offset = empty($extras['offset']) ? 0 : $extras['offset'];
            $sql .= " LIMIT {$offset}, {$extras['limit']}";
        }
        
        return parent::execute_query($sql, $extras);
    }

    function getReason($filtros = array(), $extras = array()) {
        
        $condicion = array();

        $condicion = ["activo = '1' AND borrado = 0"];

        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "idEmpresa = '{$filtros['idEmpresa']}'";

        if ( isset($filtros['idClasificacion']) )
            $condicion[] = "idClasificacion = '{$filtros['idClasificacion']}'";
                                        
        $sql = "SELECT * FROM cat_motivo";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
                
        if ( !empty($extras['orderBy']) )
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );
                
        return parent::execute_query($sql, $extras);
    }

    function getBanks($filtros = array(), $extras = array()) {
        
        $condicion = array();

        $condicion = ["activo = '1' AND borrado = 0"];

        if ( isset($filtros['idEmpresa']) )
            $condicion[] = "idEmpresa = '{$filtros['idEmpresa']}'";
                                        
        $sql = "SELECT * FROM cat_banco";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
                
        if ( !empty($extras['orderBy']) )
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );
                
        return parent::execute_query($sql, $extras);
    }

    function searchProductVariantBranch($filtros = array(), $extras = array()) {

        $condicion = array();

        if ( isset($filtros['idProducto']) ) $condicion[] = " pxs.idProducto = '{$filtros['idProducto']}' ";

        if ( isset($filtros['cveVariante']) ) $condicion[] = " ca.clave = '{$filtros['cveVariante']}' ";

        if ( isset($filtros['idProductoSucursal']) ) $condicion[] = " pxs.idProductoSucursal = '{$filtros['idProductoSucursal']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "pxs.*, av.idAtributoOpcion, ca.clave AS cveVariante, ao.clave, ao.nombre, ao.abreviatura, ao.valor";

        $sql = "
            SELECT {$campos}
            FROM producto_x_sucursal pxs INNER JOIN atributo_valor av
            ON (pxs.idProductoSucursal = av.idProductoSucursal) INNER JOIN atributo_opcion ao
            ON (av.idAtributoOpcion = ao.idAtributoOpcion) INNER JOIN cat_atributo ca
            ON (ca.idAtributo = ao.idAtributo)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function getProductsOrderHistory($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ped.activo = '1' AND ped.borrado = 0"];

        if ( isset($filtros['idPedido']) ) 
            $condicion[] = " ped.idPedido = '{$filtros['idPedido']}' ";
        
        if ( isset($filtros['idUsuario']) ) 
            $condicion[] = " ped.idUsuario = '{$filtros['idUsuario']}' ";

        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ped.idPedido,
                        pxp.idProducto,
                        pxp.idProductoSucursal,
                        pxp.nombreProducto,
                        cp.clave AS cveProduct,
                        cp.idx AS idxProduct,
                        cs.clave AS cveSucursal,
                        cs.nombre AS sucursal,
                        cm.nombre AS marca
                ";

        $sql = "
            SELECT {$campos}
            FROM pedido ped INNER JOIN producto_x_pedido pxp
            ON (ped.idPedido = pxp.idPedido AND pxp.borrado = 0) INNER JOIN cat_producto cp
            ON (cp.idProducto = pxp.idProducto AND cp.borrado = 0) INNER JOIN cat_sucursal cs
            ON (ped.idSucursal = cs.idSucursal) INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cm.activo = 1 AND cm.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getAttribute($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["ca.activo = '1' AND ca.borrado = 0 AND cva.activo = 1 AND cva.borrado = 0"];

        if ( isset($filtros['valor']) ) 
            $condicion[] = "cva.valor = '{$filtros['valor']}' ";

        if ( isset($filtros['clave']) ) 
            $condicion[] = "ca.clave = '{$filtros['clave']}' ";
        
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        ca.*, cva.*
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_atributo ca 
            LEFT JOIN atributo_valor cva ON (cva.idAtributo = ca.idAtributo AND ca.activo = 1 AND ca.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);
    
        return parent::execute_query($sql, $extras);
    }

    function lastStatus($filtros = array(), $extras = array()) {

        $condicion = array("ISNULL(t2.idHistorico)");

        if (isset($filtros['tabla']))
            $condicion[] = "t1.tabla = '{$filtros['tabla']}'";
        
        if (isset($filtros['id']))
            $condicion[] = "t1.idRegistro = " . $filtros['id'];
        
        if (isset($filtros['id_IN']))
            $condicion[] = "t1.idRegistro IN({$filtros['id_IN']})";
            
        if (isset($filtros['clave_IN']))
            $condicion[] = "e.clave IN({$filtros['clave_IN']})";
        
        $sql = "
            SELECT t1.*,
                   e.clave as claveEstatus,
                   e.nombre as estatus
            FROM historico_estatus t1 LEFT JOIN historico_estatus t2 
            ON (t1.tabla = t2.tabla and t1.idRegistro = t2.idRegistro and t1.idHistorico < t2.idHistorico) INNER JOIN cat_estatus e 
            ON (e.idEstatus = t1.idEstatus)                
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        return parent::execute_query($sql, $extras);
    }

    function getAttributes($filtros = array(), $extras = array()) {

        $condicion = array();

        $condicion = ["ca.activo = 1 AND ca.borrado = 0 AND av.activo = 1 AND av.borrado = 0"];

        $extras['groupBy'] = 'ca.idAtributo';

        if ( isset($filtros['clave']) )
            $condicion[] = "ca.clave LIKE '%{$filtros['clave']}%'";

        if ( isset($filtros['nombre']) )
            $condicion[] = "ca.nombre LIKE '%{$filtros['nombre']}%'";

        if ( isset($filtros['idContexto']) )
            $condicion[] = "ca.idContexto = '{$filtros['idContexto']}'";

        if ( isset($filtros['idEstatus']) )
            $condicion[] = "ca.activo = '{$filtros['idEstatus']}'";

        if ( isset($filtros['idAtributo']) )
            $condicion[] = "ca.idAtributo = '{$filtros['idAtributo']}'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                                ca.*,
                                av.idAtributoValor,
                                av.idx AS idxBranch,
                                av.valor, 
                                av.textHtml
                                        ';        

        $sql = "
                SELECT {$campos}
                FROM cat_atributo ca LEFT JOIN atributo_valor av
                ON (ca.idAtributo = av.idAtributo)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getCategory($filtros = array(), $extras = array()) {

        $condicion = ['cc.activo = 1 AND cc.borrado = 0'];
        $extras['groupBy'] = 'cc.idCategoria';

        if ( isset($filtros['idCategoriaPadre_ISNULL']) )
            $condicion[] = "cc.idCategoriaPadre IS NULL";

        if ( isset($filtros['idCategoriaPadre_ISNOTNULL']) )
            $condicion[] = "cc.idCategoriaPadre IS NOT NULL";
              
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                    cc.*,
                    ccP.nombre AS categoriaPadre

                ";        

        $sql = "
                SELECT {$campos}
                FROM cat_categoria cc LEFT JOIN cat_categoria ccP
                ON ( ccP.idCategoriaPadre = cc.idCategoria)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function getStatusProduct($filtros = array(), $extras = array()) {
        
        $condicion = array();
       
        if ( isset($filtros['idProductoPedido_IN']) )
			$condicion[] = "idProductoPedido IN(" . $filtros['idProductoPedido_IN'] . ")";       
        
        $sql = " SELECT exp.*,
					    vue.clave as claveEstatusRegistro,
					    vue.estatus as nombreEstatusRegistro
				FROM estatus_x_pedido exp LEFT JOIN historico_estatus he 
				ON ( he.idHistorico = exp.idHistorico ) LEFT JOIN view_ultimo_estatus vue 
				ON ( he.tabla = vue.tabla AND vue.idHistorico = he.idHistorico )
        	";
        
        if ( $condicion )
            $sql .= " WHERE " . implode(' AND ', $condicion);
                
        return parent::execute_query($sql, $extras);
    }

    function viewProductsTemp($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pt.activo = 1 AND pt.borrado = 0 AND pxs.existencias > 0 AND branch.ecommerce = '1'"];

        if( empty($extras['groupBy']) )
		    $extras['groupBy'] = ['pxs.idSucursal, pxs.idProducto'];
        
        if ( isset($filtros['idUsuario']) ) $condicion[] = " pt.idUsuario = '{$filtros['idUsuario']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cp.idCategoria,
                        cs.nombre AS servicio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cc.clave AS cveCategoria,
                        cc.nombre AS categoria,
                        ccSub.clave AS cveSubcategoria,
                        ccSub.nombre AS subcategoria,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        ccUnit.nombre AS unidadMedida,
                        emp.nombreComercial AS empresa,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        branch.descripcion AS descSucursal,
                        mcRep.valor AS emailRep,
                        branch.horario,
                        branch.ecommerce,
                        mcBranch.valor AS telefonoBranch,
                        cd.direccion,
			            CONCAT(a.ruta, '', a.nombreFS) as archivo,
                        CONCAT(aBranch.ruta, '', aBranch.nombreFS) as archivoSucursal,
                        prom.tipo AS promocion,
                        pt.idProdTemp
                ";

        $sql = "
            SELECT {$campos}
            FROM cat_producto cp INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria AND ccSub.activo = 1 AND ccSub.borrado = 0) LEFT JOIN cat_clasificacion ccUnit
            ON (ccUnit.idClasificacion = cp.idUnidadMedida AND ccUnit.activo = 1 AND ccUnit.borrado = 0) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio AND cs.activo = 1 AND cs.borrado = 0) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa AND emp.activo = 1 AND emp.borrado = 0) INNER JOIN contacto_empresa ce
            ON (emp.idEmpresa = ce.idEmpresa) INNER JOIN persona p
            ON (p.idPersona = ce.idPersona) INNER JOIN medio_contacto mcRep
            ON (mcRep.idx = p.idx AND mcRep.etiqueta = 'email') INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) INNER JOIN cat_direccion cd
            ON (branch.idx = cd.idx AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN archivo a
            ON (cp.idx = a.idx AND a.activo = 1 AND a.borrado = 0) LEFT JOIN ( SELECT idx, ruta, nombreFS, activo, borrado, MAX(idArchivo) AS latest FROM archivo GROUP BY idArchivo ) aBranch
            ON (aBranch.idx = branch.idx AND aBranch.activo = 1 AND aBranch.borrado = 0) LEFT JOIN medio_contacto mcBranch
            ON (mcBranch.idx = branch.idx AND mcBranch.activo = 1 AND mcBranch.borrado = 0) LEFT JOIN atributo_combinacion ac
            ON (cp.idProducto = ac.idProducto AND ac.idProducto = pxs.idProducto AND ac.activo = 1 AND ac.borrado = 0) LEFT JOIN atributo_combinacion_detalle acd
            ON (ac.idAtributoCombinacion = acd.idAtributoCombinacion AND acd.activo = 1 AND acd.borrado = 0) LEFT JOIN atributo_valor av
            ON (av.idAtributo = acd.idAtributo AND av.activo = 1 AND av.borrado = 0 AND cp.idx = av.idx) LEFT JOIN atributo_opcion ao
            ON (av.idAtributo = ao.idAtributo AND av.idAtributoOpcion = ao.idAtributoOpcion AND ao.activo = 1 AND ao.borrado = 0) LEFT JOIN promocion_producto pp
            ON (pp.idProducto = cp.idProducto) LEFT JOIN promocion_seleccion ps
            ON (ps.idPromSel = pp.idPromSel) LEFT JOIN promocion prom
            ON (prom.idPromocion = ps.idPromocion) LEFT JOIN promocion_sucursal promxs
            ON (promxs.idPromocion = prom.idPromocion AND promxs.idSucursal = pxs.idSucursal) INNER JOIN product_temp pt
            ON (cp.idProducto = pt.idProducto)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    function viewProductsBuy($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1' AND vue.clave != 'REGISTERED'"];

        if( empty($extras['groupBy']) )
		    $extras['groupBy'] = ['pxp.idProducto'];
        
        if ( isset($filtros['idUsuario']) ) $condicion[] = " pt.idUsuario = '{$filtros['idUsuario']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        pxp.*, 
                        vue.estatus,
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cp.idCategoria,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cc.clave AS cveCategoria,
                        cc.nombre AS categoria,
                        ccSub.clave AS cveSubcategoria,
                        ccSub.nombre AS subcategoria,
                        ccUnit.nombre AS unidadMedida,
                        cs.nombre AS servicio,
                        emp.nombreComercial AS empresa,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        branch.descripcion AS descSucursal,
                        branch.horario,
                        branch.ecommerce,
                        mcRep.valor AS emailRep,
                        COUNT(pxp.idProducto) AS numProds     
                ";

        $sql = "
            SELECT {$campos}
            FROM producto_x_pedido pxp INNER JOIN pedido p
            ON (p.idPedido = pxp.idPedido) LEFT JOIN view_ultimo_estatus vue 
            ON (vue.tabla = 'pedido' AND vue.idRegistro = p.idPedido ) INNER JOIN cat_producto cp
            ON (cp.idProducto = pxp.idProducto) INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa AND cm.activo = 1 AND cm.borrado = 0) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria AND cc.activo = 1 AND cc.borrado = 0) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria AND ccSub.activo = 1 AND ccSub.borrado = 0) LEFT JOIN cat_clasificacion ccUnit
            ON (ccUnit.idClasificacion = cp.idUnidadMedida AND ccUnit.activo = 1 AND ccUnit.borrado = 0) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio AND cs.activo = 1 AND cs.borrado = 0) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa AND emp.activo = 1 AND emp.borrado = 0) INNER JOIN contacto_empresa ce
            ON (emp.idEmpresa = ce.idEmpresa) INNER JOIN persona person
            ON (person.idPersona = ce.idPersona) INNER JOIN medio_contacto mcRep
            ON (mcRep.idx = person.idx AND mcRep.etiqueta = 'email') INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND pxp.idProductoSucursal = pxs.idProductoSucursal AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0)      
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 6) . " ";

        return parent::execute_query($sql, $extras);
    }

    function viewProductsPopulars($filtros = array(), $extras = array()) {

        $condicion = array();
        $condicion = ["cp.activo = '1' AND cp.borrado = '0' AND pxs.existencias > 0 AND branch.ecommerce = '1'"];

        if( empty($extras['groupBy']) )
		    $extras['groupBy'] = ['pxs.idSucursal, pxs.idProducto'];

        if( empty($extras['orderBy']) )
		    $extras['orderBy'] = ['promedioDecimal DESC'];
        
        if ( isset($filtros['idUsuario']) ) $condicion[] = " pt.idUsuario = '{$filtros['idUsuario']}' ";
            
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                        cp.idProducto,
                        cp.clave,
                        cp.idx,
                        cp.nombre,
                        cp.nombreCorto,
                        cp.descripcion,
                        cp.idServicio,
                        cp.idMarca,
                        cp.idCategoria,
                        cp.longitud,
                        cp.altura,
                        cp.profundidad,
                        cp.peso,
                        cp.envioADomicilio,
                        cm.clave AS cveMarca,
                        cm.nombre AS marca,
                        cc.clave AS cveCategoria,
                        cc.nombre AS categoria,
                        ccSub.clave AS cveSubcategoria,
                        ccSub.nombre AS subcategoria,
                        ccUnit.nombre AS unidadMedida,
                        cs.nombre AS servicio,
                        emp.nombreComercial AS empresa,
                        mcRep.valor AS emailRep,
                        pxs.idProductoSucursal,
                        pxs.precio,
                        pxs.almacen,
                        pxs.existencias,
                        branch.idSucursal,
                        branch.clave AS claveSucursal,
                        branch.nombre AS sucursal,
                        branch.descripcion AS descSucursal,
                        branch.horario,
                        branch.ecommerce,
                        mcBranch.valor AS telefonoBranch,
                        cd.direccion,
                        FLOOR(IFNULL(avg(exp.evaluacion), 0)) as promedio,
                        IFNULL(avg(exp.evaluacion), 0) as promedioDecimal,
                        COUNT(DISTINCT(exp.idProducto)) as totalProductos
                ";

        $sql = "
            SELECT {$campos}
            FROM evaluacion_x_producto exp LEFT JOIN cat_producto cp
            ON (exp.idProducto = cp.idProducto AND exp.idProducto IS NOT NULL) INNER JOIN cat_marca cm
            ON (cp.idMarca = cm.idMarca AND cp.idEmpresa = cm.idEmpresa) LEFT JOIN cat_categoria cc
            ON (cp.idCategoria = cc.idCategoria) LEFT JOIN cat_categoria ccSub
            ON (ccSub.idCategoria = cp.idSubCategoria) LEFT JOIN cat_clasificacion ccUnit
            ON (ccUnit.idClasificacion = cp.idUnidadMedida) INNER JOIN cat_servicio cs
            ON (cp.idServicio = cs.idServicio) INNER JOIN empresa emp
            ON (cp.idEmpresa = emp.idEmpresa) INNER JOIN contacto_empresa ce
            ON (emp.idEmpresa = ce.idEmpresa) INNER JOIN persona p
            ON (p.idPersona = ce.idPersona) INNER JOIN medio_contacto mcRep
            ON (mcRep.idx = p.idx AND mcRep.etiqueta = 'email') INNER JOIN producto_x_sucursal pxs
            ON (cp.idProducto = pxs.idProducto AND exp.idProducto = pxs.idProducto AND pxs.activo = 1 AND pxs.borrado = 0) INNER JOIN cat_sucursal branch
            ON (pxs.idSucursal = branch.idSucursal AND branch.activo = 1 AND branch.borrado = 0) INNER JOIN cat_direccion cd
            ON (branch.idx = cd.idx AND cd.activo = 1 AND cd.borrado = 0) LEFT JOIN medio_contacto mcBranch
            ON (mcBranch.idx = branch.idx AND mcBranch.activo = 1 AND mcBranch.borrado = 0)
        ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 100) . " ";

        return parent::execute_query($sql, $extras);
    }

    
}
