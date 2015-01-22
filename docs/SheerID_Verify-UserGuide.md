# SheerID Verify Extension

A plugin for [Magento eCommerce Platform](http://www.magentocommerce.com/) which allows merchants to easily integrate with [SheerID](http://www.sheerid.com)'s eligibility verification API, allowing your online store to offer qualified promotions and sell items restricted to customers with special status such as:

* Students
* Teachers
* Active duty military, veterans, reservists/guardsmen and their family members
* First responders
* Non-profits

For more information about SheerID, visit [SheerID.com](http://www.sheerid.com)

## Features

The SheerID Verify extension adds the following features to your Magento store(s):

### Built-in SheerID Verification

All of the techniques described below will lead your users through an easy verification process without leaving your site, and redirect the user to your store's cart page when they are finished. Successful verifications will result in an update to that user's current cart, containing the attributes that were verified (student, teacher, etc.), and a reference to the verification history in SheerID for your future review. If a user is logged in at the time they are verified, that information will also be appended to their customer record. The presence of these attributes unlocks special behavior that can be configured with the plugin, such as special pricing, promotions and restricted items.

### Verification of Targeted Coupon Codes

Shopping Cart Price Rules may be configured in such a way that a user must be verified in order to apply the promotional offer to their cart. If this Price Rule has a Coupon Code, the user will automatically be prompted to verify upon entering this code. This is a great way to widely market your offer but protect it to its intended audience.

### Products Requiring Verification

Some products, such as academic software, are restricted to a specific audience for purchase. Using SheerID Verify, you can ensure only verified individuals are allowed to purchase that product. Users attempting to add this item to their cart will be required to verify in order to proceed. Only upon successful verification can this item be placed into the cart and the user can proceed with checkout.

### Verification Block

The verification block allows you to easily add an embedded verification form to any CMS page or static block. This solution is great for verifying customers on a targeted landing page.

### SheerID Lightbox

Simply by including an HTML link anywhere in your site with the attribute `data-sheerid="lightbox"`, you can prompt your users to verify with SheerID. When they click on the link, a lightbox will open containing a verification form. This gives Magento site designers complete control over how targeted offers are promoted in their stores.

### Order Review

Orders placed by verified users will display additional information from SheerID related to the verification in the Order View section of your store's Admin Panel.

### Customer Information

Registered customers that are verified by SheerID will be updated to store a record of verified affiliations. This information can be used to continue to extend special offers to these customer segments without requiring re-verification. The information about verified affiliations is available on the Account Information tab for the Customer within the store's Admin Panel.

## Installation

### Magento connect

The best way to install the SheerID Verify extension is through Magento Connect.

 1. Visit the [Magento Connect Extension Page](http://www.magentocommerce.com/magento-connect/sheerid-verify.html) for this extension to obtain an extension key from this site.
 2. In your Magento site's Admin Panel nav menu, select System > Magento Connect > Magento Connect Manager
 3. Re-enter your login information to access the Magento Connect Manager
 4. Paste the extension key from step 1 above and click the "Install" button to install.

### GitHub

The alternative installation method for users that are unable to install the extension directly from Magento Connect is to download a release directly from our [GitHub Repository - Releases](https://github.com/sheerid/sheerid-magento/releases).

 1. Visit the [Releases page in the SheerID-Magento GitHub Repository](https://github.com/sheerid/sheerid-magento/releases), (this is the source code for SheerID Verify extension)
 2. Find the desired release version (recommended: latest stable release), and click on the green download button to download the extension package tarball (`.tgz`) file.
 3. In your Magento site's Admin Panel nav menu, select System > Magento Connect > Magento Connect Manager
 4. Re-enter your login information to access the Magento Connect Manager
 5. Paste the extension key from step 1 above and click the "Install" button to install.
 6. Under "Direct package file upload", upload the package file obtained from GitHub and click "Upload"

_Note: This package file can also be installed from the command line using the `mage install-file` command:_

    ./mage install-file /path/to/SheerID_Verify-${version}.tgz

## Configuration

### SheerID Configuration

#### Account Set-up

In order to use the SheerID Verify extension, you must first set up your SheerID account. You can get started with a SheerID sandbox account, but you'll need to eventually get a production account in order to use the real eligibility data sources.

 1. Visit [SheerID Sandbox - Signup](https://services-sandbox.sheerid.com/home/signup.html) to create your account
 2. Once you've created your account successfully, log in.
 3. Follow the link to Settings > API Access Tokens and click "Issue New Access Token" to create your sandbox API access token (you'll need that when setting up SheerID settings in the Magento Admin Panel)

#### Campaign/Template Creation

Now that you have your account set up, you need to create at least one SheerID Campaign (Template) that will be used to verify users.

Visit the [Template Guide](https://services-sandbox.sheerid.com/home/guide.html?platform=magento) and follow the steps to create your campaign(s). The SheerID Settings page inside the Magento Admin Panel also contains a link to the template guide.

### Magento Admin Panel Configuration

The following extension settings are available in your site's Magento Admin Panel under System > Configuration, then SheerID Settings > SheerID from the sidebar.

 * **Sandbox Mode?** - If Sandbox Mode is enabled, your store will connect to the SheerID Sandbox API which makes it easy to test your site.  For more information, please view the [SheerID Developer Center](http://developer.sheerid.com/sandbox-source.html).
 * **SheerID Access Token** - The token which allows your system to access the SheerID API.  Obtained from the [SheerID Control Center](https://www.sheerid.com/home/) (refer to the [SheerID Configuration > Account Set-up](#account-set-up) section above). Sandbox and production mode will each require the corresponding API Access Token to be entered separately.
 * **Default Campaign** - This is the campaign that will be used in the absence of more specific configuration when verification is required throughout the site.
 * **Verify on Coupon Code Entry** - Enabled by default, this setting allows your site to prompt for SheerID verification for Shopping Cart Price Rules that contain SheerID conditions when that rule's coupon code is being entered. Note that if this setting is disabled, these types of coupon codes can only be successfully used by users that have already been verified.
 * **Verify on Product Page** - When enabled, users attempting to purchase a product that requires verification will be prompted to verify directly on the product page before the item can be added to the cart. Users that have already been verified will not be presented with a verification form.

### Shopping Cart Price Rules

In order to correlate promotions in your store with verifications, you must create Shopping Cart Price Rules within the Magento Admin (Promotions > Shopping Cart Price Rules). In the "Conditions" tab for a new or existing price rule, create one or more conditions using the additional SheerID condition options (listed under "Cart Attribute"). Make sure to complete the Actions tab to define what type of offer is applied.

#### Condition: SheerID Verified Affiliation Status

Set the affiliation status to one of the options from the pick-list.  

For example, to restrict your offer to verified active-duty military members, you would specify:

 > SheerID Verified Affiliation Status is Active-Duty Military

Then, if a customer successfully completes the verification form (using one of the techniques described in the [Features](#features) section above) with an affiliation type of Active-Duty Military, this offer will be applied to the cart.

#### Condition: SheerID Campaign Eligibility

In order to prompt for verification upon entering a coupon code as described in [Verification of Targeted Coupon Codes](#verification-of-targeted-coupon-codes) above, you must associate this promotion with a SheerID campaign to be displayed when the coupon code is entered.  This is done using the "SheerID Campaign Eligibility" condition, as described below:

 > SheerID Campaign Eligibility is [Name of Campaign]

With this condition, the eligibility criteria (required verified affiliations) are defined by the campaign selected.

If a Shopping Cart Price Rule with a coupon code has at least one SheerID Verified Affiliation Status condition, but no Campaign Eligibility condition, the store-wide default campaign will be used to verify upon entry of that coupon code, if the campaign is sufficient to verify at least one of the required affiliations.

### Products that Require Verification

Within the Product Catalog, Magento administrators may configure products to require SheerID verification prior to purchase.

 1. In your site's Magento Admin Panel, select Catalog > Manage Products
 2. Select the Product that should require verification
 3. Choose SheerID settings from the Product Information sidebar menu

#### Required Affiliation Type(s)

If at least one affiliation type is selected, verification will be required to add this product to the cart. If more than one selection is made, verification of ANY of the selected types will be sufficient to purchase.

#### Verification Campaign

If a user attempts to add this product to the cart before being verified for one of the required affiliation types above, they will be directed to this campaign to be verified. If no campaign is specified, the store-wide default campaign will be used. Note that this (or the default) campaign must be capable of verifying at least one of the affiliation(s) selected above, otherwise un-verified users will be presented with an error message when attempting to purchase, but no call to action.

### SheerID Verification Lightbox

This extension adds Javascript that enables the use of simple HTML to enable verification buttons or links integrated into the site wherever the site designer may desire.  Simply add an attribute (`data-sheerid="lightbox"`) to an A or BUTTON tag to enable it to act as a trigger to open a modal verification lightbox.

    <a href="#" data-sheerid="lightbox">Students, click here to get verified</a>

By default, this will open the default campaign selected in SheerID Settings. To pop open a lightbox for a specific campaign, add another attribute, `data-sheerid-template-id="${templateId}"`

    <a href="#" data-sheerid-template-id="546fbbfe0cf2995597038d67"
       data-sheerid="lightbox">Military families, click here to get verified</a>

### Verify Block Configuration

The verification block can be added to the content of a CMS page or static block via the following template tag:

    {{block type='sheerid/verify'}}

To further qualify the campaign that is used by this block, specify that campaign's ID as the template_id attribute:

    {{block type='sheerid/verify' template_id='546fbbfe0cf2995597038d67'}}

Optionally, you can specify the campaign name (namespace) instead of the ID. This name is the custom name specified on the "URL Namespace" step when creating a template via the template guide:

    {{block type='sheerid/verify' template_name='student-software-deal'}}
