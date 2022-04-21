function mainInfo(id) {
    $.ajax({
        type: "GET",
        url: "./php/calendar_settings.php",
        data: "mainid =" + id,
        success: function(result) {
            $("#somewhere").html(result);
        }
    });
};