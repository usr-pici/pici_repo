let idPregunta = 0;
let idPreguntaCondicionGlobal = 0;
let idPreguntaOpcionGlobal = 0;

//Eliminar pregunta
function delete_question_reg(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "formulario/deleteQuestion/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblQuestions").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblQuestions tr.danger").removeClass("danger");
			},
		},
	});
}

//Eliminar opción de pregunta
function delete_option_reg(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "formulario/deleteOptionQuestion/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblOptions").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblOptions tr.danger").removeClass("danger");
			},
		},
	});
}

//Eliminar condición de una pregunta
function delete_condition_question(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "formulario/deleteConditionQuestion/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblOptionsConditions").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblOptionsConditions tr.danger").removeClass("danger");
			},
		},
	});
}


//Guardar pregunta
function saveQuestion(bandera = 0) {

    $.post(
        URL_SITE + "formulario/saveQuestion",
        $("#formModalAddQuestion").serializeArray(),
        function(resp) {
            msg(resp.error, resp.msg);
            
            if (resp.error == 0) {
                
                $('#idPregunta').val(resp.idPregunta);
                idPregunta = resp.idPregunta;

                if( bandera == 1 )
                    saveOptionsQuestion(resp.idPregunta)

                if( bandera == 2 )
                    saveCondition(resp.idPregunta)
                                
                if( bandera == 0 ) {
                    $('#dialog-add').modal('hide');
                    $("#tblQuestions").DataTable().ajax.reload();
                }
            }
        },
        'json'
    );

}

//Guardar opción de pregunta
function saveOptionsQuestion(idPregunta) {

    $.post(
        URL_SITE + "formulario/saveOptionsQuestion",
        {
            idPregunta: idPregunta,
            posicion: $('#posicion').val(),
            opcion: $('#txtOpcion').val()
        },
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {
                $("#txtOpcion").val(null);
                $("#tblQuestions").DataTable().ajax.reload();
		        $("#posicion").load( URL_SITE + "formulario/getPositionOptionQuestion", {idPregunta: idPregunta}, function (resp) {});
                initTable(idPregunta)               
                $('#divTblOpciones').show();   
            }
        },
        'json'
    );  
}

//Guardar condición
function saveCondition(idPregunta) {

    let optionId = $("#idPreguntaOpcion option:selected").val();

    $.post(
        URL_SITE + "formulario/saveQuestionCondition",
        {
            idPreguntaOpcion: optionId,
            idPregunta: idPregunta
        },
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {
                $("#idPreguntaCondicion, #idPreguntaOpcion").val(null);
                $("#tblQuestions").DataTable().ajax.reload();
                initTableCondition(idPregunta)                   
            }
        },
        'json'
    );
}

function tipoCampoOnChange(sel) {

    if( sel.value == 'TEXT' || sel.value == 'TEXT_AREA') {
        $('#divTblOpciones, #divOpciones, #divFormato').hide();
        $('#divLong').show();
        $("#formato, #posicion, #txtOpcion").removeAttr("name");
    } else if( sel.value == 'LIST' || sel.value == 'LIST_MULTIPLE' || sel.value == 'RADIO' || sel.value == 'CHECKBOX'  ) {
        $("#tblOptions").DataTable().draw();
        
        if( idPregunta == 0 )
            initTable('');

        $('#divLong, #divFormato').hide();
        $('#divOpciones, #divTblOpciones').show();
        $("#formato").removeAttr("name");
        $("#posicion").attr("name", "reg[posicion]");
        $("#txtOpcion").attr("name", "reg[opcion]");
    } else if( sel.value == 'LABEL' || sel.value == 'DATE' || sel.value == '' ) {
        $('#divLong, #divTblOpciones, #divOpciones').hide();
        $('#divFormato').show();
        $("#txtValor, #txtOpcion").removeAttr("name");
        $("#formato").attr("name", "reg[formato]");
    }

}

function configQuestion(idPregunta) {

    $('#update_add_load').load(URL_SITE + 'formulario/editModalQuestion/', 
        {
            idFormulario: idFormulario,
            idPregunta: idPregunta
        },
        function() {
            
            $('#dialog-add').modal('show');
            $('#idRol').selectpicker();

		    $("#posicion").load( URL_SITE + "formulario/getPositionOptionQuestion", {idPregunta: idPregunta}, function (resp) {});
            initTableCondition(idPregunta)

            if( typeField == 'TEXT' || typeField == 'TEXT_AREA') {
                $('#divTblOpciones, #divOpciones, #divFormato').hide();
                $('#divLong').show();
                $("#formato, #posicion, #txtOpcion").removeAttr("name");
            } else if( typeField == 'LIST' || typeField == 'LIST_MULTIPLE' || typeField == 'RADIO' || typeField == 'CHECKBOX'  ) {
                //$("#tblOptions").DataTable().draw();
                $('#divLong, #divFormato').hide();                
                $('#divOpciones, #divTblOpciones').show();
                $("#formato").removeAttr("name");
                $("#posicion").attr("name", "reg[posicion]");
                $("#txtOpcion").attr("name", "reg[opcion]");
            } else if( typeField == 'LABEL' || typeField == 'DATE' || typeField == '' ) {
                $('#divLong, #divTblOpciones, #divOpciones').hide();
                $('#divFormato').show();
                $("#txtValor, #txtOpcion").removeAttr("name");
                $("#formato").attr("name", "reg[formato]");
            }
            
            $('#radNo').prop('checked', true);
            $('#formModalAddQuestion').validate({
                rules: {
                    'reg[etiqueta]': 'required',
                    'reg[idTipoCampo]': 'required',
                },
                errorPlacement: function(error, element) {
                    if (element.attr("elem-msg-error")) {
                        error.appendTo("#" + element.attr("elem-msg-error"));
                    } else {
                        error.appendTo(element.parent());
                    }
                }
            });

            $("#tblOptions").DataTable({
                ajax: {
                    url: URL_SITE + "formulario/getOptionsRegs/" + idPregunta,
                    dataSrc: "",
                    method: "POST",
                    data: function (d) {},
                },
                columns: [
                    {data: "posicion"},	
                    {data: "opcion"},
                    {data: "opciones"}
                ],
                columnDefs: [
                    {
                        orderable: false,
                        className: "text-center",
                        width: "25%",
                        targets: [-1],
                    },
                    {
                        orderable: false,
                        className: "text-center",
                        width: "65%",
                        targets: [-2],
                    },
                    {
                        orderable: false,
                        className: "text-center",
                        width: "10%",
                        targets: [-3],
                    },
                ],
                serverSide: false,
                searching: false,
                fixedHeader: false,
                scrollY: "250px",
                scrollX: false,
                scrollCollapse: true,
                responsive: true,
                paging: false,
                bDestroy: true
            });

            $('#btnSaveOptionsQuestion').click(function () {
        
                if ($("#formModalAddQuestion").valid()) {
        
                    if( $('#txtOpcion').val() != '' ) {
                        saveQuestion(1)
                    } else {
                        msg(1, 'Agregue una opci&oacute;n.')
                    }
                }
            })
        
            $('#btnSaveConditionQuestion').click(function () {
                
                if ($("#formModalAddQuestion").valid()) {
                    
                    let posicion = $("#posicionCondicion option:selected").val();
                    let questionId = $("#idPreguntaCondicion option:selected").val();
                    let optionId = $("#idPreguntaOpcion option:selected").val();
        
                    if( questionId != '' && optionId != '' && posicion != '' ) {
                        saveQuestion(2)                 
                    } else {
                        msg(1, 'Seleccione la pregunta y la opci&oacute;n.')
                    }            
                }
            })

            $('#btnUpdateOptionsQuestion').click(function () {
                
                let positionId = $("#posicion option:selected").val();
                let option = $("#txtOpcion").val();
                    
                $.post(
                    URL_SITE + "formulario/saveOptionsQuestion",
                    {
                        posicion: positionId,
                        opcion: option,
                        idPreguntaOpcion: idPreguntaOpcionGlobal
                    },
                    function(resp) {
                        msg(resp.error, resp.msg);
                        if (resp.error == 0) {
                            $("#txtOpcion").val(null);
		                    $("#posicion").load( URL_SITE + "formulario/getPositionOptionQuestion", {idPregunta: idPregunta}, function (resp) {});
                            $('#btnUpdateOptionsQuestion').hide()
                            $('#btnSaveOptionsQuestion').show()
                            $("#tblOptions").DataTable().ajax.reload();          
                        }
                    },
                    'json'
                );                   
            })

            $('#btnUpdateConditionQuestion').click(function () {
                
                let questionId = $("#idPreguntaCondicion option:selected").val();
                let optionId = $("#idPreguntaOpcion option:selected").val();
                    
                $.post(
                    URL_SITE + "formulario/saveQuestionCondition",
                    {
                        idPreguntaOpcion: optionId,
                        idPreguntaCondicion: idPreguntaCondicionGlobal
                    },
                    function(resp) {
                        msg(resp.error, resp.msg);
                        if (resp.error == 0) {
                            $("#idPreguntaCondicion, #idPreguntaOpcion").val(null);
                            $('#btnUpdateConditionQuestion').hide()
                            $('#btnSaveConditionQuestion').show()
                            $("#tblOptionsConditions").DataTable().ajax.reload();          
                        }
                    },
                    'json'
                );                  
                
            })
    });
}

function initTable(idPregunta) {

    if( idPregunta != '' || idPregunta != null ) {

        $("#tblOptions").DataTable({
            ajax: {
                url: URL_SITE + "formulario/getOptionsRegs/" + idPregunta,
                dataSrc: "",
                method: "POST",
                data: function (d) {},
            },
            columns: [
                {data: "posicion"},	
                {data: "opcion"},
                {data: "opciones"}
            ],
            columnDefs: [
                {
                    orderable: false,
                    className: "text-center",
                    width: "25%",
                    targets: [-1],
                },
                {
                    orderable: false,
                    className: "text-center",
                    width: "65%",
                    targets: [-2],
                },
                {
                    orderable: false,
                    className: "text-center",
                    width: "10%",
                    targets: [-3],
                },
            ],
            serverSide: false,
            searching: false,
            fixedHeader: false,
            scrollY: "250px",
            scrollX: false,
            scrollCollapse: true,
            responsive: true,
            paging: false,
            bDestroy: true
        });
    }
}

function initTableCondition(idPregunta) {

    if( idPregunta != '' || idPregunta != null ) {

        $("#tblOptionsConditions").DataTable({
            ajax: {
                url: URL_SITE + "formulario/getConditionsRegs/" + idPregunta,
                dataSrc: "",
                method: "POST",
                data: function (d) {},
            },
            columns: [
                {data: "idPreguntaCondicion"},	
                {data: "pregunta"},
                {data: "opcion"},
                {data: "opciones"}
            ],
            columnDefs: [
                {
                    orderable: false,
                    className: "text-center",
                    width: "20%",
                    targets: [-1],
                },
                {
                    orderable: false,
                    className: "text-center",
                    width: "35%",
                    targets: [-2],
                },
                {
                    orderable: false,
                    className: "text-center",
                    width: "35%",
                    targets: [-3],
                },
                {
                    orderable: false,
                    className: "text-center",
                    width: "10%",
                    targets: [-4],
                },
            ],
            serverSide: false,
            searching: false,
            fixedHeader: false,
            scrollY: "250px",
            scrollX: false,
            scrollCollapse: true,
            responsive: true,
            paging: false,
            bDestroy: true
        });
    }
}

function showOptionsQuestion() {

    let questionId = $("#idPreguntaCondicion option:selected").val();

	if( questionId != '' ) {
		$("#idPreguntaOpcion").load( URL_SITE + "formulario/cat_optionsQuestion", {idPregunta: questionId}, function (resp) {});
	}
}

function configLevelQuestion(idPregunta, posicion, context, idFormulario) {

    $.post(
        URL_SITE + "formulario/configLevelQuestion",
        {
            idPregunta: idPregunta,
            posicion: posicion,
            context: context,
            idFormulario: idFormulario
        },
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {
                $("#tblQuestions").DataTable().ajax.reload();                
            }
        },
        'json'
    );
} 

function configLevelOption(idPreguntaOption, posicion, context, idPregunta) {

    $.post(
        URL_SITE + "formulario/configLevelOption",
        {
            idPreguntaOpcion: idPreguntaOption,
            posicion: posicion,
            context: context,
            idPregunta: idPregunta
        },
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {
                $("#tblOptions").DataTable().ajax.reload();                
            }
        },
        'json'
    );
} 

function disableOption(idPreguntaOpcion) {
    let pos = '.pos' + idPreguntaOpcion;
    let opc = '.opc' + idPreguntaOpcion;
    $(pos).removeAttr('disabled');
    $(opc).removeAttr('disabled');
}

function updOptionQuestion(idPreguntaOpcion, context) {

    if( context == 'OPCION') {

        $.post(
            URL_SITE + "formulario/updateOptionQuestion",
            {
                idPreguntaOpcion: idPreguntaOpcion,
                opcion: $('.opc' + idPreguntaOpcion).val()
            },
            function(resp) {
                msg(resp.error, resp.msg);
                if (resp.error == 0) {
                    $("#tblOptions").DataTable().ajax.reload();                
                }
            },
            'json'
        );

    } else {

        $.post(
            URL_SITE + "formulario/updateOptionQuestion",
            {
                idPreguntaOpcion: idPreguntaOpcion,
                posicion: $('.pos' + idPreguntaOpcion).val()
            },
            function(resp) {
                msg(resp.error, resp.msg);
                if (resp.error == 0) {
                    $("#tblOptions").DataTable().ajax.reload();                
                }
            },
            'json'
        );
    }
}

function edit_opcion_question(idPreguntaOpcion, posicion, opcion, idPregunta) {
    idPreguntaOpcionGlobal = idPreguntaOpcion;
    idPregunta = idPregunta;
    $('#btnSaveOptionsQuestion').hide()
    $('#btnUpdateOptionsQuestion').show()
	$("#posicion").load( URL_SITE + "formulario/getPositionOptionQuestion", {idPregunta: idPregunta, posicion: posicion}, function (resp) {});
	$("#txtOpcion").val(opcion);
}

function edit_condition_question(idPreguntaCat, idPreguntaOpcionCat, idPreguntaPadre, idFormulario, idPreguntaCondicion) {
    idPreguntaCondicionGlobal = idPreguntaCondicion;
    $('#btnSaveConditionQuestion').hide()
    $('#btnUpdateConditionQuestion').show()
	$("#idPreguntaCondicion").load( URL_SITE + "formulario/cat_questions", {idPreguntaPadre: idPreguntaPadre, idFormulario: idFormulario, idPregunta: idPreguntaCat}, function (resp) {});
	$("#idPreguntaOpcion").load( URL_SITE + "formulario/cat_optionsQuestion", {idPregunta: idPreguntaCat, idPreguntaOpcion: idPreguntaOpcionCat}, function (resp) {});
}

$(function () {

    $("#tblQuestions").DataTable({
        ajax: {
            url: URL_SITE + "formulario/getRegsQuestions/" + idFormulario,
            dataSrc: "",
            method: "POST",
            data: function (d) {},
        },
        columns: [
            {data: "consecutivo"},	
            {data: "etiqueta"},
            {data: "opciones"}
        ],
        columnDefs: [
            {
                orderable: false,
                className: "text-center",
                width: "25%",
                targets: [-1],
            },
            {
                orderable: true,
                className: "text-center",
                width: "8%",
                targets: [-3],
            },
        ],
		serverSide: false,
		searching: false,
		fixedHeader: false,
		scrollY: "350px",
		scrollX: false,
		scrollCollapse: true,
        responsive: true,
		paging: false,
    });

    $('#btnUpdate').click(function () {

        if ($('#form-register').valid()) {

		    $('#btnUpdate').prop('disabled', true);
            $('#btnRegresar').addClass('disabled');

            $.post(
                URL_SITE + "formulario/update",
                $("#form-register").serializeArray(),
                function(resp) {
                    msg(resp.error, resp.msg);
                    if (resp.error == 0) {
                        $('#btnUpdate').prop('disabled', false);
                        $('#btnRegresar').removeClass('disabled');
                    }
                },
                'json'
            );
        
        } else {
            msg(1,'Verifique que la información registrada esté completa y correcta.');
        }

    })

    $("#form-register").validate({
        rules: {
            'reg[nombre]': {
                required:true,
            },
            'reg[clave]': {
                required:true,
            },
            'reg[descripcion]': {
                required:true,
            },
            'reg[activo]': {
                required:true,
            },
        },
        messages: {},
        errorPlacement: function(error, element) {
            if (element.attr("elem-msg-error")) {
                error.appendTo("#" + element.attr("elem-msg-error"));
            } else {
                error.appendTo(element.parent());
            }
        }
    });

    $('#btnAddQuestion').click(function () {

        $('#update_add_load').load(URL_SITE + 'formulario/addModalQuestion/', 
            {
                idFormulario: idFormulario
            },
            function() {
                
                $('#dialog-add').modal('show');

                $("#idRol").load( URL_SITE + "formulario/cat_rol", {}, function (resp) {
                    $('#idRol').prop('multiple', true);
                    $('#idRol').selectpicker('destroy');
                    $('#idRol').selectpicker();
                });
        
                $('#radNoM').prop('checked', true);
                $('#formModalAddQuestion').validate({
                    rules: {
                        'reg[etiqueta]': 'required',
                        'reg[idTipoCampo]': 'required',
                    },
                    errorPlacement: function(error, element) {
                        if (element.attr("elem-msg-error")) {
                            error.appendTo("#" + element.attr("elem-msg-error"));
                        } else {
                            error.appendTo(element.parent());
                        }
                    }
                });

                $('#btnSaveOptionsQuestion').click(function () {
        
                    if ($("#formModalAddQuestion").valid()) {
            
                        if( $('#txtOpcion').val() != '' ) {
                            saveQuestion(1)
                        } else {
                            msg(1, 'Agregue una opci&oacute;n.')
                        }
                    }
                })
            
                $('#btnSaveConditionQuestion').click(function () {
                    
                    if ($("#formModalAddQuestion").valid()) {

                        let posicion = $("#posicionCondicion option:selected").val();
                        let questionId = $("#idPreguntaCondicion option:selected").val();
                        let optionId = $("#idPreguntaOpcion option:selected").val();
            
                        if( questionId != '' && optionId != '' && posicion != '' ) {
                            saveQuestion(2)                 
                        } else {
                            msg(1, 'Seleccione la pregunta y la opci&oacute;n.')
                        }            
                    }
                })

                $('#btnUpdateOptionsQuestion').click(function () {
                
                    let positionId = $("#posicion option:selected").val();
                    let option = $("#txtOpcion").val();
                        
                    $.post(
                        URL_SITE + "formulario/saveOptionsQuestion",
                        {
                            posicion: positionId,
                            opcion: option,
                            idPreguntaOpcion: idPreguntaOpcionGlobal
                        },
                        function(resp) {
                            msg(resp.error, resp.msg);
                            if (resp.error == 0) {
                                $("#txtOpcion").val(null);
                                $("#posicion").load( URL_SITE + "formulario/getPositionOptionQuestion", {idPregunta: idPregunta}, function (resp) {});
                                $('#btnUpdateOptionsQuestion').hide()
                                $('#btnSaveOptionsQuestion').show()
                                $("#tblOptions").DataTable().ajax.reload();          
                            }
                        },
                        'json'
                    );                   
                })

                $('#btnUpdateConditionQuestion').click(function () {
                
                    let questionId = $("#idPreguntaCondicion option:selected").val();
                    let optionId = $("#idPreguntaOpcion option:selected").val();
                        
                    $.post(
                        URL_SITE + "formulario/saveQuestionCondition",
                        {
                            idPreguntaOpcion: optionId,
                            idPreguntaCondicion: idPreguntaCondicionGlobal
                        },
                        function(resp) {
                            msg(resp.error, resp.msg);
                            if (resp.error == 0) {
                                $("#idPreguntaCondicion, #idPreguntaOpcion").val(null);
                                $('#btnUpdateConditionQuestion').hide()
                                $('#btnSaveConditionQuestion').show()
                                $("#tblOptionsConditions").DataTable().ajax.reload();          
                            }
                        },
                        'json'
                    );                  
                    
                })
                
        });

    })

    popup('#dialog-add', {
        title: 'Configuraci&oacute;n de la pregunta',
        width: "96%",
        minWidth: "40%",
        buttons: {
            Cerrar: function() {
                $('#dialog-add').modal('hide');
            },
            Aceptar: function() {
                
                if ($("#formModalAddQuestion").valid()) {
					saveQuestion()
				}
            }
        },
        opened: function() {
			$("#tblOptions").DataTable().draw();
		}
    })

})