import Vue from 'vue';
import axios from 'axios';

var stripe = Stripe(window.stripeKey);
var elements = stripe.elements();

new Vue({
    el: '#app',


    data() {
        return {
            card: elements.create('card', {
                hidePostalCode: true
            }),

            coupon: '',

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
        },

        redeemCoupon() {
            this.isSubmitting = true;

            axios.post(window.redeemCouponEndpoint, {
                code: this.coupon
            }).then(response => {
                if (response.data.error === true) {
                    this.isSubmitting = false;
                    alert(response.data.message);
                } else {
                    this.isSubmitting = false;
                    alert(response.data.message);

                    window.paymentIntent = response.data.intent;
                    // change the price of the cart in the dom
                }
            }).catch(error => {
                this.isSubmitting = false;
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
