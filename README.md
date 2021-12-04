![Bildschirmfoto 2021-12-04 um 16 37 58](https://user-images.githubusercontent.com/13385119/144723412-df0b2d76-2c2d-483f-ba77-14527fc80683.png)
This automation bridges the gap between Paypal and the Salesforce solution for non-profit organizations.<br><br>
All Paypal transactions like donations or payments will be automatically synchronized with the Salesforce Nonprofit Success Pack (NPSP).<br>
New Contacts/Accounts will be crated if not existing. New Opportunities will be created or updated (recurring donations).

The job can be run in the background and will notify the user if new transactions are available in Paypal.
<p align="center">
<img src="https://raw.githubusercontent.com/Rello/sfbridge/master/screenshots/app2.png" alt="Main" width="300" title="SFBridge">
<img src="https://raw.githubusercontent.com/Rello/sfbridge/master/screenshots/settings2.png" alt="Main" width="300" title="SFBridge">
</p>

## Features
- No customer data is stored (data privacy)
- Paypal: Read transaction history
- Salesforce: Create Household/Organization accounts
- Salesforce: Create/update Opportunities/Payments/Allocations
- Simulation mode before updating
- Automated search for new transactions via Nextcloud background job
- Nextcloud Notifications for new transactions

## Prerequisites
- Salesforce Nonprofit Success Pack (NPSP)
- API user in Saleforce
- oAuth "connected app" in Salesforce enabled
- API user in Paypal

## Installation
- [Nextcloud App Store](https://apps.nextcloud.com/apps/sfbridge)

## Maintainers
- [Marcel Scherello](https://github.com/rello) (author, project leader)

## Support
Thank you to PhpStorm from [JetBrains](https://www.jetbrains.com/?from=AudioPlayerforNextcloudandownCloud) <br>
<img src="https://raw.githubusercontent.com/rello/data/master/screenshots/jetbrains.svg" alt="Main" width="100" title="Salesforce Bridge">

---
[![Version](https://img.shields.io/github/release/rello/sfbridge.svg)](https://github.com/rello/sfbridge/blob/master/CHANGELOG.md)&#160;[![License: AGPLv3](https://img.shields.io/badge/license-AGPLv3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)&#160;&#160;&#160;[![Bitcoin](https://img.shields.io/badge/donate-Bitcoin-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)&#160;[![PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)
