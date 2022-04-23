---
title: Version Control Strategies
---

One of the great things about Statamic is that everything is flat files which means you can put all your content in version control.

Having everything in version control is great for your content, you can see who changed things, when they changed it and you can rollback any accidents. Perfect for development!

However, when you're using Simple Commerce, everything is in flat-files, including your orders and customer information.

Depends on your setup, you may not want Statamic's Git integration to be committing everytime someone adds an item to their cart or makes a purchase. You could end up with a clutered Git history very quickly. There's a few solutions to this problem:

## 1. Use a different repo for your orders and customers

If you want to continue using version control but don't want orders and customers cluttering up your main repo, you could always setup a seperate Git repository where your orders and customers would live.

However, this solution is a bit fiddly to get setup with!

## 2. Don't use version control at all for orders and customers, just back them up instead

If you don't want orders and customer sitting in version control at all, you can just ignore them in your site's `.gitignore` file.

```bash
content/collections/orders/*.md
content/collections/customers/*.md
```

Remember to backup your orders & collections though, especially now that they're not in version control. You could use something like [file backups on Snapshooter](https://snapshooter.io) to back them up on a regular basis.

## 3. Use a traditional database

The third option would be to use a traditional, old-school database.

Simple Commerce [makes it easy](/database-orders) to push your Orders & Customers into a database, leaving your Products & Coupons as flat-files.
