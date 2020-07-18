# Receipts

Simple Commerce automatically generates PDF invoices for each successful order. These receipts emailed to the user as an attachment to any order related emails.

## Storage

By default Simple Commerce will store the PDFs it generates in your application's `public` filesystem. However, if you wish to change it, just change the value of `receipt_filesystem` in `config/simple-commerce.php` to the name of one of your filesystems.

## Customising the receipt

Sometimes, you'll want to customise the receipt to change the layout or add your store's logo. To do this, you'll need to publish the receipt by running `TODO`.
