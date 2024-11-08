$(function(){
    
    $("#studio-table-div").on('load', function(){
        
        var idReg = $('#studio-data-form').data('idReg');
        
        if ( idReg === '' ) {
            
            return;
        }
        
        $("#studio-table-div").load(
            URL_SITE + 'pharma/getStudios/' + idReg
        );

    }).trigger('load');
    
    $("#btn-save").click(function(){
        
        var form = 'studio-data-form';
        
        if ( !$('#' + form).valid() ) {
            
            msg(1, "Se encontraron errores en el registro, verifique.");
            return;
        }
        
        var idReg = $('#' + form).data('idReg');
        $.post(
            URL_SITE + 'pharma/save/' + idReg,
            $('#' + form).serializeArray(),
            function(resp) {
                
                msg(resp.error, resp.msg);
                if ( idReg === '' && resp.id ) {
                    
                    $('#' + form).data('idReg', resp.id);
                    $("#studio-tab, #cluesByStudio-tab").removeClass('disabled'); 
                }
            },
            'json'
        );
    });
});