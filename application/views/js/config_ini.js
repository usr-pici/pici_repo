
function timeStamptoDate(unix_timestamp) {

    // Create a new JavaScript Date object based on the timestamp
    // multiplied by 1000 so that the argument is in milliseconds, not seconds.
    let date = new Date(unix_timestamp * 1000);

    // Year of the date
    let year = date.getFullYear();

    // Month of the date
    let month = date.getMonth() + 1;

    // Day of the date
    let day = date.getDate();

    // Hours part from the timestamp
    let hours = date.getHours();
    // Minutes part from the timestamp
    let minutes = "0" + date.getMinutes();
    // Seconds part from the timestamp
    let seconds = "0" + date.getSeconds();

    // Will display time in 10:30:23 format
    return `${year}-${month}-${day} ${hours}:` + minutes.substr(-2) + ':' + seconds.substr(-2);
}


function msg(tipo, msg, titulo) {//-20 Reenvío de guía
    var tipo_list = {0: "success", 1: "error", '-1': "error", '2': "error", '-10': "error", '-20': "error"};

    var is_touch_device = ("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch;

    tipo = tipo_list[tipo] ? tipo_list[tipo] : tipo;

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: is_touch_device ? "toast-center-movil" : "toast-center",
        timeOut: "5000",
    };

    if (titulo) {
        toastr[tipo](titulo, msg);
    } else {
        toastr[tipo](msg);
    }
}

function popup(selector, config) {
    config = config == undefined ? {} : config;

    if (!$(selector).hasClass("modal")) {
        
        $(selector).appendTo("body");

        var id = $(selector).attr("id") ? $(selector).attr("id") : "";
        var title = $(selector).attr("title") ? $(selector).attr("title") : "";

        $(selector).removeClass("hidden").addClass("modal-body");
        $("#" + id).wrap(
                '<div id="' + id + '_content" class="modal-content"></div>'
                );
        // $("#" + id).wrap(
        // 	'<div id="' + id + '_content" class="container-fluid"></div>'
        // );
        //console.log($("#" + id + '_body').html());
        $("#" + id + "_content").prepend(
                '<div class="modal-header text-center">' +
                '<h4 class="modal-title w-100">' +
                title +
                "</h4>" +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                "</div>"
                );

        $(selector).removeAttr("title");
        $(selector).removeAttr("id");

        $("#" + id + "_content")
                .append('<div class="modal-footer"></div>')
                .wrap(
                        '<div id="' +
                        id +
                        '_dialog" class="modal-dialog' +
                        (config.size ? " modal-" + config.size : "") +
                        '" role="document"></div>'
                        );
        $("#" + id + "_dialog").wrap(
                '<div id="' +
                id +
                '" class="modal inmodal fade"  role="dialog"  aria-hidden="true"' +
                (config.backdrop ? ' data-backdrop="' + config.backdrop + '"' : "") +
                "></div>"
                );
    }

    if (config.color) {
        $(selector)
                .find(".modal-header")
                .css({
                    //backgroundColor: config.color !== undefined ? config.color : '#a0aaaa'/*,
                    backgroundColor:
                            config.color !== undefined
                            ? config.color
                            : "#14a7af" /*,
                             borderRadius: '6px 6px 0 0'*/
                });
    }

    //    console.log( $(selector).html() );
    $(selector)
            .find(".modal-footer")
            .html("");

    var buttons = config.buttons != undefined ? Object.keys(config.buttons) : [];

    for (var i = 0, j = buttons.length; i < j; i++) {
        var b = $(
                '<button type="button" id="' + buttons[i] + '" class="btn btn-outline-primary btn-sm">' +
                buttons[i] +
                "</button>"
                );

        b.click(config.buttons[buttons[i]]);
        $(selector)
                .find(".modal-footer")
                .append(b);
    }

    if (config.open != undefined) {
        $(selector).on("show.bs.modal", config.open);
    }
    if (config.opened != undefined) {
        $(selector).on("shown.bs.modal", config.opened);
    }
    if (config.beforeClose != undefined) {
        $(selector).on("hide.bs.modal", config.beforeClose);
    }
    if (config.close != undefined) {
        $(selector).on("hidden.bs.modal", config.close);
    }
    if (config.width != undefined) {
        $(selector)
                .find(".modal-dialog")
                .css("width", config.width);
    }
    if (config.maxWidth != undefined) {
        $(selector)
                .find(".modal-dialog")
                .css("max-width", config.maxWidth);
    }
    if (config.minWidth != undefined) {
        $(selector)
                .find(".modal-dialog")
                .css("min-width", config.minWidth);
    }
    if (config.height != undefined) {
        $(selector)
                .find(".modal-dialog")
                .css({
                    height: config.height,
                    overflow: "auto"
                });
    }
    if (config.title != undefined) {
        $(selector)
                .find(".modal-title")
                .html(config.title);
    }
}

function Alert(mensaje) {

	var config,
		config_default = {
			title: "Mensaje",
			buttons: {
				Aceptar: function() {
					$("#dialog-alert").modal("hide");
					if (mensaje.ok) mensaje.ok();
				}
			} 
		};

	config = $.extend(true, config_default, mensaje.config ? mensaje.config : {});

	popup("#dialog-alert", config);

	$("#dialog-alert")
		.modal("show")
		.find(".modal-body")
		.html(mensaje.text);

	if( mensaje.bandera == undefined ) mensaje.bandera = '0';

	if( mensaje.bandera == '1' ) {
		$("#dialog-alert").css("color", "red");
		$("#dialog-alert").css("font-size", "18px");
	}
}

function Confirm(mensaje) {
    var config,
            config_default = {
                title: "Confirmar",
                buttons: {
                    Cancelar: function () {
                        $("#dialog-confirm").modal("hide");
                        if (mensaje.cancel)
                            mensaje.cancel();
                    },
                    Aceptar: function () {
                        $("#dialog-confirm").modal("hide");
                        mensaje.ok();
                    }
                } /*,
                 color: '#14a7af'*/
            };

    config = $.extend(true, config_default, mensaje.config ? mensaje.config : {});

    popup("#dialog-confirm", config);

    $("#dialog-confirm")
            .modal("show")
            .find(".modal-body")
            .html(mensaje.text);
}

function aplicarClases($context) {
    //    $('.btn', $context || null).each(function() {
    //        if (!$(this).hasClass('btn-lg') && !$(this).hasClass('btn-sm') && !$(this).hasClass('btn-xs')) {
    //            $(this).addClass('btn-sm');
    //        }
    //    });
    $(".botonIcon", $context || null).each(function () {
        var text = $(this).text();
        var icon =
                $(this).data("icon") !== undefined
                ? $(this).data("icon")
                : $(this).attr("icon");
        $(this).html('<span class="glyphicon glyphicon-' + icon + '"></span>');
        $(this).attr("title", text);
        $(this)
                .removeAttr("data-icon")
                .removeClass("botonIcon")
                .addClass("btn btn-primary");
    });
    $(".botonIconText", $context || null).each(function () {
        var icon =
                $(this).data("icon") !== undefined
                ? $(this).data("icon")
                : $(this).attr("icon");
        $(this).prepend('<span class="glyphicon glyphicon-' + icon + '"></span> ');
        $(this)
                .removeAttr("data-icon")
                .removeClass("botonIconText")
                .addClass("btn btn-primary");
    });
    $(".boton2Icon", $context || null).each(function () {
        var text = $(this).text();
        var iconL =
                $(this).data("icon-left") !== undefined
                ? $(this).data("icon-left")
                : $(this).attr("iconL");
        var iconR =
                $(this).data("icon-right") !== undefined
                ? $(this).data("icon-right")
                : $(this).attr("iconR");
        $(this).html(
                '<span class="glyphicon glyphicon-' +
                iconL +
                '"></span> ' +
                '<span class="glyphicon glyphicon-' +
                iconR +
                '"></span>'
                );
        $(this).attr("title", text);
        $(this)
                .removeAttr("data-icon")
                .removeClass("boton2Icon")
                .addClass("btn btn-primary");
    });

    $(".fecha", $context || null).each(function () {
        $(this).inputmask({
            alias: "date",
            placeholder: "dd/mm/aaaa"
        });
        $(this).datepicker({
            language: "es",
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            calendarWeeks: true,
            autoclose: true,
            //            orientation: 'bottom',
            todayHighlight: true
        });
    });

    $(".maskInteger", $context || null).inputmask("integer", {
        min:1,
        autoGroup: true,
        groupSeparator: ",",
        allowMinus: false
    });

    $(".maskIntegerGT0", $context || null).inputmask("integer", {
        min: 1,
        autoGroup: true,
        groupSeparator: ",",
        allowMinus: false
    });

    $(".maskCantidad1D", $context || null).inputmask("numeric", {
        digits: 2,
        autoGroup: true,
        groupSeparator: ",",
        allowMinus: true
    });
    
    $(".maskCantidad", $context || null).inputmask("numeric", {
        digits: 2,
        autoGroup: true,
        groupSeparator: ",",
        allowMinus: false
    });
    
    $(".maskPorcentaje", $context || null).inputmask("numeric", {
        digits: 2,
        min: 0,
        max: 100,
        allowMinus: false,
        clearIncomplete: true
    });

    $(".maskIntegerWithOutDigits", $context || null).inputmask("integer", {
        digits: 2,
        allowMinus: false,
        rightAlign: false,
    });

    $(".hideShowPassword-toggle").css({'font-size': '1.3rem', 'padding-right': '10px', 'top': '25px', 'background': 'transparent', 'border': 'none'}).on('click ini', function () {

//            console.log("pasooo");
        var $elem = $(this);

        if ($elem.hasClass('hideShowPassword-toggle-hide'))
            $elem.attr('title', "Ocultar contrase\xF1a").html('<i class="fas fa-eye-slash text-danger"></i>');
        else
            $elem.attr('title', "Mostrar contrase\xF1a").html('<i class="fas fa-eye text-danger"></i>');

    }).trigger('ini');
}

function logout() {

    Confirm({
        text: "&iquest;Desea cerrar sesión?",
        ok: function () {
            window.location = URL_SITE + "/login/out";
        }
    });
}

function isJson(strData) {
    try {
        JSON.parse(strData);
    } catch (e) {
        return false;
    }
    return true;
}

$(document)
        .ajaxStart(function () {
            $.blockUI({
                message:
                        "<p>&nbsp;</p><span style='font-weight:bold; font-size:17px;'>Procesando, espere por favor. </span><img src='" +
                        URL_VIEWS +
                        "images/ajax_load.gif' /><p>&nbsp;</p>",
                css: {
                    backgroundColor: "#fff",
                    color: "#000080",
                    border: "1px solid #EADEC8",
                    cursor: "default"
                },
                overlayCSS: {backgroundColor: "#aaa", cursor: "default"},
                opacity: 0.8,
                baseZ: 3000,
            });
        })
        .ajaxComplete(function (event, request) {

            var result;

            if (request.responseJSON) {
                var resp = JSON.stringify(request.responseJSON);

                if (resp !== undefined) {
                    result = JSON.parse(resp);
                    if (result.error && result.error == -1) {
                        window.location = URL_SITE + 'login';
                    }
                }
            }

            if (request.responseText) {
                if (request.responseText == '{"error":-1,"msg":"Redireccionando..."}') {
                    var text = JSON.parse(request.responseText)
                    var resp = JSON.stringify(text);

                    if (resp !== undefined) {
                        result = JSON.parse(resp);
                        if (result.error && result.error == -1) {
                            window.location = URL_SITE + 'login';
                        }
                    }
                }
            }
        })
        .ajaxStop(function () {

            aplicarClases();
            $.unblockUI();
        })
        .on('change', '.multiDep', function () {

            selectsChanged.forEach(selectChanged => {

                if (selectChanged !== $(this).prop('name').replace("reg[", "").replace("]", "")) {

                    $('select[name="reg[' + selectChanged + ']"]').val(null);
                    $('select[name="reg[' + selectChanged + ']"]').empty();
                    $('select[name="reg[' + selectChanged + ']"]').append('<option value="">Seleccionar...</option>');
                }
            });

            multiDepPadre = $(this).val();

            $.post(
                    URL_SITE + "producto/getSelectToUpdate",
                    {idPadre: multiDepPadre},
                    function (resp) {
                        cveAttrSelects = [];
                        resp.forEach(element => {
                            cveAttrSelects.push(element.clave);
                            if (!selectsChanged.includes(element.clave))
                                selectsChanged.push(element.clave);
                        }
                        );

                        cveAttrSelects.forEach(htmlEl => {

                            $('select[name="reg[' + htmlEl + ']"]').load(
                                    URL_SITE + 'producto/getRegsSelect',
                                    {clave: htmlEl, multiDepPadre: multiDepPadre},
                                    function () {
                                    }
                            );
                        });
                    },
                    'json'
                    );
        });

jQuery.validator.addMethod(
        "fechaMayorIgual",
        function (value, element, param) {
            //  console.log(value, param);
            var f1 = param.split("/");
            var f2 = value.split("/");

            return (
                    this.optional(element) ||
                    param === "" ||
                    parseInt(f2[2] + f2[1] + f2[0]) >= parseInt(f1[2] + f1[1] + f1[0])
                    );
        },
        jQuery.validator.format("La fecha debe ser mayor o igual a {0}")
        );

jQuery.validator.addMethod(
        "totalPorcentaje",
        function (value, element, param) {
            //		console.log(value, param);
            var total = 0;
            $(param).each(function () {
                if ($(this).val() !== "")
                    total += parseFloat($(this).val(), 10);
            });
            //		console.log(total, 'Cubre el 100%', total === 100);

            return this.optional(element) || total === 100;
        },
        jQuery.validator.format("La suma de los porcentajes debe ser exactamente 100")
        );

jQuery.validator.addMethod("emails", function (value, element) {
    // console.log($(element).val());			
    if (this.optional(element)) {

        return true;
    }

    var $elem = $(element);
    // $elem.val( $.trim($elem.val()) );
    var emails = $elem.val().split(','), valid = true;
    var exp_reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/

    for (var i = 0, limit = emails.length; i < limit; i++) {

        if (!exp_reg.test($.trim(emails[i]))) {

            valid = false;
            break;
        }
    }

    return valid;
}, "Formato incorrecto: para múltiples direcciones, use coma (,) para separarlas.");

jQuery.validator.addMethod("email", function (value, element) {

    if (this.optional(element)) {

        return true;
    }

    var $elem = $(element);
    // $elem.val( $.trim($elem.val()) );
    var emails = $elem.val().split(','), valid = true;
    var exp_reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/

    for (var i = 0, limit = emails.length; i < limit; i++) {

        if (!exp_reg.test($.trim(emails[i]))) {

            valid = false;
            break;
        }
    }

    return valid;
}, "Formato incorrecto: para correo.");

jQuery.validator.addMethod("pwcheck", function (value) {
    return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) // consists of only these
            && /[a-z]/.test(value) // Minucscula
            && /\d/.test(value) // Numero
            && /[A-Z]/.test(value)
});

jQuery.validator.addMethod("pwdValidate", function (value) {      
    return /^[A-Za-z0-9]+$/.test(value) && /[a-z]/.test(value) && /\d/.test(value) && /[A-Z]/.test(value)
});

jQuery.validator.addMethod("checkLetras", function (value) {
    return /^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/g.test(value)
});

jQuery.validator.addMethod("validarNombre", function (value, element) {
			
    var $elem = $(element);
    var valid = false;
	
	if( $elem.val().trim().replace(/\s+/, ' ').split(' ').length <= 1 ){
		valid = false;
	} else {
		if( $elem.val().trim().replace(/\s+/, ' ').split(' ')[0] != undefined && $elem.val().trim().replace(/\s+/, ' ').split(' ')[1] != undefined){

			if( $elem.val().trim().replace(/\s+/, ' ').split(' ')[0].length >= 2 && $elem.val().trim().replace(/\s+/, ' ').split(' ')[1].length >= 2 ){
				
				if( $elem.val().trim().replace(/\s+/, ' ').split(' ')[0].length >= 3 && $elem.val().trim().replace(/\s+/, ' ').split(' ')[1].length >= 2 )
					valid = true;
				else if( $elem.val().trim().replace(/\s+/, ' ').split(' ')[0].length >= 2 && $elem.val().trim().replace(/\s+/, ' ').split(' ')[1].length >= 3 )
					valid = true;
				else
					valid = false;			
			
			} else {
				valid = false;
			}

		}
	}
    
    return valid;
}, "El nombre debe contener al menos 2 palabras y una debe tener mínimo 2 caracteres y la otra 3.");

jQuery.validator.addMethod("RFC", function (value, element) {

    let rfc = $('#rfc').val();

    let aceptarGenerico = true;

    const re = /^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/;
    var validado = rfc.match(re);

    if (!validado)  // Coincide con el formato general del regex?
        return false;

    // Separar el dígito verificador del resto del RFC
    const digitoVerificador = validado.pop(),
            rfcSinDigito = validado.slice(1).join(''),
            len = rfcSinDigito.length,
            // Obtener el digito esperado
            diccionario = "0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ Ñ",
            indice = len + 1;
    var suma,
            digitoEsperado;

    if (len == 12)
        suma = 0
    else
        suma = 481; // Ajuste para persona moral

    for (var i = 0; i < len; i++)
        suma += diccionario.indexOf(rfcSinDigito.charAt(i)) * (indice - i);
    digitoEsperado = 11 - suma % 11;
    if (digitoEsperado == 11)
        digitoEsperado = 0;
    else if (digitoEsperado == 10)
        digitoEsperado = "A";

    // El dígito verificador coincide con el esperado?
    // o es un RFC Genérico (ventas a público general)?
    if ((digitoVerificador != digitoEsperado)
            && (!aceptarGenerico || rfcSinDigito + digitoVerificador != "XAXX010101000"))
        return false;
    else if (!aceptarGenerico && rfcSinDigito + digitoVerificador == "XEXX010101000")
        return false;
    return rfcSinDigito + digitoVerificador;

}, "Ingrese un RFC valido");

jQuery.validator.addMethod("exactlengthCard", function (value, element, param) {
    return this.optional(element) || value.length == param;
}, $.validator.format(`El n&uacute;mero debe contener 16 d&iacute;gitos.`));

jQuery.validator.addMethod("exactlengthCLABE", function (value, element, param) {
    return this.optional(element) || value.length == param;
}, $.validator.format(`El n&uacute;mero debe contener 18 d&iacute;gitos.`));

jQuery.validator.addMethod("checkNumeros", function (value) {
    return /^[0-9]+$/.test(value)
});

$(function () {

    $.fn.datepicker.dates["es"] = {
        days: [
            "Domingo",
            "Lunes",
            "Martes",
            "Miércoles",
            "Jueves",
            "Viernes",
            "Sábado"
        ],
        daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
        daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        months: [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre"
        ],
        monthsShort: [
            "Ene",
            "Feb",
            "Mar",
            "Abr",
            "May",
            "Jun",
            "Jul",
            "Ago",
            "Sep",
            "Oct",
            "Nov",
            "Dic"
        ],
        today: "Hoy",
        monthsTitle: "Meses",
        clear: "Borrar",
        weekStart: 1,
        format: "dd/mm/yyyy"
    };

    $.extend(true, $.fn.DataTable.defaults, {
        columnDefs: [
            {
                orderable: false,
                className: "text-center",
                width: "80px",
                targets: [-1]
            }
        ],
        dom: "tip",
        language: {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Ningún dato disponible en esta tabla",
            sInfo:
                    "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
            sInfoPostFix: "",
            sSearch: "Buscar:",
            sUrl: "",
            sInfoThousands: ",",
            sLoadingRecords: "Cargando...",
            oPaginate: {
                sFirst: '<i class="fa fa-step-backward"></i>',
                sLast: '<i class="fa fa-step-forward"></i>',
                sNext: '<i class="fa fa-forward"></i>',
                sPrevious: '<i class="fa fa-backward"></i>',
            },
            oAria: {
                sSortAscending:
                        ": Activar para ordenar la columna de manera ascendente",
                sSortDescending:
                        ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    aplicarClases();
});
