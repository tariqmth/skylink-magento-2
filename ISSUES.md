# Known Issues

## 1.0.0 beta-6

1. When an entire customer is saved through the Magento admin or the frontend (excluding just saving addresses), a command to sync the customer is queued multiple times - `1 + (1 * number of addresses)`. This is because there is an observer on the address repository that triggers a customer sync.
2. Customers are not synchronising back to Retail Express with the new EDS APIs.
