# activate-woocommerce-webhooks

A program that checks if any WooCommerce webhooks have been disbaled and automatically renables them

I built this because we were relying on WooCommerce webhooks to update third party platforms. Theses webhooks would often go down, leading to thrid party systems becoming out of sync.

## Infomation

The program checks the status of all WooCommerce webhooks. If any are set to disabled it automatically activates them.

It uses 2 methods to check
1. A cronjob which runs every 60 seconds. You must make sure that WP Cron is not disabled for this to work
2. Everytime someone visits the frontend of the site

## Installation

Download the php file. Zip it, install as you would any other plugin in Wordpress and voila!
