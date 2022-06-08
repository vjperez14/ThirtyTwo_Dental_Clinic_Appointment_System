$(document).ready(function(){
    function load_unseen_notification(view = '') {
        $.ajax({
            url: './assets/php/fetch_notif_status.php',
            method: 'POST',
            data: {
                view: view
            },
            dataType: 'JSON',
            success: function(response){
                
                $('.dropdown-menu').html(response.notification);
                if (response.unseen_notification > 0) {
                    $('.count').html(response.unseen_notification);
                }
            }
        });
    }
    load_unseen_notification();

    $(document).on('click', '.dropdown', function () {
        $('.count').html('');
        load_unseen_notification('yes')
    });
    setInterval(function () {
        load_unseen_notification();
    },5000);
});