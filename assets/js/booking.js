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
			// alert('test');
			swal.fire({
				icon: "error",
                title: "Oops...",
                text: "Failed to book an appoinment",
            });
			return;
		} else {
			
		}
	});




	// ------------- form validation -----------------

	function validateForm() {

		// removing span text before message
		$("#error").remove();


		// validating input if empty
		if ($("#email").val() == "") {
			$("#email").after("<span id='error' class='text-danger'> Enter your email </span>");
			return 0;
		}

		if ($("#adress").val() == "") {
			$("adress").after("<span id='error' class='text-danger'> Enter your Home Address.</span>");
			return 0;
		}
		if ($("#phone").val() == "") {
			$("#phone").after("<span id='error' class='text-danger'> Enter your Contact Phone Number. </span>");
			return 0;
		}

		if ($("#service").val() == "") {
			$("#service").after("<span id='error' class='text-danger'> Enter what kind of service you desire </span>");
			return 0;
		}


		if ($("#datepicker").val() == "") {
			$("#datepicker").after("<span id='error' class='text-danger'> Enter Desired Date </span>");
			return 0;
		}

		if ($("#timepicker1").val() == "") {
			$("#timepicker1").after("<span id='error' class='text-danger'> Enter Desired Time</span>");
			return 0;
		}
		if ($("#message").val() == "") {
			$("#message").after("<span id='error' class='text-danger'> Elaborate your problem. </span>");
			return 0;
		}

		return 1;

	}

	//  ajax realtime validation of time slots if it is taken
	$("#time").blur(function() {
		var time = $('#time').val();
		var date = $('#date').val();
		$.ajax({
				url: './assets/php/book.php',
				type: 'post',
				data: {
					'date':date,
					'time':time,
					'time_check':1,
			},
			success:function(response) {	
				// clear span before error message
				$("#time_error").remove();
				// adding span after email textbox with error message
				$("#time").after("<span id='time_error' class='text-danger'>"+response+"</span>");
			},
			error:function(e) {
				$("#result").html("Something went wrong");
			}
		});
	});


	// -----------[ Clear span after clicking on inputs] -----------

	$("#username").keyup(function () {
		$("#error").remove();
	});


	$("#email").keyup(function () {
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