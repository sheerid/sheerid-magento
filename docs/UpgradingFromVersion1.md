# SheerID Verify Extension - Version 2

## Overview

Version 2 of the SheerID Verify Magento extension has been developed to make use of SheerID's hosted verification web apps. This update will allow Magento administrators to leverage the customization and flexibility of the SheerID-hosted verification workflows, and ensures that the extension will continue to stay up to date with the latest and greatest features being developed by SheerID. A complete overview of the Version 2 feature set is available in the [SheerID Verify User Guide](SheerID_Verify-UserGuide.md).

## Upgrade Notes for SheerID Verify extension Version 1.x Users

Users of the existing (version 1) extension may continue to use the current extension on their sites as-is without being affected by the changes in version 2; however, if an upgrade to version 2.x is desired, it is important to consider the information below.

### Features With Non-Backwards-Compatible Changes

#### Verify Block

The verify block no longer supports the `affiliation_types` attribute. You must first create a campaign template using the template guide (see User Guide for more information), then supply that campaign's ID or namespace to the verify block. Alternatively, you may set the default campaign within the SheerID settings, in which case you do not need to supply any attributes when referencing the block with template tags or within a layout XML file.

### Features That Were Removed

#### One-Page Checkout Integration

The verification step on the one-page checkout form has been removed. Instead, you should verify users before they enter the checkout workflow with one of the v2-supported verification use cases (refer to the User Guide for more information).

#### Verify Widget

The verification widget has also been removed. Site administrators should instead use the Verification Lightbox or Verification Block in place of the verification widget (refer to the User Guide for more information).