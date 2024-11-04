function enviarToken(){

	if ( $("#formEnviarToken").valid() ) {
		
		$('#btnRecuperar, #btnRegresar, #btnReenviarCodigo').prop('disabled', true);

		$.post(
			URL_SITE + 'user/recoverPasswordToken',
			{ 'email': $('#txtEmail').val(), 'token': $('#tokenRecaptcha').val() },
			function(resp) {
				msg(resp.error, resp.msg);
				if (resp.error == 0) {			
					setTimeout(validar, 2000);
				} else {
					$('#btnRecuperar, #btnRegresar, #btnReenviarCodigo').prop('disabled', false);
				}
			},
			'json'
		);

	}
}

function reenviarToken(){

	$('#btnRegresarEnvio, #btnReenviarCodigo, #btnValidarCodigo').prop('disabled', true);
	
	$.post(
		URL_SITE + 'user/recoverPasswordToken',
		{ 'email': $('#txtEmail').val(), 'token': $('#tokenRecaptcha').val() },
		function(resp) {
			msg(resp.error, resp.msg);
			if (resp.error == 0) {
				$('#btnRegresarEnvio, #btnValidarCodigo').prop('disabled', false);
				setTimeout(validarReenvio, 2000);
			} else {
				$('#btnRegresarEnvio, #btnReenviarCodigo, #btnValidarCodigo').prop('disabled', false);
			}
		},
		'json'
	);		
}

function validarToken(){

	if ( $("#formValidarCodigo").valid() ) {

		$('#btnValidarCodigo, #btnRegresarEnvio, #btnReenviarCodigo').prop('disabled', true);

		$.post(
			URL_SITE + 'user/checkTokenUser',
			{ 
				'email': $('#txtEmail').val(),
				'token': $('#txtToken').val(),
				'recoverPassword': 1,
				'tokenRecaptcha': $('#tokenRecaptcha').val()
			},
			function(resp) {
				msg(resp.error, resp.msg);
				if (resp.error == 0){
					setTimeout(reset, 2000);
				} else {
					$('#btnValidarCodigo, #btnRegresarEnvio').prop('disabled', false);
				}
			},
			'json'
		);

	}
}

function actualizarPassword(){
	
	if ( $("#formUpdatePassword").valid() ) {
		var formData = new FormData();
		var params = $($("#formUpdatePassword")).serializeArray();

		$.each(params, function (i, val) {
			formData.append(val.name, val.value);
		});

		$('#btnActualizarPassword, #btnRegresarValidar').prop('disabled', true);
		
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
					$("#password, #inputPassword2").val('');
					msg(0, 'La contraseña fue cambiada exitosamente.');	
					setTimeout(login, 2000);
				} else {
					$('#btnActualizarPassword, #btnRegresarValidar').prop('disabled', false);
				}
			},
		});
				
	}
}

function validar(){
	window.location = URL_SITE + 'login/validar';
}

function validarReenvio(){
	window.location = 'validar';
}

function reset(){
	window.location = URL_SITE + 'login/reset';
}

function login(){
	window.location = URL_SITE + 'login';
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

	  $("#password").rules( "add",{
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

      $("#Numbers").removeClass((new RegExp(rules[0].Pattern).test(password) && new RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "glyphicon-remove" : "glyphicon-ok"); 
      $("#Numbers").addClass((new RegExp(rules[0].Pattern).test(password) && RegExp(rules[1].Pattern).test(password) && new RegExp(rules[2].Pattern).test(password)) ? "glyphicon-ok" : "glyphicon-remove");

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

	$("#password").on('keyup', ValidatePassword);

	$('#btnRegresar').on('click', () => {
		window.location = URL_SITE + 'login';
	});

	$('#btnRegresarEnvio').on('click', () => {
		window.location = URL_SITE + 'login/recuperar';
	});

	$('#btnRegresarValidar').on('click', () => {
		window.location = URL_SITE + 'login/validar';
	});

	$('#btnReenviarCodigo').on('click', () => {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'reenviarToken'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				reenviarToken();
			});
		});
	});

	$("#btnRecuperar").click(function () {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'enviarToken'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				enviarToken();
			});
		});
	});

	$("#btnValidarCodigo").click(function () {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'validarToken'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				validarToken();
			});
		});
	});

	$("#btnActualizarPassword").click(function () {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'actualizarPassword'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				actualizarPassword();
			});
		});
	});
    
	$('#formEnviarToken').validate({
		rules: {
			'reg[email]': {
				required:true,
				email:true
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

	$('#formValidarCodigo').validate({
		rules: {
			'reg[token]': 'required'
		},
		errorPlacement: function(error, element) {
			if (element.attr("elem-msg-error")) {
				error.appendTo("#" + element.attr("elem-msg-error"));
			} else {
				error.appendTo(element.parent());
			}
		}
	});

	$("#formUpdatePassword").validate({
		rules: {
			'usuario[password]':{
				required:true,
			},
			inputPassword2: {
				required: true,
				equalTo: "#password"
			}
		},
		messages: {
			'usuario[password]': {
				pwdValidate: "Errores encontrados, verifique.",
				minlength: 'Errores encontrados, verifique.',
				maxlength: 'Errores encontrados, verifique.'
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

	$('#password, #inputPassword2').hidePassword(true);        
	aplicarClases();

});
