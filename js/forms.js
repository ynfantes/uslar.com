$.datepicker.setDefaults( $.datepicker.regional["es"]);
$(document).ready(function() {
    $('.date').datepicker({
        dateFormat: "yy-mm-dd",
        minDate: new Date()
    });
    $('.container form').each(function() {
        $(this).validate({
            errorClass: 'help-inline',
            validClass: 'help-inline',
            errorElement: 'span',
            highlight: function (element) {
                $(element).parents("div.clearfix").addClass("error").removeClass("success");
            }, 
            unhighlight: function (element) {
                $(element).parents(".error").removeClass("error").addClass("success");
            }
        });
    });
    $("input[type='submit']").click(function() {
        if ($(this).parents("form").eq(0).valid()) {
            return confirm("¿Confirma que desea realizar esta acción?");
        } else {
            return false;
        }
    });
});
