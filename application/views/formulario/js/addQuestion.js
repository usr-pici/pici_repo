let idPregunta = 0;
let idFormulario = 0;

function saveQuestion(bandera = 0) {

    console.log('*****************')
    console.log(bandera)

    $.post(
        URL_SITE + "formulario/saveQuestion",
        $("#formModalAddQuestion").serializeArray(),
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {
                
                idPregunta = resp.idPregunta;
                
                $('#idPregunta').val(idPregunta);
                
                if( bandera == 0 ) {
                    $('#dialog-add').modal('hide');
                    $("#tblQuestions").DataTable().ajax.reload();
                    $('#divTblPreguntas').show();
                    //Load questions 
                    //$('#question-container').load(URL_SITE + 'formulario/getQuestionsHTML/', {idFormulario:idFormulario, bandera:1}, function() {});
                }
            }
        },
        'json'
    );

}

function saveOptionsQuestion() {

    $.post(
        URL_SITE + "formulario/saveOptionsQuestion",
        {
            idPregunta: $('#idPregunta').val(),
            posicion: $('#posicion').val(),
            opcion: $('#txtOpcion').val()

        },
        function(resp) {
            msg(resp.error, resp.msg);
            if (resp.error == 0) {

                $("#txtOpcion").val(null);
                $("#tblQuestions").DataTable().ajax.reload();
                $("#tblOptions").DataTable().ajax.reload();
                $('#divTblOpciones').show();   
                //$('#question-container').load(URL_SITE + 'formulario/getQuestionsHTML/', {idFormulario:idFormulario, bandera:1}, function() {});
                
            }
        },
        'json'
    );  

}

function tipoCampoOnChange(sel) {

    if( sel.value == 'TEXT' || sel.value == 'TEXT_AREA') {
        $('#divLong').show();
        $('#divTblOpciones').hide();
        $('#divOpciones').hide();
        $('#divCondicion').hide();
        $('#divFormato').hide();
        $("#formato").removeAttr("name");
        $("#posicion").removeAttr("name");
        $("#txtOpcion").removeAttr("name");
    } else if( sel.value == 'LIST' || sel.value == 'LIST_MULTIPLE' || sel.value == 'RADIO' || sel.value == 'CHECKBOX'  ) {
		$("#tblOptions").DataTable().draw();
        $('#divLong').hide();
        $('#divFormato').hide();
        $('#divOpciones').show();
        $('#divCondicion').show();
        $("#formato").removeAttr("name");
        $("#posicion").attr("name", "reg[posicion]");
        $("#txtOpcion").attr("name", "reg[opcion]");
    } else if( sel.value == 'LABEL' || sel.value == 'DATE' || sel.value == '' ) {
        $('#divLong').hide();
        $('#divTblOpciones').hide();
        $('#divOpciones').hide();
        $('#divCondicion').hide();
        $('#divFormato').show();
        $("#txtValor").removeAttr("name");
        $("#txtOpcion").removeAttr("name");
        $("#formato").attr("name", "reg[formato]");
    }
}

function saveOptionQuestion() {

    if ($("#formModalAddQuestion").valid()) {

        if( $('#txtOpcion').val() != '' ) {
            saveQuestion(1)
            saveOptionsQuestion()
        } else {
            msg(1, 'Complete el formulario.')
        }
    }
    
}

$(function () {

    $("#tblQuestions").DataTable({
        ajax: {
            url: URL_SITE + "formulario/getRegsQuestions/" + idFormulario,
            dataSrc: "",
            method: "POST",
            data: function (d) {
                d.filtros = $("#form_search").serialize();
            },
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
        ],
        //dom: '<"row"<"col-xs-6 col-sm-6 col-md-6 text-left"l><"col-xs-6 col-sm-6 col-md-6 text-right mb-2"f>>t<"row"<"col-xs-6 col-sm-6 col-md-6 text-left"i><"col-xs-6 col-sm-6 col-md-6 text-right"p>>',
		serverSide: false,
		searching: false,
		fixedHeader: false,
		scrollY: "350px",
		scrollX: false,
		scrollCollapse: true,
        responsive: true,
		paging: false,
    });

    $('#btnSave').click(function () {

        if ($('#form-register').valid()) {

		    $('#btnSave').prop('disabled', true);
            $('#btnRegresar').addClass('disabled');

            $.post(
                URL_SITE + "formulario/save",
                $("#form-register").serializeArray(),
                function(resp) {
                    msg(resp.error, resp.msg);
                    if (resp.error == 0) {
                        $('#btnSave').prop('disabled', false);
                        $('#btnRegresar').removeClass('disabled');
                        idFormulario = resp.id;
                        $('#txtIdFormulario').val(resp.id);
                        $('#divBtnAddQuestion').show();
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
        messages: {
            
        },
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
        

                
                $('#radNo').prop('checked', true);
                $('#formModalAddQuestion').validate({
                    rules: {
                        'reg[etiqueta]': 'required',
                        'reg[idTipoCampo]': 'required',
                        'reg[opcion]': 'required',
                        'reg[posicion]': 'required',
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
                        url: URL_SITE + "formulario/getOptionsRegs",
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