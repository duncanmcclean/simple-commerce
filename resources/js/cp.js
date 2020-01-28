import CommerceCreateForm from "./components/Publish/CommerceCreateForm";
import CustomerOrdersFieldtype from "./components/Fieldtypes/CustomerOrdersFieldtype";
import MoneyFieldtype from "./components/Fieldtypes/MoneyFieldtype";
import OrderStatusSettingsFieldtype from "./components/Fieldtypes/OrderStatusSettingsFieldtype";
import TaxRateSettingsFieldtype from "./components/Fieldtypes/TaxRateSettingsFieldtype";

Statamic.$components.register('commerce-create-form', CommerceCreateForm);
Statamic.$components.register('customer-orders-fieldtype', CustomerOrdersFieldtype);
Statamic.$components.register('money-fieldtype', MoneyFieldtype);
Statamic.$components.register('order-status-settings-fieldtype', OrderStatusSettingsFieldtype);
Statamic.$components.register('tax-rate-settings-fieldtype', TaxRateSettingsFieldtype);
