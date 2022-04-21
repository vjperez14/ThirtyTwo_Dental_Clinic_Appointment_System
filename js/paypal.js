paypal.Button.render({
    
    // Configure environment
    env: 'sandbox',
    client: {
        sandbox: 'AZrVUgNGzX4B4hTvAGj-3_5pj5GkgEQpfCab4kN2cMSPLrJnUUppFx0O9VdE2LslS1Q4u1MUpTFTt9rY',
        production: 'demo_production_client_id'
    },
    // Customize button (optional)
    locale: 'en_US',
    style: {
        size: 'small',
        color: 'gold',
        shape: 'pill',
    },

    // Enable Pay Now checkout flow (optional)
    commit: true,

    // Set up a payment
    
    payment: function (data, actions) {
        return actions.payment.create({
            
            transactions: [{
                amount: {
                    total: '500.00',
                    currency: 'PHP'
                }
            }]
        });
    },
    // Execute the payment
    onAuthorize: function (data, actions) {
        return actions.payment.execute().then(function () {
            // Show a confirmation message to the buyer
            var ticket = $('#paypal-button').attr("value");
            alert(ticket);
            var tick = String(ticket);
            $.ajax({
                url: './php/payprocess.php',
                type: 'post',
                data: {
                    'ticket': tick,
                    'save': 1
                },
                success: function (response) {
                    window.location.href = window.location.href;
                }
            });

        });
    }
}, '#paypal-button');