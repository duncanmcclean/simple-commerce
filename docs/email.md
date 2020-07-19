---
title: Email
parent: c4d878eb-af7d-47e7-bfc8-c5baa162d7bf
updated_by: 651d06a4-b013-467f-a19a-b4d38b6209a6
updated_at: 1595078000
id: 22499319-3c9f-4546-b792-4054d47d57fd
is_documentation: true
nav_order: 7
---
Simple Commerce automatically sends emails when things like orders are created or the status on a customer's order is updated.

## Emails
Here's a list of the emails Simple Commerce sends for you.

* Order confirmation

## Customising emails
Simple Commerce uses [Laravel's markdown mail](https://laravel.com/docs/7.x/mail#markdown-mailables) feature, meaning we can use Blade views with markdown in them and it will be sent as an email.

If you'd like to customise the text used, Simple Commerce automatically publishes them to your `resources/views/vendor/simple-commerce`.