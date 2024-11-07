![Mollie](https://www.mollie.nl/files/Mollie-Logo-Style-Small.png)

# Changelog #
## Changes in release 6.2.4 ##
+ Subscription attribute editing fix
+ Workflow improvements

## Changes in release 6.2.3 ##
+ Multi shop improvements with order states
+ Logging improvements
+ Phone field validation improvements
+ Italian language compatibility

## Changes in release 6.2.2 ##
+ Error handling improvements
+ Mobile phone fix then field is removed
+ Riverty, Trustly, Payconiq payment methods added and GiroPay removed
+ HTTP status
+ Dependencies version bump
+ Improved installation process

## Changes in release 6.2.1 ##
+ Ideal v2 payment method improvement

## Changes in release 6.2.0 ##
+ New payment methods: Bancomat and Alma
+ Apple certificate update
+ Conflicting services fix
+ Bootstrap 5 compatibility improved
+ Maximum fee field
+ Multi carrier for subscription orders added
+ Design improvements in BO
+ Improved subscription creation logic

## Changes in release 6.1.1 ##
+ Updated translations for Dutch, German, English and French languages
+ Added credit card translations for Italian and Spanish languages
+ Fixed issue where fatal exception was thrown in case of ps accounts version being too low
+ Fixed issue where in case of familyName error from mollie 502 was returned instead of 400
+ Fixed issue with credit card iframe not translating errors


## Changes in release 6.1.0 ##
+ New payment methods: Twint, Blik and Klarna
+ Added PrestaShop CloudSync support

## Changes in release 6.0.5 ##
+ Recurring order options are now in "Subscriptions" tab
+ Implemented atomic action protection for Mollie API callbacks
+ Overall improvements and bug fixes

## Changes in release 6.0.4 ##
+ New payment method: Billie.
+ Enabled mixed cart for subscription orders. Various products could be coupled with a single subscription product.
+ Overall improvements and bug fixes.

## Changes in release 6.0.3 ##
+ High priority bug fix impacting product add to cart pop-up.

## Changes in release 6.0.2 ##
+ Decoupled Apple Pay direct feature flag into product and cart separate settings.
+ Overall improvements and bug fixes.

## Changes in release 6.0.1 ##
+ Fixed payment fee tax problems and improved fee set-up process.
+ Sync Mollie components and Single click flag to the environment selected.
+ Overall improvements and bug fixes.

## Changes in release 6.0.0 ##
+ Fixed order state duplication on install when single shop instance/ multishop instances were switched during module usage.
+ Upgraded sentry logger to support PHP 8.0 version. 
+ Dropped dotenv vendor package from module as it is accessible via symfony PrestaShop package.
+ Added subscriptions

## Changes in release 5.4.0 ##
+ Fixed issue where saving wrong API key would reset settings page.
+ Fixed issue where auto shipment would not work on order creation and only on status update.
+ Error logging no longer logs webhook calls. Only logg all logs webhook calls.
+ Fixed component navigation with tab when card is saved.
+ Fixed fee no longer has max amount limitation input.
+ Fixed order number update issue with klarna method.

## Changes in release 5.3.1 ##
+ Improved UI and updated translations.

## Changes in release 5.3.0 ##
+ Removed reference variables from translations.
+ Fixed issue where credit card components would throw error even taught order was created.
+ Changed country restrictions to use invoice and not delivery address when checking for country restrictions.
+ Added PS8.0.0+ compatibility.
+ Changed how secure key is generated to avoid errors.
+ Fixed issue where payment method save with multishop would break another shop payment method settings.
+ Fixed translations that were not working because of php.
+ Added street additional information when creating order.
+ Components now use language locale and not language code when looking for iso code.
+ No longer need to add profile id, now we take it from API before each call.
+ Now all inputs are disabled if API key or environment is changed.
+ Improved refund logic to only change status when refund amount is bigger then 0.
+ Added new chargeback status.
+ Fixed issue where on first settings save the positions would not save.

## Changes in release 5.2.1 ##
+ Fixed issue where orders would have duplicated lines in list if there are more than 1 transaction.
+ Fixed duplicated orders when multiple transactions are created.

## Changes in release 5.2.0 ##
+ Fixed issue where customer id was not added when using saved credit card for order API.
+ Added QR code option for bancontact payment on live mode
+ Added in3 payment method restriction for using only order API.
+ Changed webhook logic to stop tracking sentry errors when expired transaction webhook is called.
+ Phone number exception fix when number is only +
+ Fixed issue with payment fee and price display.

## Changes in release 5.1.0 ##
+ Added order refund statuses for methods that use Order API.
+ Fixed refund and cancel information text in order page.
+ Improved transaction information saving logic to avoid missing information.
+ Added http status codes and exception handle to sentry on webhook calls.
+ Fixed issue where order API would not update status if refunded.
+ Fixed issue where credit card token was not added if user is not saved.
+ No longer try to create second mollie transaction without number if first failed and if it has credit card.
+ Ignore beta releases when showing warning about new version in module.
+ Added fix for custom checkout modules and apple pay payment.

## Changes in release 5.0.1 ##
+ Fixed issue where order API refund would fail on webhook call.

## Changes in release 5.0.0 ##
+ Now we add webhook url on test shop where before if it finds test shop webhook url is not sent.
+ Fixed issue where mollie would fail to create order if address had no post code.
+ Upgraded supported php version from 5.6 to 7.0
+ Upgraded supported prestashop version from 1.6.1 to 1.7
+ Updated how lang locale is taken in checkout. Now it uses locale and not language code.
+ Removed order status list custom filter because we no longer delete our order statuses on module uninstall.
+ Updated how assets are loaded. Now we load js and css only on checkout delivery step, but we load it on DisplayHeader hook and not on ActionFrontControllerSetMedia.
+ Added payment data cleanUp before sending to mollie so that it doesn't have empty or to long inputs.
+ Added validation to check if order is already created just before creating order to avoid creating duplicated orders when webhook gets called multiple times at the same time.
+ Fixed issue where order product price would have wrong price if it didn't use default currency.
+ Added exception catch and added error message for Klarna status error when saving status when Klarna statuses are deleted.
+ Added check that doesn't allow enabling module if php version is not supported.
+ Added credit card single click logic with components.
+ Removed custom logic for local shops.
+ Added sentry validation to avoid crash if sentry breaks down.
+ Added check to avoid exception in path utility class.
+ Using single click payment without component now ask if you want to save it or use saved card.
+ Fixed issue where klarna status would be changed to klarna shipped and not completed when using default statuses for klarna.
+ Fixed issue where modules like VAT module would make mollie create wrong order prices. It was fixed by adding customer to context in webhook.
+ Fixed missing transaction id on order payments.
+ Added validation for descriptions to avoid empty spaces.

## Changes in release 4.4.3 ##
+ Bank transfer now creates order on open status.
+ Fixed issue with klarna shipping status where it was set as completed.
+ Mollie no longer validates new order email to merchant that is sent by another module.
+ Fixed issue where custom payment description wasn't working and always used order reference.
+ Added custom payment number for order API.
+ Added Klarna Pay now.
+ Improved uninstall by deleting all tables except for mollie_payments
+ Fixed few small warning that kept getting sent to sentry
+ Fixed order API all products refund functionality
+ Status is no longer updated again to paid if it already has paid status and webhook is called again.
+ Added max address restriction for mollie. (Max 100 chars)
+ Added more validations for missing API token to handle errors.
+ Fixed open status logic to use selected open status and not awaiting.
+ Removed mail switch for awaiting and open order statuses.
+ Fixed issue where creating mollie order from back office had issues
+ Fixed issue where on some payment methods refund status would not be changed after refund webhook is called
+ Fixed issue where after wrong credit card input the button got disabled.

## Changes in release 4.4.2 ##
+ Fixed payment methods translations using API in checkout
+ Updated credit card components input labels
+ Added Klarna Pay Now check to only use Order API

## Changes in release 4.4.2 ##
+ Fixed issue where order would get cancelled after payment was successful if customer had created another payment for the same cart.
+ Fixed rounding issue when creating mollie payment and there is -0.01 cent difference.
+ No longer display warnings in configuration page about cache and rounding settings.
+ Changed order creation logic for payments without fee. Now we add price that was paid and skip awaiting status. 

## Changes in release 4.4.1 ##
+ Fixed order status change issue where order would change status to completed or shipped.
+ Fixed issue with order refund where order couldn't be refunded if there are some products already refunded.
+ Fixed issue where order was created without cart security key and because of that it would give warning in some cases.
+ When testing module pending status now returns to check out with a new message.
+ Fixed the issue with Bank Transfer. When selecting Bank transfer method, and waiting for bank payment to complete the method, cart will be cleared, so that the customer would not use the same cart until Bank Transfer is finished.

## Changes in release 4.4.0 ##
+ Changed when Prestashop order is created. Now order is only created after transaction is paid.
+ Fixed issue with backorder when last product is bought. It no longer changes status to backorder.
+ Mollie no longer kills other payment methods if mollie API breaks down on checkout page.

## Changes in release 4.3.1 ##
+ Fixed issue when returning to payment methods status of the backorder (not paid) was changed to backorder (paid) instead of canceled. https://github.com/mollie/PrestaShop/issues/349
+ Fixed issue with second chance email sending when form multi shop context
+ Fixed issue with payment country restrictions https://github.com/mollie/PrestaShop/issues/350
+ Fixed issue with mollie order not being created with manual creation in backoffice on PS 1.7.7.* https://github.com/mollie/PrestaShop/issues/361
+ Improved sentry to log all module issues
+ Removed Cartes Bancaires that was never used

# Changelog #
## Changes in release 4.3.0 ##
+ Added PrestaShop multistore compatability - different module configuration settings for each shop configured with multistore. 
+ Added double check for Apple Pay cookie to see if its created - https://github.com/mollie/PrestaShop/issues/324
+ Fixed custom url for carriers when no tracking number is provided 
+ Fixed order "Cancel" button functionality in back office  
+ Fixed duplicated discount rules issue - https://github.com/mollie/PrestaShop/issues/305
+ Fixed mail alert fix with Klarna order - https://github.com/mollie/PrestaShop/issues/316
+ Fixed payment fee calculations in the checkout - https://github.com/mollie/PrestaShop/issues/332

## Changes in release 4.2.4 ##
+ Added optional custom order status for open bank transfer 
+ Added the billing address parameter from Mollie API
+ Added configuration to exclude custom order status creation for Klarna https://github.com/mollie/PrestaShop/issues/296
+ Added general controller for one-page checkout modules https://github.com/mollie/PrestaShop/issues/295
+ Fixed missing currencies in checkout
+ Fixed compatibility with Guzzle v.5 
+ Fixed missing order confirmation page for Klarna payments on PS 1.6
+ Fixed error returned when shop domain is unknown in segment tracker tool
+ Changed “Resend payment link” image and the text in the PS BackOffice
+ Fixed "On backorder (paid)" status when order is paid but out of stock

## Changes in release 4.2.3 ##
+ Fixed translation issues
+ Fixed security issue
+ Fixed return callback issue where order status would get changed again after page refresh
+ Changed currency check to use API calls to see if currency is supported
+ Fixed issue on PS1.7.7 where order emails didn't have product list, because tpl file has changed location
+ Improved module upgrade process
+ Fixed error handle where you would get exception if you api key got expired
+ Improved Mollie order lines rounding and fixed issue where sometimes amount would be different by few cents and that would cause mollie to deny it
+ Fixed issue where order_conf would send wrong order reference

## Changes in release 4.2.2 ##
Issues fixed:
+ Order of payment methods not saved in payment configuration screen
+ Change credit card configuration logo for PS 1.6 version
+ Total_paid_real line added to the database
+ Customer {firstname} {lastname} and {email} not displayed in order_confirmation email
+ Incorrect shop name displayed in the order_confirmation email for multistore configurations
+ Rows of products not displayed in order_confirmation email
+ Apple Pay not available in checkout for PS 1.6 version

NOTE: ING HomePay as payment method is no longer available since 1st of February 2021. More details [here](https://docs.mollie.com/changelog/v2/changelog)

## Changes in release 4.2.1 ##
New features:
+ Improved error message for the test API key validation
+ Made the custom payment title visible in the checkout

Issues fixed:
+ Error when upgrading to 4.2.* version
+ Error invalid order ID: An order ID should start with 'ord_' when changing order statuses 
+ Error mol_country table empty - payment methods were not displayed
+ Slow loading of AdminOrders page
+ Components on 1.6. duplicate step for iDeal and Credit Card
+ Auto-shipment feature

## Changes in release 4.2.0 ##
New features:
+ Created setting for Klarna payment method to configure when the invoice is sent to the consumer
+ Added split transactions ID for vouchers payment method
+ Additional issues monitoring with SENTRY tool
+ Added logic to exclude payment method from checkout if min/max value is not met
+ Added Revolut as new IDEAL issuer
+ Improved translations
+ Added segment tool for analytics
+ Added additional phone number validation
+ Added Belgium to allowed countries for Klarna Pay Later payment method

Issues fixed:
+ Fixed “Test Api” button responds exception error
+ Fixed over refunding for payment methods supporting refunds for an additional €25.00 more than the original payment’s amount
+ Fixed with duplicated on error cart rules
+ Fixed BO errors translations escape
+ Fixed error 500 when submitting order: Unknown offset 0 for collection Order
+ Fixed orders 1 eur on ThirtyBees
+ Fixed “no payment methods” handling

## Changes in release 4.1.3 ##
+ Fixed payment reminder icon in BO orders page

## Changes in release 4.1.2 ##
Added compatibility with PrestaShop 1.7.7 version.
+ Fixed Mollie container issues causing services not to be retrieved.
+ Fixed Admin orders manual creation page to display checkbox when Mollie payment is chosen.
+ Added order total error messages triggered by passing maximum or not reaching minimum order total amounts.
+ Added handling for phone numbers consisting of only 0s.
+ Fixed "credit card" payment option form submission.

## Changes in release 4.1.1 ##
Release scope:
+ Restored the Klarna payment module functionalities for Mollie from the order in backoffice.
+ Fixed MYSQL issues with Mollie select query order listing.
+ Fixed the shippingAddress → organizationName and billingAdress → organizationName error.
+ Fixed the Mollie icon visibility after the module reset.
+ Fixed the module payment options positioning.
+ Added Russian Rubles as a credit card payment option.
+ Added additional translations.

## Changes in release 4.1.0 ##
+ PS1.7 - Credit card components are now indexed with tabbable feature meaning that clicking TAB in keyboard will point to next credit card input option.
+ Fixed Order creation in BO crashed page error.
+ After successful payment process memorized cart is now being removed by default. This behavior is added to ensure merchants wont have duplicated carts then
  they are not required.
+ Added sku fallback for products without names.
+ Updated translations.
+ Added voucher payment method.
+ Phone numbers without possible international code ( which does not have + in front of the number ) are not sent to mollie as delivery or billing address phone.
+ Fixed issue when uninstalling module not all mollie order statuses were deleted

## Changes in release 4.0.9 Hotfix-1 ##
+ In PS1.6 prevents double click payment method to create multiple orders
+ Fixed Order creation in BO crashed page error.

## Changes in release 4.0.9 ##
+ Improved payment settings UI. Now test and live API has separated payment settings so that you can more easily swap between test and live API.
+ Added order status field modifier that hides deleted mollie statuses in Back-office order status tab.
+ New Mollie tab logo in side bar.
+ Improved descriptions in Module configuration tab.
+ Fixed issue with order amount bigger then 1000. Mollie wouldn’t accept amount if it was displayed like 1,000.00. Now we send amount like 1000.00.
+ Fixed issue with one page checkout in PS1.6 where payments are rendered after carrier is selected.
+ Fixed issues with order API when cart has free product as gift.
+ Fixed issue where Module tab in side bar would have no style and logo if module is disabled.

## Changes in release 4.0.8 ##
+ Fixed issue where backorder paid status didn't send order_conf mail.
+ Fixed issue where order status that ended with id 0 would not get saved in mollie settings.
+ Fixed custom shipment information .
+ Refactored payment data creation to use objects instead of arrays.
+ Added single click payment.
+ Added descriptions of API methods.
+ Added API key test button.
+ Improved UX of Payments from visual side.
+ Improved UX of API from visual side.
+ Added module upgrade notice.
+ Added Mollie tab in main tabs.
+ Added custom logo for credit card payment.
+ Improved calculation logic to use Number class.
+ Added selector to control when email alert module can send new_order mail.
+ Fixed issues with refunded orders where sometimes refunded order would have partially refunded status.
+ Fixed issue with gifts and wrapping.

## Changes in release 4.0.7 ##
+ Added new order status “Completed” which is set when Mollie order is finished.
+ Added option for PS1.7 to choose when to send order_conf email to the customer.
+ Added new Mollie_payment email template that has Mollie payment link.
+ Added “Second chance email button” in admin order list. Orders with Mollie payment will have option which allows merchant to send Mollie_payment email to the customer where he can finish his order payment.
+ Added option to create order with Mollie payment from back office and send Mollie_payment mail to customer where he can finish his order.
+ Added logic where cart is saved for customer if he returns back to checkout from Mollie payment page (due to possible security vulnerabilities we removed voucher restoration if such have been added to cart).
+ Added missing translations.
+ Added functionality where merchant can exclude countries if all countries are selected for payment option.
+ Changed locale settings and fixed issue where webshop locale was not sent to mollie if option is selected.
+ Improved settings descriptions.
+ Fixed issues with credit card and order API.
+ Improved UX of Payments Enabling/Disabling function from visual side.
+ Shipping order status fixed.

## Changes in release 4.0.6 ##
+ Webhook call fail issue error 500.
+ Status duplication when payment is paid or canceled.
+ Payment method in pdf file when order is paid.
+ Return from payment screen stuck.
+ Vendor issue where in some cases it would throw error because another vendor file already has random_bytes function.
+ Tax excluded orders where the calculation were off if prices are displayed without taxes.
+ Below features added: Switch for PS1.7 that allows to choose if you want to send order_conf mail which is sent before payment is completed. Hidden order_conf mail switch for ps1.6 because it can only be disabled in core.

## Changes in release 4.0.5 ##
+ Improved payment method description in settings.
+ Fixed order transaction id when payment is accepted.
+ Removed duplicated order validation on webhook call.
+ Fixed some namespace issues.

## Changes in release 4.0.4 ##
+ Fixed issue "Automatically ship when one of these statuses is reached" wasn't working correctly.
+ Disabled order_conf email for PS1.7. To disable it for PS1.6 you need to change PS core. classes/PaymentModule.php. you should comment out line 271:
Mail::Send(intval($order->id_lang), 'order_conf', 'Order confirmation', $data, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, $fileAttachment);
+ Added fix for PS1.6 hookDisplayPaymentEU where it was using wrong method ID.
+ Added fix where saving API Key would reset default order statuses.

## Changes in release 4.0.3 ##
+ Fixed upgrade cache issue where upgrading module would only work after second time.
+ Fixed guzzle conflict with other modules that have guzzle in a vendor.

## Changes in release 4.0.2 ##
+ Added index.php files.
+ Added missing license and updated the old ones.
+ Added switch to send an email when payment is accepted.

## Changes in release 4.0.0 ##
+ Fixed double order status bug - Now Order is created before payment
+ Fixed Payment Accepted email bug
+ Fixed PHP bug where sometimes the total price is incorrect
+ New UI - Created new payment methods form in General settings
+ New Order payment Fee
+ Added payment Fee to PDF Invoice if param is added
+ Now fully compatible only from PrestaShop 1.6.1.x PHP 5.6
+ Refactored shipment settings from React to jQuery and PHP
+ Removed override validation
+ Fixed order status bug when order status is not changed to a specific status that is selected in Back-office
+ Fixed email receiving bugs
+ Prestashop 1.6 error fixes and small changes
+ Fixed bugs in One Page checkout and other Prestashop 1.6 bugs

## Changes in release 3.5.5 ##
+ Updated licenses
+ Fixed bug with front office set media hook
+ Order status bug fix

## Changes in release 3.5.4 ##
+ Fixed issue where in some themes JS and CSS wouldn't load in front office.
+ Fixed issue where profile always used test mode API.
+ Fixed email issue where email wouldn't add invoice on back-order.

## Changes in release 3.5.3 ##
+ Fixed shipment information save and auto fill when shipping from admin order page.
+ Fixed auto shipment on selected statuses.
+ Removed possibility to display payments own page.
+ Added success messages in admin order page for cancel, refund and shipment options.
+ Improved error messages in front office.
+ Improved translations.
+ Improved credit card inputs to no longer click on place holder when trying to use input on mobile.
+ Improved Mollie settings to be more user friendly.

## Changes in release 3.5.2 ##
+ Added Mollie Credit cards Components for PrestaShop1.6.1.*
+ Fixed wrong API key error handle.
+ Improved cards Components display for phones.
+ Fixed bug where shipment information URL wouldn't get saved.
+ Removed wrong configurations on 3.5.2 upgrade.
+ Added warning messages if required inputs are empty or invalid.
+ Fixed invoice with price without tax.

## Changes in release 3.5.0 ##
+ Added Mollie Components for Credit cards for PrestaShop1.7.*
+ Fixed order statuses duplication.

## Changes in release 3.4.7 ##
+ Fixed tax issue when products price is displayed without tax.
+ Removed CarteSi payment method.
+ Fixed order refund, cancel and shipment in admin order page.
+ Fixed some minor issues.

## Changes in release 3.4.6 ##
+ Fixed out of stock order status issue.
+ Fixed price rounding issues.
+ Fixed Mollie email sender bug with translations.
+ Custom css only applied on front office.
+ Apple pay is only available if SSL is enabled.

## Changes in release 3.4.5 ##
+ Added description validation in BO.
+ Fixed install and uninstall issues.
+ Fixed error when opening module for the fist time after installing Mollie.
+ Added configuration deletion when uninstalling module.

## Changes in release 3.4.4 ##
+ Fixed custom CSS in advanced settings tab.
+ Fixed issue where MOLLIE_METHODS_CONFIG sometimes would get too big for database.
+ Fixed upgrade issues.
+ Fixed countries restriction issue on PS1.6 version.

## Changes in release 3.4.3 ##
+ Added country selector for each method in back-office for PS1.6-PS1.7 versions.
+ Fixed publicPath error.
+ Added new Mollie logo in back-office.

## Changes in release 3.4.2 ##
+ Fixed orders status not set in admin order page.
+ Fixed bug where setting API key would reset Mollie configurations.
+ Added MyBank payment method.

## Changes in release 3.4.1 ##
+ Fixed wrong namespace when calling vendor classes.
+ Fixed issue with admin order page JS global values.
+ Fixed bug where PayPal with payment API would trow error in admin order page.

## Changes in release 3.4.0 ##
+ Settings splitted to General settings and advanced settings tabs.
+ Added apple pay method.
+ Payment API no longer needs billing and shipping addresses.
+ Updated FR translations.
+ Fixed statuses in back-office order page.
+ Added backorder statuses for orders with out of stock products.
+ Fixed issue when ordering without taxes.
+ Moved Html code out of PHP classes.
+ Changed namespace prefix when calling vendor functions.

## Changes in release 3.3.5 ##
+ [Orders API] Support both discounts + rounding method conversions
+ [Orders API] Support rounding differences caused by discounts


## Changes in release 3.3.4 ##
+ Override default bankwire template mail variables
+ Disable displayPaymentEU hook on 1.7 (redundant for ps_legalcompliance module)
+ Prevent double Mollie order states on (re)install
+ [Payments API] Defer QR code initialization until visible
+ [Orders API] Add support for multiple payments per order
+ [Orders API] Allow more carrier configurations for auto shipping

## Changes in release 3.3.3 ##
+ Debounce payment status check on return page
+ Restore mod_php support
+ [Orders API] Add support for cumulative specific price rules


## Changes in release 3.3.2 ##
+ Improved cache handling
+ Bypass webhooks for localhost testing
+ Postcode no longer required
+ Wait on the return page for the order status to become either paid/canceled/etc. (anything but created/pending or open when method != banktransfer)
+ [Orders API] Avoid listing free items on the order product list
+ [Orders API] Fix order reference
+ [Orders API] Fix sending mails
+ [Orders API] Improved order rounding handling


## Changes in release 3.3.1 ##
+ Make Klarna Pay later. and Klarna Slice it. translatable in checkout
+ (Payments API) Add {order.reference} example to description field


## Changes in release 3.3.0 ##
+ New payment method: Klarna (switch the module to the Orders API to unlock this payment method)
+ Add support for the Orders API
+ SVG payment method icons
+ Add a fallback (use attached cacert.pem) in case the root certificates are missing from the system, so a connection with the Mollie API can still be made
+ Completely detach from the Mollie API on the configuration page and checkout; this should improve the checkout speed as well as prevent it from taking down the entire checkout during an outage.
+ (Orders API) Ship, cancel and or refund order/order lines straight from the back office
+ (Orders API) Add automatic shipment tracking
+ (Payments API) You can now add {order.reference} to the payment description.
+ (Payments API) Add support for full and partial refunds from the back office


## Changes in release 3.2.0 ##
+ Code and style changes for Addons validation

## Changes in release 3.1.0 ##
+ Compatible with PrestaShop 1.7.4
+ Separate listings for Cartes Bancaires + CartaSi
+ More locales supported
+ Fix eps/iDEAL return pages
+ Fix default PrestaShop order statuses for Mollie paid and open statuses
+ Fix shipping address passed to Mollie
+ Fix QR Codes window height check

## Changes in release 3.0.2 ##
+ Fix for permanent error reporting
+ Fix built-in module updater on PS 1.7

## Changes in release 3.0.1 ##
+ Fix for incorrect rounding of amounts > € 1.000,00 in PrestaShop 1.7

## Changes in release 3.0.0 ##
+ Added iDEAL QR Codes
+ Added auto upgrader
+ Added sortable payment methods
+ Added Mollie multi-currency
+ Fix for Issue #53 : Change "ps_orders.payment" after customer changes payment method
+ Fix for Issue #52 : Make the 'order canceled' function optional

## Changes in release 2.0.6 ##
+ Fix for Issue #54 : Error executing API call when "Send customer credentials to Mollie" is enabled

Note: To prevent confusion for those who are used to Mollie showing TBM Bank when doing a iDEAL test payment. All bank options are showing instead of TBM Bank from now on. Select a bank to make a test payment and you will be redirected to the Mollie test payment page.

## Changes in release 2.0.5 ##
+ Fix for Issue #50: 'Send webshop locale' setting results in redirect to homepage

## Changes in release 2.0.4 ##
+ fix for Issue #45 - adjusted hookDisplayAdminOrder() method

## Changes in release 2.0.3 ##
+ fix for Issue #44 - added return.tpl for Prestashop 1.7.x

## Changes in release 2.0.2 ##
+ fix for ING Home Pay

## Changes in release 2.0.1 ##
+ Compatibility with Prestashop 1.7.3.0 final

## Changes in release 2.0.0 ##
+ Compatibility with Prestashop 1.7+
+ Fix for "Frontcontroller::init - cart cannot be loaded".
+ Fix for recieving payment multiple times.
+ Added option to send customer order to Mollie metadata
+ Mollie now works in maintenance mode.
+ Improved configuration page.
+ Improved User Experience.

## Changes in release 1.3.0 ##
+ Automatically pass along the customer's email address for banktransfer payment instructions, when available.

## Changes in release 1.2.6 ##
+ Fixed incorrect install/de-install hooks.
+ Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.5.1`

## Changes in release 1.2.5 ##
+ Added some new translations.
+ EU Compliance module has been added.
+ Update submodule mollie-api-php to version 1.4.0.

## Changes in release 1.2.4 ##
+ Added payment description filters ({cart.id} {customer.firstname} {customer.lastname} {customer.company})
+ Update submodule mollie-api-php to version 1.3.3

## Changes in release 1.2.3 ##
+ Set default language for admin when no language set
+ Update submodule mollie-api-php to version 1.3.1

## Changes in release 1.2.2 ##
+ Fixed issue where banktransfer status didn't get updated correctly
+ Update submodule mollie-api-php to version 1.2.8

## Changes in release 1.2.1 ##
+ Fixed an issue where the country context is not set when Mollie calls the PrestaShop webhook.
+ Fixed an issue where the iDEAL issuers are not displayed correctly when using module OnePageCheckout.
+ Update submodule mollie-api-php to version 1.2.7

## Changes in release 1.2.0 ##
+ Add option to send webshop locale to Mollie to use in payment screens. This will use the
current language and country locale (In admin: Localization -> Advanced)

## Changes in release 1.1.0 ##
+ Add English and German translations
+ Save Mollie payment id (tr_xxxx) in order payment
+ Update Mollie API client to version 1.2.5, contains system compatibility checker

## Changes in release 1.0.8 ##
+ Create the order before the payment is finished when payment method is banktransfer.

## Changes in release 1.0.7 ##
+ Stop Google Analytics from listing payment provider as referrer.

## Changes in release 1.0.6 ##
+ Fixed redirect issue for cancelled payments.

## Changes in release 1.0.5 ##
+ Fixed currency issue on return page.

## Changes in release 1.0.4 ##
+ Added cart ID to payment descriptions.
+ Fixed missing keys warning.

## Changes in release 1.0.3 ##
+ Fixed error in payment method selection.
+ Prevent customers from returning to an empty cart after cancelling a payment.
+ Fixed issues with Google Analytics by using Prestashop's default return page.

## Changes in release 1.0.2 ##
+ Added 'open' order status.
+ Fixed bug where order updated twice.
+ Fixed iDEAL bank list not showing.

## Changes in release 1.0.1 ##
+ Fixed issue with custom statuses for open payments.

## Changes in release 1.0.0 ##
+ Initial release.
