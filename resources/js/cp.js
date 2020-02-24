import CommerceCreateForm from "./components/Publish/CommerceCreateForm";
import CustomerOrdersFieldtype from "./components/Fieldtypes/CustomerOrdersFieldtype";
import MoneyFieldtype from "./components/Fieldtypes/MoneyFieldtype";
import OrderItemsFieldtype from "./components/Fieldtypes/OrderItemsFieldtype";
import OrderStatus from "./components/Settings/OrderStatus";
import ShippingZone from "./components/Settings/ShippingZone";
import TaxRate from "./components/Settings/TaxRate";

Statamic.$components.register('commerce-create-form', CommerceCreateForm);
Statamic.$components.register('customer-orders-fieldtype', CustomerOrdersFieldtype);
Statamic.$components.register('money-fieldtype', MoneyFieldtype);
Statamic.$components.register('order-items-fieldtype', OrderItemsFieldtype);
Statamic.$components.register('order-status-settings', OrderStatus);
Statamic.$components.register('shipping-zone-settings', ShippingZone);
Statamic.$components.register('tax-rate-settings', TaxRate);
