function eventosGlobales(){
    /* Configuración Global para peticiones ajax y envío de formularios*/
    $("body").ajaxStart(function(){
        setTimeout(function(){
            $('#myModal').modal('show');
        }, 500);
    });
    $("body").ajaxSuccess(function(){
        setTimeout(function(){
            $('#myModal').modal('hide');
        }, 500);
    });
    $("body").ajaxError(function(){
        setTimeout(function(){
            $('#myModal').modal('hide');
        }, 250);
    });
    //$("form").bind("submit",function(){
    //    $('#myModal').modal('show');
    //});

}
Number.prototype.format = function(){
var number = new String(this);
var result = '';
while( number.length > 3 )
{
 result = '.' + number.substr(number.length - 3) + result;
 number = number.substring(0, number.length - 3);
}
result = number + result;
return result;
};
$(document).ready(function(){
    $(".alert").alert();
    eventosGlobales();
});

Number.prototype.formatCurrency = function() {
    var number = new String(this);
    var splitStr = number.split('.');
    var splitLef = splitStr[0];
    if (splitStr.length > 1 ) {
        if (splitStr[1].length > 2) {
            var decimale = parseInt(splitStr[1].substring(0,3) / 10);
            splitStr[1] = decimale.toString();
        }
        if (splitStr[1].length == 1) splitStr[1] += '0';
    }
    var splitRig = splitStr.length > 1 ? ',' + splitStr[1] :',00';
    var regx = /(\d+)(\d{3})/;
    
    while (regx.test(splitLef)) {
        splitLef = splitLef.replace(regx, '$1' + '.' + '$2');
    }
    return splitLef + splitRig;
};

var iva_rata = 0.12;