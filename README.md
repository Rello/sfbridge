![Bildschirmfoto 2021-12-04 um 16 37 58](https://user-images.githubusercontent.com/13385119/144723412-df0b2d76-2c2d-483f-ba77-14527fc80683.png)
This automation in Nextcloud bridges the gap between financial transactions and Salesforce.<br>
Donations/payments in Paypal or bank accounts can be synchronized with the Salesforce Nonprofit Success Pack (NPSP).<br>

<p align="center">
<img src="https://raw.githubusercontent.com/Rello/sfbridge/master/screenshots/app2.png" alt="Main" width="300" title="SFBridge">
<img src="https://raw.githubusercontent.com/Rello/sfbridge/master/screenshots/settings2.png" alt="Main" width="300" title="SFBridge">
</p>

## Benefits
- Reduce manual efforts to enter transactions in salesforce
- High quality of data (e.g. no duplicates)
- Avoid user errors
- No data is stored in Nextcloud (compliance to GPDR)
- License free

## Features
- Paypal: Read transaction history
- Bank (via API): Read transaction history
- Salesforce: Create Household/Organization accounts and contact on demand
- Salesforce: Create/update Opportunities/Payments/Allocations
- Salesforce: Link Opportunities to Campaigns
- Nextcloud: Automation via background job
- Nextcloud: Notifications for new transactions

By using the API for csv data, bank accounts can also be synced (e.g. via the file export of MoneyMoney)

## Prerequisites
- Salesforce Nonprofit Success Pack (NPSP)
- API user in Salesforce
- oAuth "connected app" in Salesforce enabled
- API user in Paypal

## Installation
<b>! Get in contact with me if you are interested !<br>
! It is working for us. Your setup might be different !<br>
! I am interested to make it flexible for any usecase !</b><br>
- [Nextcloud App Store](https://apps.nextcloud.com/apps/sfbridge)

## Maintainers
- [Marcel Scherello](https://github.com/rello) (author, project leader)

## Support
Thank you to PhpStorm from [JetBrains](https://www.jetbrains.com/?from=AudioPlayerforNextcloudandownCloud) <br>
<img src="https://raw.githubusercontent.com/rello/analytics/master/screenshots/jetbrains.svg" alt="Main" width="100" title="Salesforce Bridge">

---
[![Version](https://img.shields.io/github/release/rello/sfbridge.svg)](https://github.com/rello/sfbridge/blob/master/CHANGELOG.md)&#160;[![License: AGPLv3](https://img.shields.io/badge/license-AGPLv3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)&#160;&#160;&#160;[![Bitcoin](https://img.shields.io/badge/donate-Bitcoin-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)&#160;[![PayPal](https://img.shields.io/badge/donate-PayPal-blue.svg)](https://github.com/rello/audioplayer/wiki/donate)
