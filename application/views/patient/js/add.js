function delete_reg_contact(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "patient/deleteContact/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblTelefono").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblTelefono tr.danger").removeClass("danger");
			},
		},
	});
}

function delete_reg_responsible(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "patient/deleteResponsible/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblResponsable").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblResponsable tr.danger").removeClass("danger");
			},
		},
	});
}

function showModalContact(idContacto){

    $('#update_add_phone_load').load(URL_SITE + 'patient/addPhone/', 
        {
            idContacto: idContacto
        },
        function() {
            
            $('#dialog-add-phone').modal('show');   

            $('#formContact').validate({
                rules: {
                    'reg[valor]': 'required',
                    'reg[cveContact]': 'required',
                    'reg[prioridad]': 'required',
                },
                errorPlacement: function(error, element) {
                    if (element.attr("elem-msg-error")) {
                        error.appendTo("#" + element.attr("elem-msg-error"));
                    } else {
                        error.appendTo(element.parent());
                    }
                }
            });

    });   
}

function showModalResponsible(idResponsable) {

    $('#update_add_responsable_load').load(URL_SITE + 'patient/addResponsible/', 
        {
            idResponsable: idResponsable
        },
        function() {
                            
            $('#dialog-add-responsable').modal('show');
            
            $('#formResponsible').validate({
                rules: {
                    'reg[nombre]': 'required',
                    'reg[parentesco]': 'required',
                },
                errorPlacement: function(error, element) {
                    if (element.attr("elem-msg-error")) {
                        error.appendTo("#" + element.attr("elem-msg-error"));
                    } else {
                        error.appendTo(element.parent());
                    }
                }
            });
    });
}

$(function () {

    popup('#dialog-add-phone', {
        title: 'Configuraci&oacute;n del tel&eacute;fono',
        width: "96%",
        minWidth: "20%",
        buttons: {
            Cerrar: function() {
                $('#dialog-add-phone').modal('hide');
            },
            Aceptar: function() {

                if( $("#formContact").valid() ) {

                    $.post(
                        URL_SITE + 'patient/saveContact',
                        $('#formContact').serializeArray(),
                        function(resp) {
                            msg(resp.error, resp.msg);
                            if ( resp.error == 0 ) {
                                $("#tblTelefono").DataTable().ajax.reload(); 
                                $('#dialog-add-phone').modal('hide');
                            }
                        },
                        'json'
                    );
                }                
            }
        }
    })

    popup('#dialog-add-responsable', {
        title: 'Configuraci&oacute;n del responsable',
        width: "96%",
        minWidth: "20%",
        buttons: {
            Cerrar: function() {
                $('#dialog-add-responsable').modal('hide');
            },
            Aceptar: function() {
              
                if( $("#formResponsible").valid() ) {

                    $.post(
                        URL_SITE + 'patient/saveResponsible',
                        $('#formResponsible').serializeArray(),
                        function(resp) {
                            msg(resp.error, resp.msg);
                            if ( resp.error == 0 ) {
                                $('#dialog-add-responsable').modal('hide'); 
                                $("#tblResponsable").DataTable().ajax.reload();   
                            }
                        },
                        'json'
                    );
                } 
            }
        }
    })


})