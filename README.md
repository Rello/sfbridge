This automation bridges the gap between Paypal and Salesforce for non-profit organizations.<br><br>
All Paypal transactions like donations or payments will be automatically syncronied with the N**? cloud of Salesforce.<br>
New Contacts/Accounts will be crated if not existing. New Opportunities will be created or updated (recurring).

<p align="center">
<img src="https://raw.githubusercontent.com/rello/sfbridge/master/screenshots/app.png" alt="Main" width="300" title="SFBridge">
<img src="https://raw.githubusercontent.com/rello/sfbridge/master/screenshots/settings.png" alt="Main" width="300" title="SFBridge">
</p>

## Features
- Paypal: Read transaction history
- Salesforce: Create Household/Organization accounts
- Salesforce: Create Opportunities/Payments/Fees (GAU transaction)
- Salesforce: Update Opportunities (of recurring donations)
- Simulation mode before updating
- Automated search for new transactions via Nextcloud background job
- Nextcloud Notifications for new transactions

## Prerequisites
- non-proft cloud of Saleforce
- API user in Saleforce
- oAuth connected app in Saleforce with the corresponding scopes
- API user in Paypal

## Installation
- [Nextcloud App Store](https://apps.nextcloud.com/apps/sfbridge)

## Maintainers
- [Marcel Scherello](https://github.com/rello) (author, project leader)

## Support
Thank you to PhpStorm from [JetBrains](https://www.jetbrains.com/?from=AudioPlayerforNextcloudandownCloud) <br>
<img src="https://raw.githubusercontent.com/rello/data/master/screenshots/jetbrains.svg" alt="Main" width="100" title="Analytics">

---

[![Version](https://img.shields.io/github/release/rello/analytics.svg)](https://github.com/rello/sfbridge/blob/master/CHANGELOG.md)&#160;[![License: AGPLv3](https://img.shields.io/badge/license-AGPLv3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)&#160;&#160;&#160;[![Bitcoin](https://img.shields.io/badge/donate-Bitcoin-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)&#160;[![PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)
