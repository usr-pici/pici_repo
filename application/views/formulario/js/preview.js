
/*function showFieldPreview(sel, idPregunta) {

	id = '#s_' + idPregunta;

	idPreguntaMostrar = $(sel).find(':selected').attr('data-cond')
	
	console.log(idPreguntaMostrar)
	
	if( idPreguntaMostrar != '' )
		$('#p_' + idPreguntaMostrar).show()
	else
		$('#p_' + resp).hide()



}*/

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

	
});
