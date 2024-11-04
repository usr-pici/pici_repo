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
				URL_SITE + "formulario/delete/" + idRegistro,
				{},
				function (resp) {
					msg(resp.error, resp.msg);

					if (resp.error == 0) {
						$("#dialog-usr").modal("hide");
						$("#tblFormularios").DataTable().ajax.reload();
					}
				},
				"json"
			);
		},
		config: {
			close: function () {
				$("#dialog-delete").modal("hide");
				$("#tblFormularios tr.danger").removeClass("danger");
			},
		},
	});
}

function cleanFilter(){
    $('#txtNombre, #txtClave, #txtFechaIni, #txtFechaFin').val('');
    $('#idEstatus').val('');
    $("#tblFormularios").DataTable().ajax.reload();
}

$(function () {

	$("#btn_buscar").on('click', function(){
        $("#tblFormularios").DataTable().ajax.reload();
    });

	$('#tblFormularios').addClass('table table-striped table-condensed table-bordered table-hover w-100').DataTable({
		ajax: {
			url: URL_SITE + 'formulario/get_regs',
			dataSrc: '', 
			method: 'POST',
			data: function (d) {
				d.filtros = $("#form_search").serialize();
			}
		},
		columns: [
			{ data: 'idFormulario' },
			{ data: 'clave' },
			{ data: 'nombre' },
			{ data: 'estatus' },
			{ data: 'vigenciaIni' },
			{ data: 'vigenciaFin' },
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
		//responsive: true,
		scrollCollapse: true,
		paging: false
	});
	
});
