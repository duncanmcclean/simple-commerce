import CountryRegionFieldtype from './components/Fieldtypes/CountryRegionFieldtype.vue'
import CouponCodeFieldtype from './components/Fieldtypes/CouponCodeFieldtype.vue'
import CouponSummaryFieldtype from './components/Fieldtypes/CouponSummaryFieldtype.vue'
import CouponValueFieldtype from './components/Fieldtypes/CouponValueFieldtype.vue'
import GatewayFieldtype from './components/Fieldtypes/GatewayFieldtype.vue'
import MoneyFieldtype from './components/Fieldtypes/MoneyFieldtype.vue'
import OrderStatusFieldtype from './components/Fieldtypes/OrderStatusFieldtype.vue'
import OrderStatusIndexFieldtype from './components/Fieldtypes/OrderStatusIndexFieldtype.vue'
import PaymentStatusFieldtype from './components/Fieldtypes/PaymentStatusFieldtype.vue'
import PaymentStatusIndexFieldtype from './components/Fieldtypes/PaymentStatusIndexFieldtype.vue'
import ProductVariantFieldtype from './components/Fieldtypes/ProductVariantFieldtype.vue'
import ProductVariantsFieldtype from './components/Fieldtypes/ProductVariantsFieldtype.vue'
import StatusLogFieldtype from './components/Fieldtypes/StatusLogFieldtype.vue'
import CouponListing from './components/Listings/CouponListing.vue'
import OrdersChart from './components/Widgets/OrdersChart.vue'

Statamic.booting(() => {
    // Fieldtypes
    Statamic.$components.register('country_region-fieldtype', CountryRegionFieldtype)
    Statamic.$components.register('coupon-code-fieldtype', CouponCodeFieldtype)
    Statamic.$components.register(
        'coupon-summary-fieldtype',
        CouponSummaryFieldtype
    )
    Statamic.$components.register('coupon-value-fieldtype', CouponValueFieldtype)
    Statamic.$components.register('gateway-fieldtype', GatewayFieldtype)
    Statamic.$components.register('money-fieldtype', MoneyFieldtype)
    Statamic.$components.register('order-status-fieldtype', OrderStatusFieldtype)
    Statamic.$components.register(
        'order_status-fieldtype-index',
        OrderStatusIndexFieldtype
    )
    Statamic.$components.register(
        'payment-status-fieldtype',
        PaymentStatusFieldtype
    )
    Statamic.$components.register(
        'payment_status-fieldtype-index',
        PaymentStatusIndexFieldtype
    )
    Statamic.$components.register(
        'product-variant-fieldtype',
        ProductVariantFieldtype
    )
    Statamic.$components.register(
        'product-variants-fieldtype',
        ProductVariantsFieldtype
    )
    Statamic.$components.register('sc_status_log-fieldtype', StatusLogFieldtype)

    // Listings
    Statamic.$components.register('coupon-listing', CouponListing)

    // Widgets
    Statamic.$components.register('orders-chart', OrdersChart)
});