<?php

namespace Damcclean\Commerce\Http\Controllers\Web;

class RedeemCouponController extends Controller
{
    public function __invoke()
    {
        // validate we have the coupon code

        // lookup the coupon entry

        // if an entry is found
            // calculate the affect (probs using a helper)
            // add the users coupon to their session
            // get a new cart total (with the coupon)
            // return a message saying the coupon is valid
                // change checkout total
                // regenerate payment intent with new price

        // if an entry is not found
            // return an error back to the user
    }
}
