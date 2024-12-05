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

    console.log('idPaciente: '+idReg)

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

    console.log('idPaciente: '+idReg)

    if( idReg != '' ) {

        $('#update_add_responsable_load').load(URL_SITE + 'patient/addResponsible/', 
            {
                idPaciente: idReg
            },
            function() {
                                
                $('#idPaciente').val(idReg)
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
                    if (resp.action == 'addAD' || resp.action == 'editForm' )
                        window.location = URL_SITE + 'patient/add/'+ pantientIdGlobal + '/visit'

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

    $('#tblBitacora').addClass('table').DataTable({
		order: [[1, "ASC"]],
		ajax: {
			url: URL_SITE + 'patient/get_regs_binnacle/' + idPaciente,
			dataSrc: '', 
			method: 'POST',
			data: function (d) {}
		},
		columns: [
			{ data: 'estatus' },
			{ data: 'usuario' },
			{ data: 'fechaHora' },
			{ data: 'observacion' }
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
				targets: [-2,-3, -4],
			}
		],
		serverSide: false,
		searching: false,
		fixedHeader: true,
		scrollY: '1000px',
		scrollX: false,
		responsive: true,
		scrollCollapse: true,
		paging: false,
        bDestroy: true
	});

    var collapsedGroups = {};
    var table = $('#tblVisitas').DataTable({
        ajax: {
            url: URL_SITE + 'patient/get_visits/' + idPaciente,
            dataSrc: '', 
            method: 'POST',
            data: function (d) {}
        },
        rowGroup: {
            dataSrc: 'visita',
            startRender: function ( rows, group ) {
                var collapsed = !!collapsedGroups[group];
                    rows.nodes().each(function (r) {
                    r.style.display = 'none';
                    if (collapsed) {
                        r.style.display = '';
                    }});

                    let avance = rows.data().pluck('avance').reduce((a, b) => a + b.replace(/[^\d]/g, '') * 1, 0) / rows.count();
                    avance = DataTable.render.number(',', '.', 0, '', '%').display(avance)

                    var maxDate = null;
                var usuario = null;

                //seleccionar la fecha mayor del todos los rows de cada grupo y su usuario
                rows.data().each(function (row) {
                    if (row) {
                        var rowDate = new Date(row.fechaHoraPlus); // Suponiendo que fechaHora está en formato ISO 8601
                        if (!isNaN(rowDate)) { // Validar si rowDate es una fecha válida
                            maxDate = maxDate ? Math.max(maxDate, rowDate) : rowDate;
                            usuario = row.usuario;
                        }
                    }
                })
                    
                var date;

                if( maxDate == null )
                    date = ''
                else {
                    date = new Date(maxDate);						
                    date = date.toLocaleString()
                    //Quitar segundos en la hora
                    date = date.split(' ')[0] + ' ' + date.split(' ')[1].split(':')[0] + ':' + date.split(' ')[1].split(':')[1];
                }

                var toggleClass = collapsed ? 'fa-minus-square' : 'fa-plus-square';

                    return $('<tr/>')
                        .append('<td class="text-center">' + '<span class="fa fa-fw ' + toggleClass + ' toggler"/> ' + group + ' (Formularios: ' + rows.count() + ')</td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed)
                    
                        .append('<td class="text-center">Avance Global</td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed)

                        .append('<td class="text-center"><span class="avance">' + avance + '</span></td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed)

                        .append('<td class="text-center">'+ usuario +'</td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed)

                        .append('<td class="text-center">'+  date.toLocaleString() +'</td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed)

                        .append('<td></td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed);
            },
        },
        columns: [
            { data: 'visita' },
            { data: 'formulario' },
            { data: 'avance' },
            { data: 'usuario' },
            { data: 'fechaHora' },
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
                targets: [-2, -3, -4, -5, -6],
            }
        ],
        paging: false,
        bDestroy: true
    }).on( 'draw', function () {
    
        $(".avance").each(function( index ) {
            var avance = $(this).text();

            avance = avance.replace('%','');

            if( avance  == 0 )
                $(this).parent().css( "background-color", "rgba(255, 0, 0, .6)" );
            else if( avance > 0 && avance < 50 )
                $(this).parent().css( "background-color", "rgba(255, 165, 0, .6)" );
            else if( avance > 50 && avance < 90 )
                $(this).parent().css( "background-color", "rgba(60, 178, 232, .6)" );
            else if( avance == 100 )
                $(this).parent().css( "background-color", "rgba(60, 179, 113, .6)" );
        });
    });

    $('#tblVisitas tbody').on('click', 'tr.dtrg-start', function() {
        var name = $(this).data('name');
        collapsedGroups[name] = !collapsedGroups[name];
        table.draw(false);
    });
}

$(function () {

    $("#demographic-tab").on('click', function(){
        $("#tblTelefono").DataTable().ajax.reload();
        $("#tblResponsable").DataTable().ajax.reload();
    });

    $("#btnNewVisit").on('click', function(){

        if( idPaciente != 0 )
            pantientIdGlobal = idPaciente

        if( pantientIdGlobal != 0 )
            window.location = URL_SITE + 'patient/newVisit/' + pantientIdGlobal 
    });   
	
});
