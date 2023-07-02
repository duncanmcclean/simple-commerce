// Fieldtypes

import GatewayFieldtype from './components/Fieldtypes/GatewayFieldtype.vue'
import MoneyFieldtype from './components/Fieldtypes/MoneyFieldtype.vue'
import OrderStatusFieldtype from './components/Fieldtypes/OrderStatusFieldtype.vue'
import OrderStatusIndexFieldtype from './components/Fieldtypes/OrderStatusIndexFieldtype.vue'
import PaymentStatusFieldtype from './components/Fieldtypes/PaymentStatusFieldtype.vue'
import PaymentStatusIndexFieldtype from './components/Fieldtypes/PaymentStatusIndexFieldtype.vue'
import ProductVariantFieldtype from './components/Fieldtypes/ProductVariantFieldtype.vue'
import ProductVariantsFildtype from './components/Fieldtypes/ProductVariantsFieldtype.vue'

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
    ProductVariantsFildtype
)

// Inputs

import RegionSelector from './components/Inputs/RegionSelector.vue'

Statamic.$components.register('region-selector', RegionSelector)

// Overview

import OverviewConfigure from './components/Overview/Configure.vue'
import OverviewLowStockProducts from './components/Overview/LowStockProducts.vue'
import OverviewOrdersChart from './components/Overview/OrdersChart.vue'
import Overview from './components/Overview/Overview.vue'
import OverviewRecentOrders from './components/Overview/RecentOrders.vue'
import OverviewTopCustomers from './components/Overview/TopCustomers.vue'

Statamic.$components.register('overview-configure', OverviewConfigure)
Statamic.$components.register(
    'overview-low-stock-products',
    OverviewLowStockProducts
)
Statamic.$components.register('overview-orders-chart', OverviewOrdersChart)
Statamic.$components.register('overview', Overview)
Statamic.$components.register('overview-recent-orders', OverviewRecentOrders)
Statamic.$components.register('overview-top-customers', OverviewTopCustomers)

// Listings
import CouponListing from './components/Listings/CouponListing.vue'

Statamic.$components.register('coupon-listing', CouponListing)
