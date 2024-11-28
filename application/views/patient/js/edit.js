
$(function () {

	$('#txtIdPaciente').val(patientId)

	$("#visits-tab").on('click', function(){
        $("#tblVisitas").DataTable().ajax.reload();
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

	var collapsedGroups = {};
    var table = $('#tblVisitas').DataTable({
        ajax: {
			url: URL_SITE + 'patient/get_visits/' + patientId,
			dataSrc: '', 
			method: 'POST',
			data: function (d) {}
		},
//        "order": [[ 2, "desc" ]],
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
					rows.every(function(rowIdx) {
					var rowData = rows.data()[rowIdx];
					var rowDate = new Date(rowData.fechaHoraPlus); // Suponiendo que fechaHora está en formato ISO 8601
					if (!isNaN(rowDate)) { // Validar si rowDate es una fecha válida
						maxDate = maxDate ? Math.max(maxDate, rowDate) : rowDate;
						usuario = rowData.usuario;
					}
					});

					var date = new Date(maxDate);						

					var toggleClass = collapsed ? 'fa-minus-square' : 'fa-plus-square';

					return $('<tr/>')
						.append('<td class="text-center">' + '<span class="fa fa-fw ' + toggleClass + ' toggler"/> ' + group + ' (' + rows.count() + ')</td>')
						.attr('data-name', group)
						.toggleClass('collapsed', collapsed)
					
						.append('<td class="text-center">Avance Global</td>')
						.attr('data-name', group)
						.toggleClass('collapsed', collapsed)

						.append('<td class="text-center">' + avance + '</td>')
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
    });
 
	$('#tblVisitas tbody').on('click', 'tr.dtrg-start', function() {
		var name = $(this).data('name');
		collapsedGroups[name] = !collapsedGroups[name];
		table.draw(false);
	});

})