$(document).ready(function () {
	

	// form autocomplete off		
	$(":input").attr('autocomplete', 'off');

	// remove box shadow from all text input
	$(":input").css('box-shadow', 'none');



	// save button click function
	$("#send").click(function () {

		// calling validate function
		var response = validateForm();

		// alert("test");
		// if form validation fails			
		if (response == 0) {
			return;
		}


		// getting all form data
		var name = $("#name").val();
		var email = $("#email").val();
		var message = $("#message").val();



		// sending ajax request
		$.ajax({

			url: './assets/php/sendinquary.php',
			type: 'post',
			data: {
				'name': name,
				'email': email,
				'message': message,
				'save': 1
			},
			beforeSend: function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Pleas Wait...',
                    text: 'Submitting your inquiry',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            success: function (response) {
                console.log(response);
                Swal.fire({
                    icon: 'success',
                    title: 'Inquiry',
                    text: response,
                    timer: 3000,
                    showConfirmButton: false,
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = "contact.php";
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




	// ------------- form validation -----------------

	function validateForm() {

		// removing span text before message
		$("#error").remove();
		var mailformat = /^\w+([.-]?\w+)@\w+([.-]?\w+)(.\w{2,3})+$/;

		if ($("#name").val() == "") {
			$("#name").after("<span id='error' class='text-danger'>Enter your Name</span>");
			return 0;
		}

		if ($("#email").val() == "") {
			$("#email").after("<span id='error' class='text-danger'>Enter your email</span>");
			return 0;
		}

        if (!$("#email").val().match(mailformat)) {
            $("#email").after("<span id='error' class='text-danger'> You have entered invalid email address </span>");
            return 0;
        }

		// validating input if empty
		if ($("#message").val() == "") {
			$("#message").after("<span id='error' class='text-danger'> Write a message </span>");
			return 0;
		}

		return 1;
	}

	// -----------[ Clear span after clicking on inputs] -----------

	$("#name").keyup(function () {
		$("#error").remove();
	});
	$("#email").keyup(function () {
		$("#error").remove();
	});
	$("#message").keyup(function () {
		$("#error").remove();
	});

}); 