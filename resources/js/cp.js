import CommerceCreateForm from "./components/CommerceCreateForm";
import CustomerOrdersFieldtype from "./components/CustomerOrdersFieldtype";
import MoneyFieldtype from "./components/MoneyFieldtype";
import OrderStatusSettingsFieldtype from "./components/OrderStatusSettingsFieldtype";

Statamic.$components.register('commerce-create-form', CommerceCreateForm);
Statamic.$components.register('customer-orders-fieldtype', CustomerOrdersFieldtype);
Statamic.$components.register('money-fieldtype', MoneyFieldtype);
Statamic.$components.register('order-status-settings-fieldtype', OrderStatusSettingsFieldtype);
