class Maps {

    idTextCp; idDivMapa; idInputAutocomplete; method; vMarker; map; latitud; longitud; calle; numeroCalle; cp; colonia; localidad; estado; nombreEstadoCorto; pais; nombrePaisCorto; direccion; autocomplete;

    constructor(mapa, autocomplete, txtCp, metodo) {
        this.idDivMapa = mapa;
        this.idInputAutocomplete = autocomplete;
        this.idTextCp = txtCp;
        this.method = metodo;
        //this[this.method](1);
        //console.log( jQuery.type( this.method ) );
        this.initMap();
        this.getInfoCP();
        
        if( jQuery.type( this.method ) === "function" )
            this.getInfoEnterFocus(1);           
        
    }

    set setMap(map){
        this.map = map;
    }

    get getMap(){
        return this.map;
    }

    set setMarker(vMarker){
        this.vMarker = vMarker;
    }

    get getMarker(){
        return this.vMarker;
    }
    
    initMap() {

        this.map = new google.maps.Map(document.getElementById(this.idDivMapa), {
            zoom: 16,
            center: new google.maps.LatLng($('#txtLatitud').val(), $('#txtLongitud').val()),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
                   
        this.vMarker = new google.maps.Marker({
            position: new google.maps.LatLng($('#txtLatitud').val(), $('#txtLongitud').val()),
           // draggable: true
        });
        
        this.map.setCenter(this.vMarker.position);
        this.vMarker.setMap(this.map);

        google.maps.event.addDomListener(this.map, "click", (e) => {
            //this.getInfoCoordinate(e.latLng.toJSON(), this.vMarker, this.map, this.idInputAutocomplete);
            this.getInfoEnterFocus(0, e.latLng.toJSON(), this.vMarker, this.map, this.idInputAutocomplete);
		});

        this.initAutocomplete()
    }

    async getInfoAddress(address,vMarker,map, autocomplete) {

        var geocoder = new google.maps.Geocoder();
        var response = {};

        await geocoder.geocode({
            "address": address
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                Maps.calle = '', Maps.numeroCalle = '', Maps.cp = '', Maps.colonia = '', Maps.localidad = '', Maps.estado = '', Maps.pais = '', Maps.latitud = '', Maps.longitud = '';
                //console.log(JSON.stringify(results));           
                vMarker.setPosition(new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()));
                map.panTo(new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()));

                for (var i = 0; i < results[0].address_components.length; i++) {
                    var addressType = results[0].address_components[i].types[0];

                    $('#'+autocomplete).val(results[0].formatted_address);
                    response.direccion = results[0].formatted_address;

                    if (addressType === "route"){
                        Maps.calle = results[0].address_components[i].long_name;
                        response.calle = results[0].address_components[i].long_name;
                    }

                    if (addressType === "street_number"){
                        Maps.numeroCalle = results[0].address_components[i].long_name;
                        response.numeroCalle = results[0].address_components[i].long_name;
                    }

                    if (addressType === "postal_code"){
                        Maps.cp = results[0].address_components[i].long_name;
                        response.cp = results[0].address_components[i].long_name;
                    }

                    if (addressType === "sublocality" || addressType === 'sublocality_level_1' || addressType === 'political'){
                        Maps.colonia = results[0].address_components[i].long_name;
                        response.colonia = results[0].address_components[i].long_name;
                    }

                    if (addressType === "locality"){
                        Maps.localidad = results[0].address_components[i].long_name;
                        response.localidad = results[0].address_components[i].long_name;
                    }

                    if (addressType === "administrative_area_level_1"){
                        Maps.estado = results[0].address_components[i].long_name;
                        response.estado = results[0].address_components[i].long_name;
                        response.nombreEstadoCorto = results[0].address_components[i].short_name
                    }

                    if (addressType === "country"){
                        Maps.pais = results[0].address_components[i].long_name;
                        response.pais = results[0].address_components[i].long_name;
                        response.nombrePaisCorto = results[0].address_components[i].short_name;
                    }

                    Maps.latitud = results[0].geometry.location.lat();
                    response.latitud = results[0].geometry.location.lat();

                    Maps.longitud = results[0].geometry.location.lng();
                    response.longitud = results[0].geometry.location.lng();  
                }
                                
            } else {
                return response;
            }
        });

        return response;
    }

    async getInfoCoordinate(latLng,vMarker,map,idInputAutocomplete) {

        var geocoder = new google.maps.Geocoder();
        let response = {};

        await geocoder.geocode({
            "latLng": latLng
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                //console.log(JSON.stringify(results));        
                Maps.calle = '', Maps.numeroCalle = '', Maps.cp = '', Maps.colonia = '', Maps.localidad = '', Maps.estado = '', Maps.pais = '', Maps.latitud = '', Maps.longitud = '';
                vMarker.setPosition(new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()));
                map.panTo(new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()));

                for (var i = 0; i < results[0].address_components.length; i++) {
                    var addressType = results[0].address_components[i].types[0];
         
                    $('#'+idInputAutocomplete).val(results[0].formatted_address);
                    response.direccion = results[0].formatted_address;

                    if (addressType === "route"){
                        Maps.calle = results[0].address_components[i].long_name;
                        response.calle = results[0].address_components[i].long_name;
                    }

                    if (addressType === "street_number"){
                        Maps.numeroCalle = results[0].address_components[i].long_name;
                        response.numeroCalle = results[0].address_components[i].long_name;
                    }

                    if (addressType === "postal_code"){
                        Maps.cp = results[0].address_components[i].long_name;
                        response.cp = results[0].address_components[i].long_name;
                    }

                    if (addressType === "sublocality" || addressType === 'sublocality_level_1' || addressType === 'political'){
                        Maps.colonia = results[0].address_components[i].long_name;
                        response.colonia = results[0].address_components[i].long_name;
                    }

                    if (addressType === "locality"){
                        Maps.localidad = results[0].address_components[i].long_name;
                        response.localidad = results[0].address_components[i].long_name;
                    }

                    if (addressType === "administrative_area_level_1"){
                        Maps.estado = results[0].address_components[i].long_name;
                        response.estado = results[0].address_components[i].long_name;
                        response.nombreEstadoCorto = results[0].address_components[i].short_name
                    }

                    if (addressType === "country"){
                        Maps.pais = results[0].address_components[i].long_name;
                        response.pais = results[0].address_components[i].long_name;
                        response.nombrePaisCorto = results[0].address_components[i].short_name;
                    }

                    Maps.latitud = results[0].geometry.location.lat();
                    response.latitud = results[0].geometry.location.lat();

                    Maps.longitud = results[0].geometry.location.lng();
                    response.longitud = results[0].geometry.location.lng();
                }
            } else {
                return response;
            }

        });

        return response;
    }

    initAutocomplete() {

        this.autocomplete = new google.maps.places.Autocomplete(
            document.getElementById(this.idInputAutocomplete),
            {
                types: [],
                componentRestrictions: {'country': ['MX']},
                fields: ['place_id', 'geometry', 'name']
            }
        );
    }

    getInfoEnterFocus(bandera = '', coordenadas = [], marcador = '', mapa = '', id = '') {

        if( document.getElementById(this.idInputAutocomplete) ) {

            document.getElementById(this.idInputAutocomplete).addEventListener('focusout', () => {

                let value = document.getElementById(this.idInputAutocomplete).value;
    
                if( value !== '' ){
                    var response = this.getInfoAddress(value, this.getMarker, this.getMap, this.idInputAutocomplete);
                    this.method(response);
                }
            });
            
            document.getElementById(this.idInputAutocomplete).addEventListener('keyup', (e) => {
    
                if (e.key === 'Enter' || e.keyCode === 13) {
    
                    let value = document.getElementById(this.idInputAutocomplete).value;

                    if( value !== '' ){
                        var response = this.getInfoAddress(value, this.getMarker, this.getMap, this.idInputAutocomplete);
                        this.method(response);              
                    }
                }
            });

            if( bandera == 0){
                var response;
                response = this.getInfoCoordinate(coordenadas,marcador,mapa,id);
                this.method(response);
            }
        }
    }

    getInfoCP() {

        if( document.getElementById(this.idTextCp) ) {
            document.getElementById(this.idTextCp).addEventListener('focusout', () => {
                $("#idEstado").load( URL_SITE + "service/cat_estado", {cp: $("#" + this.idTextCp).val()}, function (resp) { });
                $("#idMunicipio").load( URL_SITE + "service/cat_municipio", {cp: $("#" + this.idTextCp).val()}, function (resp) { });
                $("#idLocalidad").load( URL_SITE + "service/cat_localidad", {cp: $("#" + this.idTextCp).val()}, function (resp) { });
            });
        }
    }
}

function getInfoEnterFocus(resp) {


    resp.then(function(result){
        //console.log(result.estado)
        
        //Modulo de Mapa empresa y sucursal
        $('#txtLatitud').val(result.latitud)
        $('#txtLongitud').val(result.longitud)
        $('#txtCalle').val(result.calle)
        $('#txtNumeroExterior').val(result.numeroCalle)
        $('#txtCp').val(result.cp)

        if( $("#txtCp").val() != '' ){
            $("#idEstado").load( URL_SITE + "service/cat_estado", {cp: $("#txtCp").val()}, function (resp) { });
            $("#idMunicipio").load( URL_SITE + "service/cat_municipio", {cp: $("#txtCp").val()}, function (resp) { });
            $("#idLocalidad").load( URL_SITE + "service/cat_localidad", {cp: $("#txtCp").val()}, function (resp) { });
        }
  
    }).catch(e => {
        msg(1,'No se encontró ningún resultado, verifique.');
    });

};


$(function() {	

    if( $("#txtCp").val() != '' ){
        $("#idEstado").load( URL_SITE + "service/cat_estado", {cp: $("#txtCp").val()}, function (resp) { });
        $("#idMunicipio").load( URL_SITE + "service/cat_municipio", {cp: $("#txtCp").val()}, function (resp) { });
        $("#idLocalidad").load( URL_SITE + "service/cat_localidad", { cp: $("#txtCp").val(), idLocalidad: $('#idLocalidad').val() }, function (resp) { });   
    } 

   let mapa = new Maps('mapaGoogle', 'autocomplete', 'txtCp', getInfoEnterFocus);

    $('#btnSearchGoogle').on('click', () => {
        if( $('#autocomplete').val() != '' )
		    mapa.getInfoEnterFocus(1);
        else 
            msg(1,'Ingrese una dirección.');
	});

});