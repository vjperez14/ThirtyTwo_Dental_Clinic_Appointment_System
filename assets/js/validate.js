$(document).ready(function () {
	

	// form autocomplete off		
	$(":input").attr('autocomplete', 'off');

	// remove box shadow from all text input
	$(":input").css('box-shadow', 'none');



	// save button click function
	$("#savebtn").click(function () {

		// calling validate function
		var response = validateForm();

		// alert("test");
		// if form validation fails			
		if (response == 0) {
			return;
		}


		// getting all form data
		var fname = $("#firstname").val();
		var minitial = $("#minitial").val();
		var lname = $("#lastname").val();
		var email = $("#registeremail").val();
		var password = $("#registerpassword").val();
		var phonenumber = $("#phonenumber").val();



		// sending ajax request
		$.ajax({

			url: './assets/php/registration.php',
			type: 'post',
			data: {
				'firstname': fname,
				'middleinitial': minitial,
				'lastname': lname,
				'registeremail': email,
				'registerpassword': password,
				'phonenumber': phonenumber,
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
                if (response == "taken") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Your email is already registered',
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else {
                    console.log(response);
                    Swal.fire({
                        icon: 'success',
                        title: 'Registered',
                        text: response,
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "registersuccess.php";
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
		var password = $("#registerpassword").val()
		var conFpassword = $("#confirmpassword").val()

		console.log(password.length <= 8);

		if ($("#firstname").val() == "") {
			$("#firstname").after("<span id='error' class='text-danger'>Enter your First Name</span>");
			return 0;
		}

		if ($("#lastname").val() == "") {
			$("#lastname").after("<span id='error' class='text-danger'>Enter your Last Name</span>");
			return 0;
		}


		// validating input if empty
		if ($("#registeremail").val() == "") {
			$("#registeremail").after("<span id='error' class='text-danger'> Enter your email </span>");
			return 0;
		}

		if ($("#registerpassword").val() == "") {
			$("#registerpassword").after("<span id='error' class='text-danger'> Enter your password </span>");
			return 0;
		}	

		if (!$("#registeremail").val().match(mailformat)) {
            $("#registeremail").after("<span id='error' class='text-danger'> You have entered invalid email address </span>");
            return 0;
        }

			
		if (password.length <= 8) {
			$("#registerpassword").after("<span id='error' class='text-danger'> Password must be atleast 8 characters </span>");
			return 0;
		}

		if ($("#confirmpassword").val() == "") {
			$("#confirmpassword").after("<span id='error' class='text-danger'> Re-enter your password </span>");
			return 0;
		}

		if ($("#confirmpassword").val() != $("#registerpassword").val()) {
			$("#confirmpassword").after("<span id='error' class='text-danger'> Password not matched! </span>");
			return 0;
		}

		return 1;

	}


	// ------------------- [ Email blur function ] -----------------

	$("#registeremail").blur(function () {

		var email = $('#registeremail').val();

		// if email is empty then return
		if (email == "") {
			return;
		}


		// send ajax request if email is not empty
		$.ajax({
			url: './assets/php/registration.php',
			type: 'post',
			data: {
				'email': email,
				'email_check': 1,
			},
			success: function (response) {
				var myEle = document.getElementById("email_error");
				// clear span before error message
				$("#email_error").remove();
				// adding span after email textbox with error message
				$("#registeremail").after("<span id='email_error' class='text-danger'>" + response + "</span>");
			},

			error: function (e) {
				$("#result").html("Something went wrong");
			}

		});
	});
	// -----------[ Clear span after clicking on inputs] -----------

	$("#firstname").keyup(function () {
		$("#error").remove();
	});
	$("#minitial").keyup(function () {
		$("#error").remove();
	});
	$("#lastname").keyup(function () {
		$("#error").remove();
	});



	$("#registeremail").keyup(function () {
		$("#error").remove();
		$("span#email_error").remove();
	});

	$("#registerpassword").keyup(function () {
		$("#error").remove();
	});

	$("#confirmpassword").keyup(function () {
		$("#error").remove();
	});

	

}); 