function delete_reg(elem, idRegistro) {

	var $tr = $(elem).parents("tr:first");

	$tr.addClass("danger");

	Confirm({
		text: "&iquest;Desea borrar el registro?",
		cancel: function () {
			$tr.removeClass("danger");
		},
		ok: function () {
			$.post(
				URL_SITE + "patient/delete/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblPatients").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblPatients tr.danger").removeClass("danger");
			},
		},
	});
}

function cleanFilter(){
    $('#txtNombre, #idGenero, #txtFechaIni, #txtFechaFin').val('');
    $("#tblPatients").DataTable().ajax.reload();
}

$(function () {

	$("#btn_buscar").on('click', function(){
        $("#tblPatients").DataTable().ajax.reload();
    });

	$("#btn_exportar").on('click', function(){
       
		$.ajax({
			url: URL_SITE + "patient/exportar_excel",
			destroy: true,
			type: 'POST',
			data: {},
			processData: false,
			contentType: false,
			beforeSend: function() {
				$('#btn_exportar').prop('disabled', true);
			},
			success: function(response){
				window.open(URL_SITE + "patient/exportar_excel");
				$('#btn_exportar').prop('disabled', false);
			},
			error: function(){
				$('#btn_exportar').prop('disabled', false);
				msg(1, "Error al exportar");
			}
		});	

    });

	$('#tblPatients').addClass('table table-striped table-condensed table-bordered table-hover w-100').DataTable({
		ajax: {
			url: URL_SITE + 'patient/get_regs',
			dataSrc: '', 
			method: 'POST',
			data: function (d) {
				d.filtros = $("#form_search").serialize();
			}
		},
		columns: [
			{ data: 'numPaciente' },
			{ data: 'nombre' },
			{ data: 'genero' },
			{ data: 'fechaNacimiento' },
			{ data: 'tipoDocumento' },
			{ data: 'numDocumento' },
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
				targets: [-2, -3, -4, -5, -6, -7],
			}
		],
		serverSide: false,
		searching: false,
		fixedHeader: true,
		scrollY: '300px',
		scrollX: false,
		responsive: true,
		scrollCollapse: true,
		paging: false
	});
	
});
