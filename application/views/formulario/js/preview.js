
function validar_grupo_pregunta(grupo, comparacion, valor){ 

	resultado = false;
	$( grupo ).each(function(i){
	   //alert( ' .. '+$(this).val());
	   if(comparacion == '==' && $(this).val() == valor){
		  resultado++;
		}else if(comparacion == '!=' && $(this).val() != valor){
		  resultado++;
		}
	});
  
	return (resultado > 0) ? true : false;
  }

  function ocultar_campo(clase){ 

    $( '.div_preg_'+clase ).hide('fast', function() { 
            ocultar_subcondicion(this);

            limpiar_campos(clase);
            
    });


}

function ocultar_subcondicion(campo){
    
    var clase = $(campo).attr('id');
    $('.div_dep_'+clase).hide();

    limpiar_campos(clase);
    
}

function limpiar_campos(clase){

    $(":checkbox, :radio", $('#'+clase)).each(function(){   
        $(this).attr('checked', false);
    });
    $(":text, :file", $('#'+clase)).each(function(){   
        $(this).val('');
    });

    $("textarea", $('#'+clase)).each(function(){   
        $(this).val('');
    });

    $("select", $('#'+clase)).each(function(){   
        $(this).val('');
    });
    return;
    
}

$(function () {

    $('#btn_enviar').click(function(){

        Confirm({
            text: "&iquest;Datos correctos?",
            ok: function(obj) {
                
                var formData = new FormData();
                            
                var params = $( $("#form_formulario") ).serializeArray();
                $.each(params, function (i, val) {
                    formData.append(val.name, val.value);
                });
        
                $.each($("input[type=file]"), function(i, objFile) {
                    $.each(objFile.files,function(j,file){
                        //formData.append('photo['+i+']', file);
                        formData.append('archivo_'+$(objFile).attr('name')+'['+i+']', file);
                        console.log('++++', 'archivo_'+$(objFile).attr('name')+'['+i+']');
                    });
                });
                    
                $.ajax({
                    url: URL_SITE + 'formulario/response',
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    success: function(data){
                        
                        if ( data.error == 0 ) {
                            
                            window.location = URL_SITE + 'confirmacion';
        
                        }else{
                            $("#msg_result").html(data.msg).show();
                        }

                        obj.dialog("close");    
                        
                    }
                });

            }
        });
         
    });

	
});
