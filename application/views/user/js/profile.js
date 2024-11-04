function actualizarPerfil(){
	
	if( $('#changePwd').is(':checked') ) {
		$("#inputPasswordActual").attr("name","usuario[passwordActual]");
		$("#inputPassword").attr("name","usuario[password]");
		$("#inputPassword2").attr("name","inputPassword2");
	} else {
		$("#inputPasswordActual, #inputPassword, #inputPassword2").removeAttr("name");
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

		$('#btnActualizarUsuario, #btnRegresarInicio').prop('disabled', true);

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
					$('#btnActualizarUsuario, #btnRegresarInicio').prop('disabled', false);
				}
			},
		});
				
	}
}

function ValidatePassword() {

	var rules = [
        {
          Pattern: "^[A-Za-z0-9]+$",
          Target: "Numbers"
        },
        {
            Pattern: "[0-9]",
            Target: "Numbers3"
        },
		{
            Pattern: "[a-z]",
            Target: "Numbers2"
        },
        {
          Pattern: "[A-Z]",
          Target: "UpperCase"
        },
      ];

	  var password = $(this).val();

	  $("#inputPassword").rules( "add",{
		pwdValidate: true,
		minlength:8,
		maxlength:20
	} );
    	
      $("#UpperCase").removeClass(new RegExp(rules[3].Pattern).test(password) ? "glyphicon-remove" : "glyphicon-ok"); 
      $("#UpperCase").addClass(new RegExp(rules[3].Pattern).test(password) ? "glyphicon-ok" : "glyphicon-remove");

      $("#imgUpperCase").removeClass(new RegExp(rules[3].Pattern).test(password) ? "fas fa-times" : "fas fa-check"); 
      $("#imgUpperCase").addClass(new RegExp(rules[3].Pattern).test(password) ? "fas fa-check" : "fas fa-times");

      if(password.length == 0) {
          $("#UpperCase").addClass("glyphicon-remove");
          $("#imgUpperCase").addClass("fas fa-times");
      }

      $("#Numbers").removeClass(( new RegExp(rules[0].Pattern).test(password) && new RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "glyphicon-remove" : "glyphicon-ok"); 
      $("#Numbers").addClass(( new RegExp(rules[0].Pattern).test(password) && new RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "glyphicon-ok" : "glyphicon-remove");

      $("#imgNumbers").removeClass((new RegExp(rules[0].Pattern).test(password) && new RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "fas fa-times" : "fas fa-check"); 
      $("#imgNumbers").addClass((new RegExp(rules[0].Pattern).test(password) && new RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "fas fa-check" : "fas fa-times");

      if(password.length == 0) {
          $("#Numbers").addClass("glyphicon-remove");
          $("#imgNumbers").addClass("fas fa-times");
      }

	$("#Length").removeClass((password.length >= 8 && password.length <= 20) ? "glyphicon-remove" : "glyphicon-ok");
	$("#Length").addClass((password.length >= 8 && password.length <= 20)? "glyphicon-ok" : "glyphicon-remove");

	$("#imgLength").removeClass((password.length >= 8 && password.length <= 20) ? "fas fa-times" : "fas fa-check");
	$("#imgLength").addClass((password.length >= 8 && password.length <= 20)? "fas fa-check" : "fas fa-times");
      
}

$(function() {

	$('#reempImg').hide();

	if( bandera == 1 ){
		$('#reempImg').show();
		$('#imgInput').hide();
	}

	$("#inputPassword").on('keyup', ValidatePassword);

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
			$("#divActual, #divNueva, #divReglas, #divConfirmar").removeClass('d-none');
			$(".hideShowPassword-wrapper").addClass('w-100');
			$(".hideShowPassword-toggle").css({'font-size': '1.3rem', 'padding-right': '10px', 'top': '9px', 'background': 'transparent', 'border': 'none'});

			$("#Numbers, #UpperCase, #Length").addClass("glyphicon-remove");
          	$("#imgNumbers, #imgUpperCase, #imgLength").addClass("fas fa-times");

			$("#Length").removeClass('glyphicon-ok');

		} else {

			$("#divActual, #divNueva, #divReglas, #divConfirmar").addClass('d-none');

			$("#inputPasswordActual, #inputPassword, #inputPassword2").val('');

			$("#Numbers, #UpperCase, #Length").removeClass('glyphicon-ok');
			$("#Numbers, #UpperCase, #Length").addClass("glyphicon-remove");
			$("#imgUpperCase, #imgLength, #imgNumbers").addClass("fas fa-times");

		}
	});

	$("#btnActualizarUsuario").click(function () {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'actualizarPerfil'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				actualizarPerfil();
			});
		});
	});

	$("#formUpdatePerfil").validate({
		rules: {
			'persona[nombre]': {
				required:true,
				checkLetras: true,
			},
			'persona[apellidos]': {
				required:true,
				checkLetras: true,
			},
			'persona[fechaNacimiento]': {
				required:true,
			},
			'medioContactoCel[celular]': {
				required:true,
				minlength:10,
				maxlength:10,
				checkNumeros: true
			},
			'usuario[passwordActual]': {
				required: true
			},
			'usuario[password]': {
				required:true,
			},
			inputPassword2: {
				required: true,
				equalTo: "#inputPassword"
			}		
		},
		messages: {
			'usuario[password]': {
				pwdValidate: "Errores encontrados, verifique.",
				minlength: 'Errores encontrados, verifique.',
				maxlength: 'Errores encontrados, verifique.'
			},
			'persona[nombre]': {
				checkLetras: "El nombre solo debe contener letras y no dejar espacios al final de la cadena."
			},
			'persona[apellidos]': {
				checkLetras: "El nombre solo debe contener letras y no dejar espacios al final de la cadena."
			},
		},
		errorPlacement: function(error, element) {
			if (element.attr("elem-msg-error")) {
				error.appendTo("#" + element.attr("elem-msg-error"));
			} else {
				error.appendTo(element.parent());
			}
		}
	});

	$('#inputPasswordActual, #inputPassword, #inputPassword2').hidePassword(true);
	aplicarClases();
});