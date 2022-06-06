$(document).ready(function () {
    $("#save").click(function () {
        // getting all form data
        var id = $("#id").val();
		var fname = $("#firstname").val();
		var minitial = $("#minitial").val();
		var lname = $("#lastname").val();
		var email = $("#email").val();
		var phone = $("#phone").val();

        $.ajax({

			url: './assets/php/updateprofile.php',
			type: 'post',
			data: {
                'id': id,
				'firstname': fname,
				'middleinitial': minitial,
				'lastname': lname,
				'email': email,
				'phone': phone,
				'save': 1
			},
            beforeSend: function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Pleas Wait...',
                    text: 'Submitting your form',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            success: function (response) {
                console.log(response);
                Swal.fire({
                    icon: 'success',
                    title: 'Eidt Success',
                    text: response,
                    timer: 3000,
                    showConfirmButton: false,
                    allowOutsideClick: false
                }).then(() => {
                    location.reload(true);
                });
            },
            error: function (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Opps...',
                    text: 'Something went wrong',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            }

		});
    });
});