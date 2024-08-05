// Fieldtypes
import OrderReceiptFieldtype from './components/Fieldtypes/OrderReceiptFieldtype.vue'
import MoneyFieldtype from './components/Fieldtypes/MoneyFieldtype.vue'
import ProductVariantsFieldtype from './components/Fieldtypes/ProductVariants/ProductVariantsFieldtype.vue'

Statamic.$components.register('order_receipt-fieldtype', OrderReceiptFieldtype)
Statamic.$components.register('money-fieldtype', MoneyFieldtype)
Statamic.$components.register(
    'product-variants-fieldtype',
    ProductVariantsFieldtype
)

// Inputs
import RegionSelector from './components/Inputs/RegionSelector.vue'
Statamic.$components.register('region-selector', RegionSelector)

// Listings
import CouponListing from './components/Listings/CouponListing.vue'
Statamic.$components.register('coupon-listing', CouponListing)

// Widgets
import OrdersChart from './components/Widgets/OrdersChart.vue'
Statamic.$components.register('orders-chart', OrdersChart)


// Fresh... todo: tidy up this file
import OrdersListing from './components/orders/Listing.vue'
import OrdersPublishForm from './components/orders/PublishForm.vue'

Statamic.$components.register('orders-listing', OrdersListing)
Statamic.$components.register('orders-publish-form', OrdersPublishForm)