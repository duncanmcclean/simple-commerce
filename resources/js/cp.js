import CommerceCreateForm from "./components/Publish/CommerceCreateForm";
import CustomerOrdersFieldtype from "./components/Fieldtypes/CustomerOrdersFieldtype";
import MoneyFieldtype from "./components/Fieldtypes/MoneyFieldtype";
import OrderStatus from "./components/Settings/OrderStatus";

Statamic.$components.register('commerce-create-form', CommerceCreateForm);
Statamic.$components.register('customer-orders-fieldtype', CustomerOrdersFieldtype);
Statamic.$components.register('money-fieldtype', MoneyFieldtype);
Statamic.$components.register('order-status-settings', OrderStatus);
