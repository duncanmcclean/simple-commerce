// Fieldtypes

import MoneyFieldtype from './components/Fieldtypes/MoneyFieldtype.vue'
import ProductVariantFieldtype from './components/Fieldtypes/ProductVariantFieldtype.vue'
import ProductVariantsFildtype from './components/Fieldtypes/ProductVariantsFieldtype.vue'

Statamic.$components.register('money-fieldtype', MoneyFieldtype)
Statamic.$components.register('product-variant-fieldtype', ProductVariantFieldtype)
Statamic.$components.register('product-variants-fieldtype', ProductVariantsFildtype)

// Inputs

import RegionSelector from './components/Inputs/RegionSelector.vue'

Statamic.$components.register('region-selector', RegionSelector)

// Widgets

import SalesWidget from './components/Widgets/SalesWidget.vue'

Statamic.$components.register('sales-widget', SalesWidget)
