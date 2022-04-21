$(document).ready(function () {
    $("#paynow").click(function () {
        var ref = $(this).val();
        var tick = String(ref);

        $.ajax({

            url: './php/payprocess.php',
            type: 'post',
            data: {
                'ticket': tick,
                'save': 1
            },
            success: function (response) {
                alert(tick);
                window.location.href = "./php/payprocess.php";
            }
        });
    });
});