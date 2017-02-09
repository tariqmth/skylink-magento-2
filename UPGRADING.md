# SkyLink for Magento 2 Upgrade Guide

## Upgrading 1.0.0-beta5 to 1.0.0-beta6

1. **You must be connected to a Retail Express database that runs the latest APIs and EDS functionality**.
2. Legacy package has been deprecated so remove the line containing `retail-express/skylink-magento-2-legacy` from your `composer.json` file.
3. V2 Order Shim package has been deprecated.
3. Multiple console commands have been renamed to better reflect their responsibility, please reference :
   1. `retail-express:command-bus:consume` becomes `retail-express:command-bus:consume-queue`
   2. `retail-express:lagacy:bulk-customers` becomes `retail-express:skylink:bulk-customers`
   3. `retail-express:lagacy:bulk-products` becomes `retail-express:skylink:bulk-products`

## Upgrading from 1.0.0-beta6 to 1.0.0-beta7
1. You must re-run the migrations for the extension. Becuase Magento verisoning does not allow `beta` versions, it appears impossible to introduce new migrations in subsequent betas of the same version. To re-run migrations, run the following:
  1. Run the following SQL command:

     ```sql
     UPDATE `setup_module`
     SET `schema_version` = null, `data_version` = null
     WHERE `module` = "RetailExpress_SkyLink"
     ```
  2. Re-run `bin/magento setup:upgrade`. If you see an error that contains `Duplicate entry 'RetailExpress_SkyLink' for key 'PRIMARY'`, you have successfully ran the migrations, however the database has not been restored.
  1. Run the following SQL command to complete manual migration:

     ```sql
     UPDATE `setup_module`
     SET `schema_version` = "0.1.0", `data_version` = null
     WHERE `module` = "RetailExpress_SkyLink"
     ```
