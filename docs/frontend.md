# Overview
To make Simple Commerce as flexible as possible, we've decided not to ship things like templates or routing as part of the product. We believe that every e-commerce site should have a custom frontend and shouldn't be burnded by having to fit around someone else's setup.

This means that everything from making the product pages, the checkout flow and setting up routing for that stuff is all on your shoulders.

However, if you want some guidance around how to build stuff or how certain things work, have a look at [our example repo](https://github.com/doublethreedigital/simple-commerce-example).

## But how do I integrate with Simple Commerce?
Easy. How do you integrate any other Statamic addon into your site? Through tags. Simple Commerce provides a set of tags you can use in your templates to do things like outputting the user's cart and getting product information.