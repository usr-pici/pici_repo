function login() {
	
    if ( $("#form_login").valid() ) {
                
        deshabilitarOpciones();

        $.post(
            URL_SITE + 'login/in',
            $('#form_login').serializeArray(),
            function(resp) {
                
                msg(resp.error, resp.msg);

                if (resp.error == 0)
					//setTimeout(window.location = urlConfirmLogin, 2000);
					window.location = urlConfirmLogin;
                else
                    habilitarOpciones();
            },
            'json'
        );
    }
}

function deshabilitarOpciones(){
	$('#btn_enviar').prop('disabled', true);
	$('#linkOlvidastePass, #btn-Google, #btn-facebook, #registrate').addClass('disabled');
	$('#registrate').addClass('d-none');
}

function habilitarOpciones(){
	$('#btn_enviar').prop('disabled', false);
	$('#registrate').removeClass('d-none');
	$('#btn-Google, #btn-facebook').removeClass('disabled');
}

$(function() {	
    
	$('#form_login').submit(function(event) {
        
        event.preventDefault();
		login();       

    });

	$('#form_login').validate({
		rules: {
			'usuario': {
				required:true,
				email:true
			},
			'password': 'required'
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
