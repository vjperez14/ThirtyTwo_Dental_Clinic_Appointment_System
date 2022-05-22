function cancelappt(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to cancel your appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'No',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "./assets/php/updateappt.php?apt_id=" + id + "&event=cancel";
        }
    })
}

$(document).ready(function () {
    $('#updateapt').click(function () {
        // calling validate function
        var response = validateForm();

        // if form validation fails
        if (response == 0) {
            return;
        }

        // getting all form data
        var aptticket = $('#aptticket').val();
        var date = $('#date').val();
        var time = $('#time').val();

        $.ajax({
            url: './assets/php/update_apt.php',
            type: 'POST',
            data: {
                'ticket': aptticket,
                'date': date,
                'time': time,
                'save': 1
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
                        text: "It's Already: " + formatAMPM(new Date),
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Update success',
                        text: response,
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
						window.location.href = "myaccount.php";
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

    function validateForm() {

        if($('#date').val() == "") {
			$("#date").after("<span id='error' class='text-danger'>Please select a date</span>");
			return 0;
		}

        if($('#time option:selected').prop('disabled')) {
			$("#time").after("<span id='error' class='text-danger'>Please select a time slot</span><br id='time-br-error'>");
			return 0;
		}
        return 1;
    }
    $("#date").change(function () {
		$("#error").remove();
	});
	$("#time").change(function () {
        $("#time-br-error").remove();
		$("#error").remove();
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

    // fetch time slots available
    $('#date').change(function () {
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
            data: { date: selectedDate },
            dataType: 'html',
            success: function (data) {
                $('#time-container').html(data);
            },
        });

    });
});
