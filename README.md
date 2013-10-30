# SheerID Magento Plugin

A plugin for [Magento eCommerce Platform](http://www.magentocommerce.com/) which allows merchants to easily integrate with [SheerID](http://sheerid.com)'s instant online verification API, allowing your online store to offer qualified discounts and offers to customers with special status such as:

* Students
* Active-duty military
* Non-profits

## Components

* **One-Page Checkout** - One-page checkout support allows customers to verify as part of the checkout process.  When this feature is enabled, customers will be prompted to provide qualification information as part of the standard checkout flow.
* **Verification Widget** - The verification widget can be dropped into your layout configuration to allow instant on-page verification from any page within your Magento site.
* **Verification Block** - The custom verification block allows you to easily add a verification form to any CMS page or static block.  Great for verifying customers on a targeted landing page.

* Coming soon:
 * Catalog items requiring verification - require customers to verify before purchasing specific items from your catalog.
 * Verified account registration - verify during the registration process.

## Installation

1. Note: Make sure that you initialize the git submodules or otherwise ensure the SheerID PHP library is present in app/code/SheerID/Verify/lib/SheerID
1. Move the app/code/SheerID directory into $MAGENTO_HOME/app/code/community
1. Move etc/SheerID_All.xml into $MAGENTO_HOME/app/etc/modules
1. Move files from app/design/frontend/default/default into the corresponding location based on your current theme setting.
1. In the Magento admin, navigate to System > Configuration > SheerID Settings > SheerID and set a valid access token.  Access tokens can be obtained from the [SheerID Control Center](https://www.sheerid.com/home/).

_**Note:** It may be preferable to symlink module files/directories rather than moving them if your Magento configuration supports it._

## Configuration

### Admin Configuration

* **SheerID Access Token** - The token which allows your system to access the SheerID API.  Obtained from the [SheerID Control Center](https://www.sheerid.com/home/).
* **Sandbox Mode?** - If Sandbox Mode is enabled, your store will connect to the SheerID Sandbox API which facilitates integration testing.  For more information, please view the [SheerID Developer Center](http://developer.sheerid.com).
* **Verify in Checkout?** - Determine whether to prompt for verification during the checkout process.  If a customer has already verified, they won't be prompted to verify again regardless of this setting.  If Cookie-Dependent, you will be prompted to specify the cookie name.
* **Verify Cookie Name** - If Verify in Checkout is set to Cookie-Dependent, this is the name of the cookie which can be set to toggle the verification.  The value of the cookie should be a comma-delimited list of affiliation types which are to be verified.

### Verify Block Configuration

The verification block can be added to the content of a CMS page or static block via the following template tag:

```
{{block type='sheerid/verify'}}
```

To further qualify the type of verification which is to be performed by this block, supply a comma-delimited list of affiliation types with the `affiliation_types` attribute:

```
{{block type='sheerid/verify' affiliation_types='STUDENT_FULL_TIME,STUDENT_PART_TIME'}}
```

### Promotions Configuration

In order to correlate promotions in your store with verifications, you must create Shopping Cart Price Rules within the Magento Admin (Promotions > Shopping Cart Price Rules). In the "Conditions" tab for a new or existing price rule, create one or more conditions using the SheerID Verified Affiliation Status (Cart Attribute).  Set the affiliation status to one of the options from the pick-list.  Make sure to complete the Actions tab to define what type of offer is applied.

For example, to restrict your offer to verified active-duty military members, you would specify:
SheerID Verified Affiliation Status is ACTIVE_DUTY

Then, if a customer successfully completes the verification form with an affiliation type of ACTIVE_DUTY, this offer will be applied to the cart.

### Verify Widget Configuration

This plugin also exposes a Verify Widget, which is a component that offers a link to verify, which when clicked renders a verification form.  This was primarily intended to be displayed on the shopping cart page, but could really be placed anywhere in your theme.  The steps below describe how this widget can be added to your cart page.

First, edit `$MAGENTO_HOME/app/design/frontend/base/default/layout/checkout.xml` (note: actual path may vary depending on your active theme).

At the location in this file represented by the XPath selector: `/layout/checkout_cart_index/reference[@name='content']/block[@type='checkout/cart'])`, add:

````
<block type="sheerid/widget" name="sheerid.verifywidget" as="verifywidget">
	<action method="setData"><name>title</name><value>Student Discount</value></action>
	<action method="setData"><name>affiliation_types</name><value>STUDENT_FULL_TIME,STUDENT_PART_TIME</value></action>
</block>
````

Next, edit `$MAGENTO_HOME/app/design/frontend/base/default/template/checkout/cart.phtml` (note: actual path may vary depending on your active theme).

Add the following PHP snippet in the location where you would like the Verify Widget to appear:
<?php echo $this->getChildHtml('verifywidget') ?>

Alternatively, the Verify Widget may be included using the template tag syntax shown in the "Verify Block Configuration" section, example:

````
{{block type='sheerid/verifywidget' affiliation_types='STUDENT_FULL_TIME,STUDENT_PART_TIME' title='Student Discount'}}
````

#### Verify Widget Settings

The Verify Widget's behavior and content can be modified by setting block attributes.  Its configuration options include:

 * `is_conditional` - should the widget be hidden once the user has been verified? (default: `true`)
 * `title` - the title of the widget
 * `description` - additional text promoting the offer/verification, displayed above the "Click here to verify" link.
 * `affiliation_types` - a comma-delimited list of affiliations to use for the verification form 