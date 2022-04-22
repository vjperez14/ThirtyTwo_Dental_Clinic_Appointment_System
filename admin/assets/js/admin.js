$(document).ready(function () {
    for (i = 0; i < 1000; i++) {
        $("#approve" + i).click(function () {
            var ref = $(this).val();
            var tick = String(ref);

            $.ajax({
                url: './assets/php/setstatus.php',
                type: 'post',
                data: {
                    'appticket': tick,
                    'save': 1
                },
                success: function (response) {
                    alert("Approved");
                    window.location.href = window.location.href;
                }
            });
        });


        $("#cancel" + i).click(function () {
            var ref = $(this).val();
            var tick = String(ref);

            $.ajax({
                url: './assets/php/setstatus.php',
                type: 'post',
                data: {
                    'canticket': tick,
                    'save': 1
                },
                success: function (response) {
                    alert("Cancelled");
                    window.location.href = window.location.href;
                }
            });
        });
        $("#decline" + i).click(function () {
            var ref = $(this).val();
            var tick = String(ref);

            $.ajax({
                url: './assets/php/setstatus.php',
                type: 'post',
                data: {
                    'decticket': tick,
                    'save': 1
                },
                success: function (response) {
                    alert("Declined");
                    window.location.href = window.location.href;
                }
            });
        });
        $("#complete" + i).click(function () {
            var ref = $(this).val();
            var tick = String(ref);

            $.ajax({
                url: './assets/php/setstatus.php',
                type: 'post',
                data: {
                    'comticket': tick,
                    'save': 1
                },
                success: function (response) {
                    alert("Mark as Completed");
                    window.location.href = window.location.href;
                }
            });
        });
    }
});