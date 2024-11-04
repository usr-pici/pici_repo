function irLogin(){
	window.location = URL_SITE + 'login';
}

function registrarUsuario(){

	if ($('#form-register').valid()) {

		$('#btnRegistrarUsuario, #btnRegresarLogin').prop('disabled', true);

		$.post(
			URL_SITE + "user/save",
			$("#form-register").serializeArray(),
			function(resp) {
				msg(resp.error, resp.msg);
				if (resp.error == 0) {
					var idPersona = resp.data.idPersona;
					var bandera = resp.data.bandera;
					var envio = resp.data.envio;
					msg(resp.error, 'Cuenta registrada, favor de revisar su correo para validar su cuenta.');
					window.location = URL_SITE + 'user/notified/' + idPersona + '/' + bandera + '/' + envio;
				} else {
					$('#btnRegistrarUsuario, #btnRegresarLogin').prop('disabled', false);
				}
			},
			'json'
		);
	
	} else {
		msg(1,'Verifique que la información registrada esté completa y correcta.');
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

function resendEmailUser(email, bandera){

	var formData = new FormData();
	formData.append('email', email);
	formData.append('bandera', bandera);
	
	$.ajax({
		url: URL_SITE + "user/resendEmailUser",
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
		},
	});
}

$(function() {

	$("#inputPassword").on('keyup', ValidatePassword)

	$("#btnRegistrarUsuario").click(function () {
		grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'registrarUsuario'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				registrarUsuario();
			});
		});
	});

    $("#form-register").validate({
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
			'medioContactoMail[email]': {
				required:true,
				email:true
			},
			'medioContactoCel[celular]': {
				required:true,
				minlength:10,
				maxlength:10,
				checkNumeros: true
			},
			'usuario[password]': {
				required:true
			},
			inputPassword2: {
				required: true,
				equalTo: "#inputPassword"
			},		
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

    $('#inputPassword, #inputPassword2').hidePassword(true);        
	aplicarClases();
});