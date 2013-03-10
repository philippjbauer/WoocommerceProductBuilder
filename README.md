WooCommerce Product Builder -- Let customers build their products!
==================================================================

## Early version warning!

The WooCommerce Product Builder is still work in progress.
This commit is still a pre-release version and not recommended for production!

## Description

The WooCommerce Product Builder (WCPB) is a handy WooCommerce plugin that lets the customer build their own products.

What are the goals of the WCPB?

* Let customers build their own products
* Build upon a base option (e.g. different shapes of chocolate)
* Add different options to said base (e.g. add candies, nuts, etc. to the chosen chocolate base)
* Integrate with WooCommerce (Product Management, Cart, Orders, Reports)

## System Requirements

The plugin was build and tested with following versions of Wordpress and WooCommerce:

* Wordpress 3.5.1
* WooCommerce 1.6.6

Please report any conflicts with other versions or plugins.
Please report if other versions of WP and WooCommerce work as well. Thanks!

## Installation

WCPB depends on an installed WooCommerce!

Installs like every other ordinary Wordpress plugin.
Put files into wp-content/plugins/ and activate in the backend.

## Usage

Important: Since this plugin is still work in progress, certain things may change.

### 1. Create parent product category
The WCPB depends on a parent product category that is chosen in the WCPB Settings.
The user build product will be added to this category as soon as the user puts it into the cart.

### 2. Create child categories
The child categories hold the actual product options that the customer can choose to add to the product.
The categories will be used for tabs in the frontend. You can customize the tab-names in the WCPB Settings.

### 3. Setup
In the WCPB Settings, first choose your parent category and save it. The child categories of this category 
will appear for further configuration. After you chose the parent catgory choose how many options can be chosen
by the user. You can also define how many options of a category can be chosen. If you have a base-product you will
want to set a maximum of 1 for example. You can also define custom tab-names that will appear in the frontend.