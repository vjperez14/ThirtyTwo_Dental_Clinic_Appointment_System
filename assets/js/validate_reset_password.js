$(document).ready(function () {
	

	// form autocomplete off		
	$(":input").attr('autocomplete', 'off');

	// remove box shadow from all text input
	$(":input").css('box-shadow', 'none');



	// save button click function
	$("#submit").click(function () {

		// calling validate function
		var response = validateForm();

		// alert("test");
		// if form validation fails			
		if (response == 0) {
			return;
		}


		// getting all form data
		var email = $("#reset-email").val();



		// sending ajax request
		$.ajax({

			url: './assets/php/reset_password_logic.php',
			type: 'post',
			data: {
				'email': email,
				'reset_password': 1
			},
			beforeSend: function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Pleas Wait...',
                    text: 'Submitting your request to reset password',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            success: function (response) {
                console.log(response);
                if (response == "empty") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Your email is required',
                        timer: 1500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                } else if (response == "zero") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Sorry, no email exists on our system with that email',
                        timer: 1500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reset Password Success',
                        text: response,
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "login.php";
                    });
                }
                
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




	// ------------- form validation -----------------

	function validateForm() {

		// removing span text before message
		$("#error").remove();
		var mailformat = /^\w+([.-]?\w+)@\w+([.-]?\w+)(.\w{2,3})+$/;

		if ($("#reset-email").val() == "") {
			$("#reset-email").after("<span id='error' class='text-danger'>Enter your email</span>");
			return 0;
		}

        if (!$("#reset-email").val().match(mailformat)) {
            $("#reset-email").after("<span id='error' class='text-danger'> You have entered invalid email address </span>");
            return 0;
        }

		return 1;
	}

	// -----------[ Clear span after clicking on inputs] -----------

	$("#reset-email").keyup(function () {
		$("#error").remove();
	});
	$("#reset-email").keyup(function () {
		$("#error").remove();
	});

}); 