=== QuotePress - Quote Estimate ===
Contributors: quotepress, freemius
Tags: quote estimate, quote, estimate, store, shop, cart, billing, payment, ecommerce, sales, sell, checkout, quotepress
Requires at least: 4.9
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The Ultimate WordPress Quote Plugin - Setup a store for your products & services and get quotes.

== Description ==

Setup a WordPress store for your products & services, customers request quotes which you can review, add pricing and send to customer for payment.

Perfect for selling services, handmade products, customizable products, products with complex pricing structures and more.

== Watch Demo ==

[youtube https://www.youtube.com/watch?v=jV7lvKW4Oe4]

== Awesome Features ==

- Request Quote Functionality
- Create products and categories
- Add product attributes and variations
- Quote Management System
- Automatically email notifications to store owner/customer
- Quote Payments by Bank Transfer, Check, Stripe & PayPal
- Filter products by price and variations
- Sort products by name, date and featured
- Product Image Lightbox Gallery
- Several widgets to display cart, categories, filters
- AJAX Cart Facility
- Customer Registration System
- Customer Account Area with Quote History

== Customizable and extendable ==

- Works with majority of WordPress themes
- Custom category and product pages templates
- Custom email notification templates
- Various filters and hooks

== Getting Started ==

Upon activation of the plugin you will see a notice, click the link and follow the guide to setting up your store which covers creating pages, theme integration and more.

== Installation ==

= Minimum Requirements =

* PHP 7.2 or greater is recommended
* MySQL 5.6 or greater is recommended

It's very likely your hosting will meet these requirements, but if you are unsure ask your web hosting provider.

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of QuotePress, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”
 
In the search field type “QuotePress,” then click “Search Plugins”. Click “Install Now,” and WordPress will take it from there.

= Manual installation =

Manual installation method requires you to download the QuotePress plugin and upload it to the WordPress plugin directory on your web server via your favorite FTP application. Once done activate the plugin through the Plugins area.

= Updating =

Automatic updates should work smoothly, but we still recommend you back up your site.

If you encounter issues with the shop/category pages after an update, flush the permalinks by going to WordPress > Settings > Permalinks and hitting “Save”.

== Follow Us ==

For the latest news, updates and tips on using QuotePress follow our [Twitter](https://twitter.com/quotepresswp) and [YouTube](https://www.youtube.com/channel/UCxCUExLksISFPFW2NhpRMOA).

== Frequently Asked Questions ==

= Can I customize the page and email templates? =

Yes, to do so copy the templates to your theme folder and edit there. For further details view Settings > Setup.

= Are there any shortcodes? =

You can use the following shortcodes to render QuotePress content within your website, when automatically creating QuotePress pages during setup pages will be created which contain some of these shortcodes:

- [qpr_account]
- [qpr_cart]
- [qpr_checkout]
- [qpr_checkout_confirmation]
- [qpr_login]
- [qpr_lost_password]
- [qpr_mini_cart]
- [qpr_password_reset]
- [qpr_register]

= Is there a store page? =

Yes you can access this using yourdomain.com/store. We recommend adding some widgets to the sidebar so this page can easily be sorted, filtered and to allow navigation to product categories.

= Does it set any cookies? =

Yes, to ensure the cart functionality works QuotePress sets a qpr_session cookie. This remembers the user and their cart contents for a limited time. The expiry time of this cookie can be amended in settings.

= How does it work with my user roles? =

Any users already with an account will keep their existing role - if they order via the website or update their profile in their account they will be assigned the customer role in addition to their existing role.

Any new users signing up via the registration page will be assigned as customers from the start.

= Can users checkout without an account? =

No, user registration is required and ensures that customers have access to all their previous quotes, can request new quotes without entering their details again and can make payment. Due to the requirement for user registration by activating the plugin the WordPress customer registration option will be enabled.

= If customers request a quote why set prices? =

Adding price data to products is optional but it saves time when processing quotes as you can estimate the contents of the quote from the prices you have set on products and variations.

= How can I change the no image placeholder? =

Use the qpr_no_image_src_thumbnail and qpr_no_image_src_full to filter the image.

= Can I use page caching? =

We do not recommend using page caching with QuotePress, we may actively support page caching in future, but not right now. Feel free to try and let us know of any issues you encounter.

= Does it work with WooCommerce? =

No - QuotePress is a standalone plugin, it works similar to WooCommerce, but shouldn't be used alongside it, they do similar things, WooCommerce is designed to allow users to place orders immediately, QuotePress allows users to request a quote for a particular product, receive the quote and make payment after review.

= Product pages/categories don't work? =

Try going to Settings > Permalinks and saving the page, this will regenerate WordPress's rewrite rules, some other plugins may override QuotePress rewrites, this should ensure they are working.

= Is it compatible with my theme? =

QuotePress is designed to be compatible with most WordPress themes, some themes may require you to add your theme's wrapper markup around some elements if you notice sidebars or other elements displaying in incorrect positions. For further details see Settings > Setup.

== Screenshots ==

1. Products
2. Products Admin
3. Product
4. Product Admin
5. Quotes Admin
6. Quote Admin
7. Account

== Changelog ==

= 1.1.3 - 2019-10-12 =
* Added Freemius integration
* Removed qpr_active_extensions function
* Removed extensions functionality, extension functionality now available as QuotePress Pro via Freemius
* Readme updated

= 1.1.2 - 2019-10-06 =
* Added color selection for email notifications to settings page, if you have previously used theme override templates for files in the emails folder we recommend merging these changes into your templates
* Added pricing display to products section
* Added enqueue of wp-color-picker to settings page
* Added settings specific admin js
* Added display SKUs in cart setting
* Added descriptions to various setting fields
* Edit of cart contents, removal of SKU and Variation columns, this is now displayed underneath the product title
* Edit of activation conditions to ensure existing data is kept on reactivation
* Fixed broken links to extensions in settings tabs
* Readme updated

= 1.1.1 - 2019-10-05 =
* Edit of email templates, full redesign of all email templates, if you have previously used theme override templates for files in the emails folder we recommend merging these changes into your templates
* Edit of qpr-notice link styling
* Added responsive-html-email-template library
* Added notices of sent quotes to account page so customers can easily navigate to quotes for review/payment
* Readme updated

= 1.1.0 - 2019-09-29 =
* Added pending quote indicator to dashboard menu
* Added code tags to relevant information in Settings > Setup tab
* Fixed template overrides can't get no image placeholder, if you have previously used theme override templates we recommend merging these changes into your templates
* Fixed non-static method QPR_Settings::page() called statically
* Fixed undefined search index notices on variations page
* Fixed non-static method QPR_Variations::get_term_combinations() called statically
* Fixed non-static method QPR_Variations::get_product_variations() called statically
* Fixed non-static method QPR_Variations::page() called statically
* Fixed non-static method QPR_Attributes::page() called statically
* Fixed non-static method QPR_Payments::payment_options() called statically
* Fixed non-static method QPR_Payments::payment_option_settings() called statically
* Fixed non-static method QPR_Customers::customer_link() called statically
* Fixed QPR_NAME warning related to extensions
* Readme updated

= 1.0.3 - 2019-09-27 =
* Fixed test alert shows when estimating pricing/tax
* Fixed tax profile percent issue, if you have issues with estimated tax try resaving or recreating tax profiles
* Readme updated

= 1.0.2 - 2019-09-22 =
* Added lightbox gallery to product images
* Added qpr_is_product function
* Added product page specific JS (public-product.js)
* Edit of website/author URLs
* Edit of archive.php template translation strings
* Readme updated

= 1.0.1 - 2019-09-16 =
* Added missing product title on product page
* Edit of qpr_date_format function to use WordPress setting with fallback if options not populated
* Edit of postcode labels to include zip
* Edit of review notice button styling
* Edit of pagination labels on archive.php template
* Fixed broken link on settings payment tab
* Readme updated

= 1.0.0 - 2019-09-14 =
* Added paid email notifications
* Added qpr_active_extensions function
* Added extension info and license management to extensions tab
* Added qpr_is_checkout function
* Added missing classes to multiple button elements
* Edit of qpr_is_store_page and qpr_is_account_page function names to remove _page suffix
* Edit of payment functionality and settings to allow extensions to be included
* Edit of qpr_get_currency_symbol to use country code as fallback if no symbol
* Edit of create pages description in settings
* Fixed account page gets renamed to a quote ID upon payment
* Fixed navigating quotes pagination on account page
* Fixed selecting United Kingdom as country displays as Northern Ireland due to both using GB country code
* Readme updated

= 0.0.3 - 2019-09-07 =
* Added option to update address on account for next time on checkout
* Added customer link function
* Added QPR_Customers class
* Added SKU display to mini cart
* Added add to cart redirect option
* Added review notice
* Edit of quote-received pages and qpr_quote_received shortcode to checkout-confirmation and qpr_checkout_confirmation
* Edit of qpr_login_form, qpr_lost_password_form, qpr_password_reset_form, qpr_register_form shortcodes to remove _form suffix
* Edit of customer data display on quote listings and quote edit
* Edit of title column label on quote listings to ID
* Edit of product template text content
* Fixed non variation products have spacing on mini cart
* Fixed non variation products don't get SKU attached on add to cart
* Fixed assign new quote to customer field not finding customers
* Fixed WordPress dashboard user profile customer edit country field not a select field
* Updated WordPress.org assets
* Removed WordPress default slug meta box from quote edit
* Removed quote meta boxes from screen options which should persist
* Note if using version less than 0.0.3 you should recreate pages via Settings > Create pages or update old shortcodes and URLs based on specified changes above
* Readme updated

= 0.0.2 - 2019-09-05 =
* Added product price entry information
* Edit to variation fields when adding products to a quote
* Readme updated

= 0.0.1 - 2019-09-04 =
* Initial release