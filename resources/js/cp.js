import CouponListing from './components/coupons/Listing.vue'
import CustomerFieldtype from './components/fieldtypes/CustomerFieldtype.vue'
import CustomerFieldtypeIndex from './components/fieldtypes/CustomerFieldtypeIndex.vue'
import MoneyFieldtype from './components/fieldtypes/MoneyFieldtype.vue'
import OrderReceiptFieldtype from './components/fieldtypes/OrderReceiptFieldtype.vue'
import ProductVariantsFieldtype from './components/fieldtypes/ProductVariants/ProductVariantsFieldtype.vue'
import OrderStatusFieldtypeIndex from './components/fieldtypes/OrderStatusFieldtypeIndex.vue'
import PaymentDetailsFieldtype from './components/fieldtypes/PaymentDetailsFieldtype.vue'
import ShippingDetailsFieldtype from './components/fieldtypes/ShippingDetailsFieldtype.vue'
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
Statamic.$components.register('order_status-fieldtype-index', OrderStatusFieldtypeIndex)
Statamic.$components.register('payment_details-fieldtype', PaymentDetailsFieldtype)
Statamic.$components.register('shipping_details-fieldtype', ShippingDetailsFieldtype)

// Orders
Statamic.$components.register('orders-listing', OrdersListing)
Statamic.$components.register('orders-publish-form', OrdersPublishForm)

// Widgets
// Statamic.$components.register('orders-chart', OrdersChart)