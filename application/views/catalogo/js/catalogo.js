var $config;

var onUpdateStatus = function(resp) {
	//    console.log(resp);
	$tabla_regs.ajax.reload();
};

function clone_reg(elem, id) {
	var $tr = $(elem).parents('tr:first');

	$tr.addClass('danger');

	Confirm({
		text: '&iquest; Realmente desea clonar el Indicador seleccionado ?',
		cancel: function() {
			$tr.removeClass('danger');
		},
		ok: function() {
			$.post(
				URL_SITE + 'catalogo/indicador/cloneReg/',
				{ contexto: 'indicador', id: id },
				function(resp) {
					msg(resp.error, resp.msg);

					if (resp.error === 1) {
						$tr.removeClass('danger');
					} else {
						$('#tabla_regs').DataTable().ajax.reload();
					}
				},
				'json'
			);
		}
	});
}

function config_datatable() {
	var $columns = [];

	for ( var x in $config['columns'] ) {
            
            
            if ( ( 'notShowInTable' in $config['columns'][x] ) && $config['columns'][x]['notShowInTable'] == 1 ) {
                
		continue;
            }
//            console.log($config['columns'][x]);
            $columns.push($config['columns'][x]);
	}
//	   console.log($columns);

	//    console.log($config);
	$('#tabla_regs').addClass('table table-striped table-condensed table-bordered table-hover').DataTable({
		/*"language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
			
        },*/
		ajax: {
			url: URL_SITE + 'catalogo/' + $config['catalogo'] + '/get_regs',
			dataSrc: '', //
			method: 'POST' //,
			//            data: function (d) {
			//
			//                d.filtros = $('#form_search').serialize();
			//            }
		},
		rowId: $config['key_field'],
		//        columns: $config['columns'],
		columns: $columns,
		columnDefs: [ { orderable: false, className: 'text-center', width: '40px', targets: [ -1 ] } ],
		dom:
			'<"row"<"col-xs-6 col-sm-6 col-md-6 text-left"l><"col-xs-6 col-sm-6 col-md-6 text-right"f>>t<"row"<"col-xs-6 col-sm-6 col-md-6 text-left"i><"col-xs-6 col-sm-6 col-md-6 text-right"p>>',
		serverSide: false,
		searching: true,
		fixedHeader: true,
		//scrollY: '300px',
		//        scrollX: false,  //"100%"
		//        scrollCollapse: false,
		paging: true
	});

	$('#tabla_regs tbody').on('click', '.deleteAction', function() {
		$(this).parents('tr').addClass('bg-danger');
	});

	for (var x in $config['columns']) {
		$('#c_r_' + $config['columns'][x]['data']).data('campo', $config['columns'][x]['data']);
	}
}

function delete_reg(id_reg) {
	$('#text_id_reg').text(id_reg);

	$config['catalogo'] === 'tipoTarjeta' ? $('#note_delete').show() : $('#note_delete').hide();

	$('#dialog-delete').modal('show');
}

function reg(id_reg) {
	var $title = id_reg === 0 ? 'Nuevo registro' : 'Edici&oacute;n de registro';

	if (id_reg === 0) {
		$('#div_identificador').hide();
	} else {
		$('#identificador').text(id_reg);
		$('#div_identificador').show();
	}

	$('#form-box-msg').hide();

	popup('#dialog-registro', {
		title: $title,
		buttons: {
			Guardar: function() {
				$('#form-box-msg').hide();

				$.post(
					URL_SITE + 'catalogo/' + $config['catalogo'] + '/save/' + (id_reg === 0 ? '' : id_reg),
					$('#form_reg').serializeArray(),
					function(resp) {
						//                        console.log(resp);
						if (resp.error == 0) {
							toastr.success('Datos guardados.', $title);
							$('#dialog-registro').modal('hide');
							$('#tabla_regs').DataTable().ajax.reload();
						} else {
							$('#form-box-msg').html(resp.msg).show();
						}
					},
					'json'
				);
			},
			Cerrar: function() {
				$('#dialog-registro').modal('hide');
			}
		}
	});

	$('.field_options').prop('checked', false);

	if (id_reg === 0) {
		$("[id^='c_r_']").data('value', '').val('');
		$('[data-dependencia] :first').trigger('change');

		$('#dialog-registro').modal('show');
		$('.field_options.selected_default').prop('checked', true);
	} else {
		$.post(
			URL_SITE + 'catalogo/' + $config['catalogo'] + '/get_reg',
			{ id: id_reg },
			function(resp) {
				//                console.log(resp);
				if (resp.error == 0) {
					for (var x in resp.reg) {
						if ($('#c_r_' + x).length > 0) {
							$('#c_r_' + x).data('value', resp.reg[x]).val(resp.reg[x]);
						} else if ($("[name='reg\\[" + x + "\\]'][value='" + resp.reg[x] + "']").length > 0) {
							$("[name='reg\\[" + x + "\\]'][value='" + resp.reg[x] + "']").prop('checked', true);
						}
					}

					$('[data-dependencia] :first').trigger('change');

					$('#dialog-registro').modal('show');
				} else {
					Alert({ text: resp.msg });
				}
			},
			'json'
		);
	}
}

$(function() {
	popup('#dialog-registro');

	toastr.options = {
		closeButton: true,
		progressBar: true,
		showMethod: 'slideDown',
		timeOut: 4000
	};

	$('[data-dependencia]').change(function() {
		var $elem = $(this),
			campo_dependencia = $elem.attr('data-dependencia'),
			$dependencia = $('#c_r_' + campo_dependencia);
		var params = { dependencia: campo_dependencia, filtros: {} };

		params.filtros[$elem.data('campo')] = $elem.val() === '' ? -1 : $elem.val();

		$dependencia.load(URL_SITE + 'catalogo/' + $config['catalogo'] + '/get_cat_dependiente', params, function() {
			$dependencia.val($dependencia.data('value')).trigger('change');
		});
	});

	$('#btn_nuevo_reg').click(function() {
		reg(0);
	});

	popup('#dialog-delete', {
		title: 'Eliminar registro',
		buttons: {
			No: function() {
				$('#dialog-delete').modal('hide');
				$('#tabla_regs tr.bg-danger').removeClass('bg-danger');
			},
			Si: function() {
				$('#dialog-delete').modal('hide');
				$.post(
					URL_SITE + 'catalogo/' + $config['catalogo'] + '/delete',
					{ id: $('#text_id_reg').text() },
					function(resp) {
						//                        console.log(resp);
						if (resp.error == 0) {
							toastr.success('Registro eliminado.', 'Eliminaci&oacute;n de registro');
							$('#tabla_regs').DataTable().ajax.reload();
						}
					},
					'json'
				);
			}
		}
	});
});
