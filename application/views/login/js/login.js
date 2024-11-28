function login() {
	
    if ( $("#form_login").valid() ) {
                
        deshabilitarOpciones();

        $.post(
            URL_SITE + 'login/in',
            $('#form_login').serializeArray(),
            function(resp) {
                
                msg(resp.error, resp.msg);

                if (resp.error == 0)
					window.location = URL_SITE + 'patient';
                else
                    habilitarOpciones();
            },
            'json'
        );
    }
}

function deshabilitarOpciones(){
	$('#btn_enviar').prop('disabled', true);
	$('#linkOlvidastePass').addClass('disabled');
}

function habilitarOpciones(){
	$('#btn_enviar').prop('disabled', false);
}

$(function() {	
    
	$('#form_login').submit(function(event) {
        
        event.preventDefault();
        
        grecaptcha.ready(function() {
			grecaptcha.execute(keySiteWeb, {
				action: 'login'
			}).then(function(token) {
				$('#tokenRecaptcha').val(token);
				login();
			});
		});
    });

	$('#form_login').validate({
		rules: {
			'usuario': {
				required:true,
				email:true
			},
			'password': 'required',
			'avisoPrivacidad': 'required',
			'terminosCond': 'required'
		},
		errorPlacement: function(error, element) {
			if (element.attr("elem-msg-error")) {
				error.appendTo("#" + element.attr("elem-msg-error"));
			} else {
				error.appendTo(element.parent());
			}
		}
	});

	$('#password').hidePassword(true);        
	aplicarClases();
        
});
