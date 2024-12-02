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

	if( tab_initial == 'visit' )
		$("#visits-tab").trigger('click');

	$('#txtIdPaciente').val(patientId)

	$("#visits-tab").on('click', function(){
        $("#tblVisitas").DataTable().ajax.reload();
    });

	$("#binnacle-tab").on('click', function(){
        $("#tblBitacora").DataTable().ajax.reload();
    });

    $('#tblTelefono').addClass('table table-striped table-condensed table-bordered table-hover w-100').DataTable({
		ajax: {
			url: URL_SITE + 'patient/get_regs_phone/' + patientId,
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
			url: URL_SITE + 'patient/get_regs_responsible/' + patientId,
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
			url: URL_SITE + 'patient/get_regs_binnacle/' + patientId,
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
        //bDestroy: true
	});

	var collapsedGroups = {};
    var table = $('#tblVisitas').addClass('table').DataTable({
        ajax: {
			url: URL_SITE + 'patient/get_visits/' + patientId,
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
						.append('<td class="text-center">' + '<span class="fa fa-fw ' + toggleClass + ' toggler"/> ' + group + ' (' + rows.count() + ')</td>')
						.attr('data-name', group)
						.toggleClass('collapsed', collapsed)
					
						.append('<td class="text-center">Avance Global</td>')
						.attr('data-name', group)
						.toggleClass('collapsed', collapsed)

						.append('<td class="text-center"><span class="avance">' + avance + '</span></td>')
						.attr('data-name', group)
						.toggleClass('collapsed', collapsed)

						.append('<td class="text-center">'+ (usuario == null ? '' : usuario) +'</td>')
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
	});;
 
	$('#tblVisitas tbody').on('click', 'tr.dtrg-start', function() {
		var name = $(this).data('name');
		collapsedGroups[name] = !collapsedGroups[name];
		table.draw(false);
	});

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