$(document).ready(function() {
    $("#id_copytext").bind("click",function(o) {
        if ($("#id_copytext:checked").length) {
            $("#id_paddressline1").val($("#id_addressline1").val());
            $("#id_paddressline2").val($("#id_addressline2").val());
            $("#id_pcity").val($("#id_city").val());
            $("#id_pstate").val($("#id_state").val());
            $("#id_pcountry").val($("#id_country").val());
            $("#id_ppincode").val($("#id_pincode").val());
        } else {
            $("#id_paddressline1").val("");
            $("#id_paddressline2").val("");
            $("#id_pcity").val("");
            $("#id_pstate").val("");
            $("#id_pcountry").val("");
            $("#id_ppincode").val("");
        }
    });
});