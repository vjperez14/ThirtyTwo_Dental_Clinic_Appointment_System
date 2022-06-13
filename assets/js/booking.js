$(document).ready(function () {
	
	$("#setapt").click(function () {

		// calling validate function
		var response = validateForm();

		// if form validation fails
		if (response == 0) {
			return;
		} else {
			// window.location.href = "summary.php";
		}

		// getting all form data
		var name = $('#name').val();
		var phone = $('#phone').val();
		var email = $('#email').val();
		var service = $('#service').val();
		// date convertion
		var date = $('#date').val();
		var time = $('#time').val();
		var message = $('#message').val();

		$.ajax({
			url: './assets/php/new_book.php',
			type: 'POST',
			data: {
				'name': name,
				'phone': phone,
				'email': email,
				'service': service,
				'date': date,
				'time': time,
				'message': message,
				'save': 1,
			},
			cache: false,
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
						text: 'Your chosen time slot is already taken',
						timer: 3000,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				} else if (response == "late") {
					
					Swal.fire({
						icon: 'error',
						title: 'Opps...',
						text: "It's Already: "+ formatAMPM(new Date),
						timer: 3000,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				} else if (response == "pending") {
					Swal.fire({
						icon: 'error',
						title: 'Opps...',
						text: 'You have still pending appointment',
						timer: 3000,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				} else {
					Swal.fire({
						icon: 'success',
						title: 'Submitted',
						text: response,
						timer: 1500,
						showConfirmButton: false,
						allowOutsideClick: false
					}).then(() => {
						window.location.href = "summary.php";
					});
				}
			},
			error: function(e) {
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
		$('#error').remove();

		// validating input if empty
		if($('#name').val() == "") {
			$("#name").after("<span id='error' class='text-danger'>Please enter your name</span>");
			return 0;
		}
		if($('#phone').val() == "") {
			$("#phone").after("<span id='error' class='text-danger'>Please enter your phone number</span>");
			return 0;
		}
		if($('#email').val() == "") {
			$("#email").after("<span id='error' class='text-danger'>Please enter your email</span>");
			return 0;
		}
		if($('#service option:selected').prop('disabled')) {
			$("#service").after("<span id='error' class='text-danger'>Please choose a service</span>");
			return 0;
		}
		if($('#date').val() == "") {
			$("#date").after("<span id='error' class='text-danger'>Please select a date</span>");
			return 0;
		}
		if($('#time option:selected').prop('disabled')) {
			$("#time").after("<span id='error' class='text-danger'>Please select a time slot</span><br id='time-br-error'>");
			return 0;
		}
		if($('#message').val() == "") {
			$("#message").after("<span id='error' class='text-danger'>Please elaborate your concern</span>");
			return 0;
		}
		return 1;
	}

	// -----------[ Clear span after clicking on inputs] -----------
	$("#name").keyup(function () {
		$("#error").remove();
	});
	$("#phone").keyup(function () {
		$("#error").remove();
	});
	$("#email").keyup(function () {
		$("#error").remove();
	});
	$("#address").keyup(function () {
		$("#error").remove();
	});
	$("#service").change(function () {
		$("#error").remove();
	});
	$("#date").change(function () {
		$("#error").remove();
	});
	$("#time").change(function () {
		$("#time-br-error").remove();
		$("#error").remove();
	});
	$("#message").keyup(function () {
		$("#error").remove();
	});


	// fetch time slots available
	$('#date').change(function(){
        console.log("changed")
        var date = new Date($('#date').val());
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        var selectedDate = [year, month, day].join('-');
        console.log(selectedDate);
        $.ajax({
            type: "POST",
            url: "./assets/php/fetch_time_slots.php",
            data: {date: selectedDate},
            dataType: 'html',
			
            success: function(data) {
                $('#time-container').html(data);
            },
        });
        
    });

	// Getting time as 12 hour format
	function formatAMPM(date) {
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12;
		hours = hours ? hours : 12; // the hour '0' should be '12'
		minutes = minutes < 10 ? '0'+minutes : minutes;
		var strTime = hours + ':' + minutes + ' ' + ampm;
		return strTime;
	}
}); 