# SkyLink for Magento 2 Upgrade Guide

## Upgrading 1.0.0-beta4 to 1.0.0-beta5

1. **You must be connected to a Retail Express database that runs the latest APIs and EDS functionality**.
2. Legacy package has been deprecated so remove the line containing `retail-express/skylink-magento-2-legacy` from your `composer.json` file.
3. V2 Order Shim package has been deprecated.
3. Multiple console commands have been renamed to better reflect their responsibility, please reference :
   1. `retail-express:command-bus:consume` becomes `retail-express:command-bus:consume-queue`
   2. `retail-express:lagacy:bulk-customers` becomes `retail-express:skylink:bulk-customers`
   3. `retail-express:lagacy:bulk-products` becomes `retail-express:skylink:bulk-products`
