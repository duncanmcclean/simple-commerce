import CouponListing from './components/coupons/Listing.vue'
import CustomerFieldtype from './components/fieldtypes/CustomerFieldtype.vue'
import CustomerFieldtypeIndex from './components/fieldtypes/CustomerFieldtypeIndex.vue'
import MoneyFieldtype from './components/fieldtypes/MoneyFieldtype.vue'
import OrderReceiptFieldtype from './components/fieldtypes/OrderReceiptFieldtype.vue'
import ProductVariantsFieldtype from './components/fieldtypes/ProductVariants/ProductVariantsFieldtype.vue'
import OrdersListing from './components/orders/Listing.vue'
import OrdersPublishForm from './components/orders/PublishForm.vue'
import OrdersChart from './components/widgets/OrdersChart.vue'

// Coupons
// Statamic.$components.register('coupon-listing', CouponListing)

// Fieldtypes
Statamic.$components.register('customer-fieldtype', CustomerFieldtype)
Statamic.$components.register('customer-fieldtype-index', CustomerFieldtypeIndex)
Statamic.$components.register('money-fieldtype', MoneyFieldtype)
Statamic.$components.register('order_receipt-fieldtype', OrderReceiptFieldtype)
Statamic.$components.register('product-variants-fieldtype', ProductVariantsFieldtype)

// Orders
Statamic.$components.register('orders-listing', OrdersListing)
Statamic.$components.register('orders-publish-form', OrdersPublishForm)

// Widgets
// Statamic.$components.register('orders-chart', OrdersChart)