let pantientIdGlobal = 0;


function validar_grupo_pregunta(grupo, comparacion, valor){ 

	resultado = false;
	$( grupo ).each(function(i){
	   if(comparacion == '==' && $(this).val() == valor){
		  resultado++;
		}else if(comparacion == '!=' && $(this).val() != valor){
		  resultado++;
		}
	});
  
	return (resultado > 0) ? true : false;
  }

  function ocultar_campo(clase){ 

    $( '.div_preg_'+clase ).hide('fast', function() { 
        ocultar_subcondicion(this);
        limpiar_campos(clase);
    });
}

function ocultar_subcondicion(campo){
    
    var clase = $(campo).attr('id');
    $('.div_dep_'+clase).hide();
    limpiar_campos(clase);
}

function limpiar_campos(clase){

    $(":checkbox, :radio", $('#'+clase)).each(function(){   
        $(this).attr('checked', false);
    });
    $(":text, :file", $('#'+clase)).each(function(){   
        $(this).val('');
    });

    $("textarea", $('#'+clase)).each(function(){   
        $(this).val('');
    });

    $("select", $('#'+clase)).each(function(){   
        $(this).val('');
    });
    
    return;
}

function calculateIMC () {

    if( $('.Peso').val() !== '' && $('.Talla').val() !== '' ) {
        $.post(
            URL_SITE + "formulario/calculateIMC",
            {
                peso: $('.Peso').val(),
                talla: $('.Talla').val(),
            },
            function(resp) {
    
                if (resp.error == 0) {
                    $('.IMC').val(resp.imc)
                }
            },
            'json'
        ); 
    } else {
        msg(1,'El peso y la talla deben ser llenados.')
    }

}

function sendPhone(idFormulario) {

    idReg = $('#data_form_patient'+idFormulario).data('idReg');

    console.log('aaaa: '+idReg)

    if( idReg != '' ) {

        $('#update_add_phone_load').load(URL_SITE + 'patient/addPhone/', 
            {},
            function() {
                                
                $('#idPaciente').val(idReg)
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
    } else {
        msg(1, 'Guarde los datos del formulario.')
    }

}

function sendResponsible(idFormulario) {

    idReg = $('#data_form_patient'+idFormulario).data('idReg');

    console.log('aaaa: '+idReg)

    if( idReg != '' ) {

        $('#update_add_responsable_load').load(URL_SITE + 'patient/addResponsible/', 
            {
                idPaciente: idReg
            },
            function() {
                                
                //$('#idPaciente').val(idReg)
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
    } else {
        msg(1, 'Guarde los datos del formulario.')
    }

}

function sendResponse(idFormulario) {

    Confirm({
        text: "&iquest;Datos correctos?",
        ok: function(obj) {

            if ( !$('#data_form_patient'+idFormulario).valid() ) {
                msg(1, "Se encontraron errores en el registro, verifique.");
                return;
            }

            if( pantientIdGlobal == 0 )
                pantientIdGlobal = $('#txtIdPaciente').val()
            
            var formData = new FormData();
            var params = $( $('#data_form_patient'+idFormulario) ).serializeArray();

            $.each(params, function (i, val) {
                if( val.value !== '' && val.value !== null )
                    formData.append(val.name, val.value);
            });
    
            $.each($("input[type=file]"), function(i, objFile) {
                $.each(objFile.files,function(j,file){
                    formData.append('archivo_'+$(objFile).attr('name')+'['+i+']', file);
                });
            });
                
            $.ajax({
                url: URL_SITE + 'formulario/response/' + pantientIdGlobal,
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(resp){

                    msg(resp.error, resp.msg);
                    if ( resp.idPaciente ) {
                        initTable(resp.idPaciente)
                        pantientIdGlobal = resp.idPaciente
                        $('#txtIdPaciente').val(resp.idPaciente)
                        $('#data_form_patient'+idFormulario).data('idReg', resp.idPaciente);
                        $("#background-tab, #visits-tab, #binnacle-tab").removeClass('disabled'); 
                    }
                    
                }
            });

        }
    });
}

function initTable(idPaciente) {

    $('#tblTelefono').addClass('table table-striped table-condensed table-bordered table-hover w-100').DataTable({
		ajax: {
			url: URL_SITE + 'patient/get_regs_phone/' + idPaciente,
			dataSrc: '', 
			method: 'POST',
			data: function (d) {}
		},
		columns: [
			{ data: 'telefono' },
			{ data: 'tipo' },
			{ data: 'principal' },
			{ data: 'opciones' }
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
				targets: [-2,-3,-4],
			}
		],
		serverSide: false,
		searching: false,
		fixedHeader: true,
		scrollY: '300px',
		scrollX: false,
		responsive: true,
		scrollCollapse: true,
		paging: false,
        bDestroy: true
	});

    $('#tblResponsable').addClass('table table-striped table-condensed table-bordered table-hover w-100').DataTable({
		ajax: {
			url: URL_SITE + 'patient/get_regs_responsible/' + idPaciente,
			dataSrc: '', 
			method: 'POST',
			data: function (d) {}
		},
		columns: [
			{ data: 'responsable' },
			{ data: 'parentesco' },
			{ data: 'opciones' }
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
				targets: [-2,-3],
			}
		],
		serverSide: false,
		searching: false,
		fixedHeader: true,
		scrollY: '300px',
		scrollX: false,
		responsive: true,
		scrollCollapse: true,
		paging: false,
        bDestroy: true
	});
}

$(function () {

    
    
	
});
