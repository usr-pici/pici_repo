function actualizarPerfil(){
	
	if( $('#changePwd').is(':checked') ) {
		$("#inputPasswordActual").attr("name","usuario[passwordActual]");
		$("#inputPassword").attr("name","usuario[password]");
		$("#inputPassword2").attr("name","inputPassword2");
	} else {
		$("#inputPasswordActual").removeAttr("name");
		$("#inputPassword").removeAttr("name");
		$("#inputPassword2").removeAttr("name");
	}

	if ( $("#formUpdatePerfil").valid() ) {
		var formData = new FormData();
		var params = $($("#formUpdatePerfil")).serializeArray();

		$.each(params, function (i, val) {
			formData.append(val.name, val.value);
		});

		$.each($("input[type=file]"), function (i, objFile) {
			$.each(objFile.files, function (j, file) {
				formData.append($(objFile).attr("name"), file);
			});
		});

		$('#btnActualizar').prop('disabled', true);
		$('#btnRegresarInicio').prop('disabled', true);

		$.ajax({
			url: URL_SITE + "user/update",
			data: formData,
			dataType: "json",
			cache: false,
			contentType: false,
			processData: false,
			type: "POST",
			xhrFields: {
				withCredentials: true,
			},
			success: function (data) {
				msg(data.error, data.msg);
				if (data.error == 0) {
					window.location = URL_SITE;
				} else {
					$("#msg_result").html(data.msg).show();
					$('#btnActualizar').prop('disabled', false);
				}
			},
		});
				
	}
}

$(function() {

	$('#imgInput').hide();

	$('#changeImg').on('click', () => {
		$('#imgInput').toggle();
	});

	$('#imgInput').on('change', () => {
	
		const imgInp = document.querySelector('#imgInput');
		const img = document.querySelector('#img');

		const [file] = imgInp.files;

		if (file)
		img.src = URL.createObjectURL(file);
	});

	$("#changePwd").click(function () {
		if( $('#changePwd').is(':checked') ) {
			$("#divActual").removeClass('d-none');
			$("#divNueva").removeClass('d-none');
			//$("#divMSG").removeClass('d-none');
			$("#divConfirmar").removeClass('d-none');
			$(".hideShowPassword-wrapper").addClass('w-100');

		} else {
			$("#divActual").addClass('d-none');
			$("#divNueva").addClass('d-none');
			//$("#divMSG").addClass('d-none');
			$("#divConfirmar").addClass('d-none');

			$("#inputPasswordActual").val('');
			$("#inputPassword").val('');
			$("#inputPassword2").val('');
		}
	});

	$('#formUpdatePerfil').submit(function(event) {
        
        event.preventDefault();
        
        actualizarPerfil();
    });

	$("#formUpdatePerfil").validate({
		rules: {
			'persona[nombre]': {
				required:true,
				checkLetras: true,
				validarNombre: true,
			},
			'medioContactoCel[celular]': {
				required:true
			},
			'passwordActual': 'required',
			'password': {
				required:true,
				pwcheck: true,
				minlength:8,
				maxlength:20
			},
			inputPassword2: {
				required: true,
				equalTo: "#inputPassword"
			}		
		},
		messages: {
			'usuario[password]': {
				pwcheck: "La contraseña debe tener una longitud entre 8 y 20 caracteres compuestos por letras y n&uacute;meros, al menos una letra may&uacute;scula debe contener.",
			},
			'persona[nombre]': {
				checkLetras: "El nombre solo debe contener letras y no dejar espacios al final de la cadena."
			}
		},
		errorPlacement: function(error, element) {
			if (element.attr("elem-msg-error")) {
				error.appendTo("#" + element.attr("elem-msg-error"));
			} else {
				error.appendTo(element.parent());
			}
		}
	});

	$('#inputPasswordActual').hidePassword(true);
    $('#inputPassword').hidePassword(true);        
	$('#inputPassword2').hidePassword(true);
	aplicarClases();
});