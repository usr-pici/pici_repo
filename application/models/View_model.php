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
    
    function getForms($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["borrado = 0"];

        if ( !empty($filtros['idFormulario']) )
            $condicion[] = "idFormulario = '{$filtros['idFormulario']}'";

        if ( !empty($filtros['clave']) )
            $condicion[] = "clave LIKE '%{$filtros['clave']}%'";

        if ( !empty($filtros['nombre']) )
            $condicion[] = "nombre LIKE '%{$filtros['nombre']}%'";

        if ( isset($filtros['activo']) )
            $condicion[] = "activo = '{$filtros['activo']}'";

        if ( isset($filtros['fechaOrdenPar']) )
			$condicion[] = "DATE(vigenciaIni) between " . $filtros['fechaOrdenPar'];

        if ( isset($filtros['fechaIniSolo']) )
			$condicion[] = "vigenciaIni >= " . $filtros['fechaIniSolo'];

        if ( isset($filtros['fechaFinSolo']) )
			$condicion[] = "vigenciaFin <= " . $filtros['fechaFinSolo'];

        $campos = !empty($extras['campos']) ? $extras['campos'] : '*';        

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

        if ( !empty($filtros['idPregunta_IN']) )
            $condicion[] = " p.idPregunta IN ({$filtros['idPregunta_IN']}) ";

        if ( isset($filtros['option_IN']) )
            $condicion[] = " ctc.clave IN ({$filtros['option_IN']}) ";

        if ( isset($filtros['idPreguntaNot']) )
            $condicion[] = " p.idPregunta NOT IN ({$filtros['idPreguntaNot']}) ";

        if ( !empty($filtros['vigente']) )
            $condicion[] = "p.borrado = '0' AND p.activo = '1'";

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

    function showFieldsQuestion($filtros = array(), $extras = array()) {

        $condicion = [];

        //$condicion = ["p.borrado = 0"];

        if ( !empty($filtros['idPreguntaCondicion']) )
            $condicion[] = "pc.idPreguntaCondicion = '{$filtros['idPreguntaCondicion']}'";

        if ( isset($filtros['idPreguntaOpcion']) )
            $condicion[] = "po.idPreguntaOpcion = '{$filtros['idPreguntaOpcion']}'";

        if ( isset($filtros['idPregunta_IN']) )
			$condicion[] = "pc.idPregunta IN ({$filtros['idPregunta_IN']}) ";

        if ( isset($filtros['idPregunta']) )
            $condicion[] = "pc.idPregunta = '{$filtros['idPregunta']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    p.idPregunta,
                    p.etiqueta AS pregunta,
                    po.idPregunta AS idTest,
                    po.idPreguntaOpcion,
                    po.opcion,
                    pc.idPreguntaCondicion,
                    pc.idPregunta AS idPreguntaMostrar,
                    pc.igual,
                    pM.etiqueta AS preguntaMostrar
                ';        

        $sql = "
                SELECT {$campos}
                FROM pregunta p INNER JOIN pregunta_opcion po
                ON (p.idPregunta = po.idPregunta) INNER JOIN pregunta_condicion pc
                ON (pc.idPreguntaOpcion = po.idPreguntaOpcion) INNER JOIN pregunta pM
                ON (pc.idPregunta = pM.idPregunta)

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

        $campos = !empty($extras['campos']) ? $extras['campos'] : '*';        

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

    function getConditionsQuestion($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["pc.borrado = 0"];

        if ( !empty($filtros['idPregunta']) )
            $condicion[] = "pc.idPregunta = '{$filtros['idPregunta']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    pc.*,
                    pCat.idPregunta AS idPreguntaCat,
                    pCat.etiqueta AS pregunta,
                    po.idPreguntaOpcion AS idPreguntaOpcionCat,
                    po.opcion AS opcion,
                    p.idFormulario AS idFormularioPadre
                ';        

        $sql = "
                SELECT {$campos}
                FROM pregunta_condicion pc INNER JOIN pregunta p
                ON (pc.idPregunta = p.idPregunta) INNER JOIN pregunta_opcion po
                ON (pc.idPreguntaOpcion = po.idPreguntaOpcion) INNER JOIN pregunta pCat
                ON (pCat.idPregunta = po.idPregunta)
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
  
    function getPatients($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["patient.borrado = '0'"];

        if ( !empty($filtros['nombre']) )
            $condicion[] = "person.nombre LIKE '%{$filtros['nombre']}%' OR person.apellidos LIKE '%{$filtros['nombre']}%'";

        if ( isset($filtros['idGenero']) )
            $condicion[] = "po.opcion = '{$filtros['idGenero']}'";

        if ( isset($filtros['idClues']) )
            $condicion[] = "rClues.idPreguntaOpcion = '{$filtros['idClues']}'";

        if ( isset($filtros['idPaciente']) )
            $condicion[] = "patient.idPaciente = '{$filtros['idPaciente']}'";

        if ( isset($filtros['fechaOrdenPar']) )
			$condicion[] = "DATE(person.fechaNacimiento) between " . $filtros['fechaOrdenPar'];

        if ( isset($filtros['fechaIniSolo']) )
			$condicion[] = "person.fechaNacimiento >= " . $filtros['fechaIniSolo'];

        if ( isset($filtros['fechaFinSolo']) )
			$condicion[] = "person.fechaNacimiento <= " . $filtros['fechaFinSolo'];

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    patient.*,
                    person.*,
                    ccTypeDoc.nombre AS tipoDocumento,
                    r.idPreguntaOpcion,
                    po.opcion AS genero,
                    rClues.idPreguntaOpcion AS idClues,
                    cc.nombre AS clues
                ';        

        $sql = "
                SELECT {$campos}
                FROM paciente patient INNER JOIN persona person
                ON (patient.idPersona = person.idPersona) LEFT JOIN cat_clasificacion ccTypeDoc
                ON (ccTypeDoc.idClasificacion = person.idTipoDocumento) LEFT JOIN respuesta r
                ON (r.idPaciente = patient.idPaciente AND r.idPregunta = (SELECT idPregunta FROM pregunta WHERE etiqueta = 'Sexo' )) LEFT JOIN pregunta_opcion po
                ON (r.idPreguntaOpcion = po.idPreguntaOpcion) LEFT JOIN respuesta rClues
                ON (rClues.idPaciente = patient.idPaciente AND rClues.idPregunta = (SELECT idPregunta FROM pregunta WHERE etiqueta = 'CLUES' )) LEFT JOIN pregunta_opcion poClues
                ON (rClues.idPreguntaOpcion = poClues.idPreguntaOpcion) LEFT JOIN cat_clues cc
                ON (cc.idClues = rClues.idPreguntaOpcion)
            ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getContacts($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["mc.borrado = '0'"];

        if ( isset($filtros['idPaciente']) )
            $condicion[] = "patient.idPaciente = '{$filtros['idPaciente']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    mc.*,
                    mc.valor AS telefono,
                    mc.etiqueta AS tipo,
                    mc.prioridad AS principal,
                    p.nombre,
                    p.apellidos,
                    patient.idPaciente
                ';        

        $sql = "
                SELECT {$campos}
                FROM medio_contacto mc INNER JOIN persona p
                ON (mc.idx = p.idx) INNER JOIN paciente patient
                ON (p.idPersona = patient.idPersona)
            ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getResponsible($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["cr.borrado = '0'"];

        if ( isset($filtros['idPaciente']) )
            $condicion[] = "patient.idPaciente = '{$filtros['idPaciente']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    cr.*,
                    cr.nombre AS responsable,
                    p.nombre,
                    p.apellidos,
                    patient.idPaciente
                ';        

        $sql = "
                SELECT {$campos}
                FROM cat_responsable cr INNER JOIN persona p
                ON (cr.idx = p.idx) INNER JOIN paciente patient
                ON (p.idPersona = patient.idPersona)
            ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        return parent::execute_query($sql, $extras);
    }

    function getVisits($filtros = array(), $extras = array()) {

        $condicion = [];

        if ( isset($filtros['idPaciente']) )
            $condicion[] = "v.idPaciente = '{$filtros['idPaciente']}'";

        $extras = ["groupBy" => 'v.idVisita, f.idFormulario'];
        $campos = !empty($extras['campos']) ? $extras['campos'] : "
                            v.idVisita,
                            v.idEstudioClues,
                            v.idPaciente,
                            v.numVisita AS visita,
                            f.idFormulario,
                            f.nombre AS formulario
                            ";        

        $sql = "
                SELECT {$campos}
                FROM visita v INNER JOIN estudio_x_clues exc
                ON (v.idEstudioClues = exc.idEstudioClues) INNER JOIN estudio e
                ON (e.idEstudio = exc.idEstudio) INNER JOIN formulario_x_estudio fxe
                ON (fxe.idEstudio = e.idEstudio) INNER JOIN formulario f
                ON (f.idFormulario = fxe.idFormulario) INNER JOIN paciente p
                ON (p.idPaciente = v.idPaciente) LEFT JOIN respuesta resp
                ON (resp.idPaciente = v.idPaciente AND v.idVisita = resp.idVisita AND f.idFormulario = resp.idFormulario)
            ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if ( !empty($extras['groupBy']) )
            $sql .= " GROUP BY " . ( is_array($extras['groupBy']) ? implode(", ", $extras['groupBy']) : $extras['groupBy'] );

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

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

    function getStudyClues($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["exc.activo = 1 AND exc.borrado = 0"];
        
        if ( isset($filtros['idUsuario']) )
            $condicion[] = "exu.idUsuario = '{$filtros['idUsuario']}'";

        if ( isset($filtros['idEstudioUsuario']) )
            $condicion[] = "exu.idEstudioUsuario = '{$filtros['idEstudioUsuario']}'";
      
        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    exu.*, 
                    exc.idEstudioClues,
                    exc.idEstudio,
                    exc.idClues,
                    e.nombre AS estudio,
                    cc.nombre AS clues,
                    CONCAT(e.nombre, " / ", cc.nombre) as estudioClues
                ';        

        $sql = "
                SELECT {$campos}
                FROM estudio_x_usuario exu INNER JOIN estudio_x_clues exc
                ON (exu.idEstudioClues = exc.idEstudioClues) INNER JOIN estudio e
                ON (e.idEstudio = exc.idEstudio) INNER JOIN cat_clues cc
                ON (cc.idClues = exc.idClues)
            ";

        return $this->execute_view($sql, $condicion, $extras);
    }

    function get_regs_binnacle($filtros = array(), $extras = array()) {

        $condicion = [];

        $condicion = ["vue.tabla = 'respuesta'"];

        if ( !empty($filtros['idRegistro_IN']) )
            $condicion[] = "vue.idRegistro IN ({$filtros['idRegistro_IN']}) ";

        if ( !empty($filtros['comentarioISNULL']) )
            $condicion[] = " vue.comentario IS NOT NULL";

        if ( !empty($filtros['cveEstatus']) )
            $condicion[] = "vue.clave = '{$filtros['cveEstatus']}'";

        $campos = !empty($extras['campos']) ? $extras['campos'] : '
                    vue.*,
	                CONCAT(p.nombre, " ", p.apellidos) as usuario
                ';        

        $sql = "
                SELECT {$campos}
                FROM view_ultimo_estatus vue INNER JOIN usuario u
                ON (vue.idUsuario = u.idusuario) INNER JOIN persona p
                ON (p.idPersona = u.idPersona) 
            ";

        if ($condicion)
            $sql .= " WHERE " . implode(' AND ', $condicion);

        if (!empty($extras['orderBy']))
            $sql .= " ORDER BY " . ( is_array($extras['orderBy']) ? implode(", ", $extras['orderBy']) : $extras['orderBy'] );

        if (!empty($extras['getBy']))
            $sql .= " LIMIT " . (!empty($filtros['offset']) ? $filtros['offset'] : 0) . " , " . (!empty($filtros['fetch']) ? $filtros['fetch'] : 1) . " ";

        return parent::execute_query($sql, $extras);
    }

    
}
