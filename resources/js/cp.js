import BaseCouponCreateForm from './components/coupons/BaseCreateForm.vue'
import CouponsListing from './components/coupons/Listing.vue'
import CouponPublishForm from './components/coupons/PublishForm.vue'
import CouponAmountFieldtype from './components/fieldtypes/CouponAmountFieldtype.vue'
import CouponCodeFieldtype from './components/fieldtypes/CouponCodeFieldtype.vue'
import CustomerFieldtype from './components/fieldtypes/CustomerFieldtype.vue'
import CustomerFieldtypeIndex from './components/fieldtypes/CustomerFieldtypeIndex.vue'
import MoneyFieldtype from './components/fieldtypes/MoneyFieldtype.vue'
import OrderReceiptFieldtype from './components/fieldtypes/OrderReceiptFieldtype.vue'
import ProductVariantsFieldtype from './components/fieldtypes/ProductVariants/ProductVariantsFieldtype.vue'
import OrderStatusFieldtypeIndex from './components/fieldtypes/OrderStatusFieldtypeIndex.vue'
import PaymentDetailsFieldtype from './components/fieldtypes/PaymentDetailsFieldtype.vue'
import ShippingDetailsFieldtype from './components/fieldtypes/ShippingDetailsFieldtype.vue'
import OrdersListing from './components/orders/Listing.vue'
import OrderPublishForm from './components/orders/PublishForm.vue'
import OrdersChart from './components/widgets/OrdersChart.vue'

// Coupons
Statamic.$components.register('base-coupon-create-form', BaseCouponCreateForm)
Statamic.$components.register('coupons-listing', CouponsListing)
Statamic.$components.register('coupon-publish-form', CouponPublishForm)

// Fieldtypes
Statamic.$components.register('coupon_amount-fieldtype', CouponAmountFieldtype)
Statamic.$components.register('coupon_code-fieldtype', CouponCodeFieldtype)
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
Statamic.$components.register('order-publish-form', OrderPublishForm)

// Widgets
// Statamic.$components.register('orders-chart', OrdersChart)