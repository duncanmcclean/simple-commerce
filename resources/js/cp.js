// Fieldtypes

import GatewayFieldtype from './components/Fieldtypes/GatewayFieldtype.vue';
import MoneyFieldtype from './components/Fieldtypes/MoneyFieldtype.vue'
import ProductVariantFieldtype from './components/Fieldtypes/ProductVariantFieldtype.vue'
import ProductVariantsFildtype from './components/Fieldtypes/ProductVariantsFieldtype.vue'

Statamic.$components.register('gateway-fieldtype', GatewayFieldtype)
Statamic.$components.register('money-fieldtype', MoneyFieldtype)
Statamic.$components.register('product-variant-fieldtype', ProductVariantFieldtype)
Statamic.$components.register('product-variants-fieldtype', ProductVariantsFildtype)

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
Statamic.$components.register('overview-low-stock-products', OverviewLowStockProducts)
Statamic.$components.register('overview-orders-chart', OverviewOrdersChart)
Statamic.$components.register('overview', Overview)
Statamic.$components.register('overview-recent-orders', OverviewRecentOrders)
Statamic.$components.register('overview-top-customers', OverviewTopCustomers)

// Hide 'Collections' active state if Simple Commerce nav item is active

let simpleCommerceCollectionNavItems = Object.values(document.querySelectorAll('.nav-section-simple-commerce li.current')).filter((el) => {
    return el.innerHTML.includes('collections/')
})

if (simpleCommerceCollectionNavItems.length) {
    Object.values(document.querySelectorAll('.nav-section-content li.current'))
        .filter((el) => {
            return el.innerHTML.includes('collections/')
        })
        .forEach((el) => {
            el.classList.remove('current')

            if (el.children.length > 1) {
                el.children[1].remove()
            }
        })
}

// Move 'Simple Commerce' nav section to under 'Content' section (& above 'Fields')
let fieldsSectionHeader = Object.values(document.querySelectorAll('.nav-main-inner h6')).filter((el) => {
    return el.innerText == 'Fields'
})

let simpleCommerceHeading = Object.values(document.querySelectorAll('.nav-main-inner h6')).filter((el) => {
    return el.innerText == 'Simple Commerce'
})

let simpleCommerceSection = document.getElementsByClassName('nav-section-simple-commerce')

if (fieldsSectionHeader.length && simpleCommerceHeading.length && simpleCommerceSection.length) {
    fieldsSectionHeader = fieldsSectionHeader[0]

    simpleCommerceHeading = simpleCommerceHeading[0]
    simpleCommerceSection = simpleCommerceSection[0]

    document.querySelector('.nav-main-inner').insertBefore(simpleCommerceHeading, fieldsSectionHeader)
    document.querySelector('.nav-main-inner').insertBefore(simpleCommerceSection, fieldsSectionHeader)
}