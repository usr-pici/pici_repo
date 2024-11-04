class Temporizador {

    idDivTemporizador; 
    idBtnReenviar;

    constructor(temporizador, btnReenviar) {
        this.idDivTemporizador = temporizador;
        this.idBtnReenviar = btnReenviar;

        this.initTemporizador(temporizador,btnReenviar);           
    }

    initTemporizador(temporizador, btnReenviar) {

        $('#'+temporizador).each(function() {
            var endTime = $(this).data('time');
            $(this).countdown(endTime, function(tm) {
                
                if( tm.strftime('%M') == '00' && tm.strftime('%S') == '00'){
                    $('#'+btnReenviar).prop('disabled', false);
                }
    
                $(this).html(tm.strftime('<div class="countdown_box"><div class="countdown-wrap"><span style="font-size: medium;" class="countdown minutes">%M</span><span style="font-size: x-small;" class="cd_text">Minutos</span></div></div><div class="countdown_box"><div class="countdown-wrap"><span style="font-size: medium;" class="countdown seconds">%S</span><span style="font-size: x-small;" class="cd_text">Segundos</span></div></div>'));
            });
        });

    }
}

$(function() {	
   
    let temp = new Temporizador('temporizador', 'btnReenviarCodigo');
           
 });