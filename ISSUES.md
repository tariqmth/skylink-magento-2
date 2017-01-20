# Known Issues

1. When an entire customer is saved through the Magento admin or the frontend (excluding just saving addresses), a command to sync the customer is queued multiple times - `1 + (1 * number of addresses)`. This is because there is an observer on the address repository that triggers a customer sync.
