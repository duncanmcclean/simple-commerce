import Vue from 'vue';

var stripe = Stripe(window.stripeKey);
var elements = stripe.elements();

new Vue({
    el: '#app',


    data() {
        return {
            card: elements.create('card', {
                hidePostalCode: true
            }),

            isCouponModalOpen: false,
            isSubmitting: false
        }
    },

    methods: {
        submitCheckout() {
            this.isSubmitting = true;

            stripe.confirmCardPayment(window.paymentIntent, {
                payment_method: {
                    card: this.card
                }
            }).then(function (result) {
                if (result.error) {
                    this.isSubmitting = false;
                    alert(result.error.message);
                } else if (result.paymentIntent.status === 'succeeded') {
                    var paymentMethod = document.getElementsByName('payment_method')[0];
                    paymentMethod.value = result.paymentIntent.payment_method;

                    document.getElementById('payment-form').submit();
                }
            })
        }
    },

    mounted() {
        if (document.getElementById('payment-form')) {
            this.card.mount('#card-element');

            this.card.addEventListener('change', ({error}) => {
                const displayError = document.getElementById('card-errors');

                if (error) {
                    displayError.textContent = error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }
    }
});
