<?php
/**
 * Audio Player
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <audioplayer@scherello.de>
 * @copyright 2016-2021 Marcel Scherello
 */

script('sfbridge', 'settings/personal');
?>

<div class="section">
    <h2><?php p($l->t('Paypal Settings')); ?></h2>
    <div style="display: table;">

        <label for="paypal_client_id"><?php p($l->t('paypal_client_id')); ?>:</label>
        <input type="text" id="paypal_client_id" value="<?php p($_['paypal_client_id']); ?>" style="width: 600px;"/>
        <br>
        <label for="paypal_client_secret"><?php p($l->t('paypal_client_secret')); ?>:</label>
        <input type="text" id="paypal_client_secret" value="<?php p($_['paypal_client_secret']); ?>" style="width: 600px;"/>
        <br>
        <label for="paypal_instanceUrl"><?php p($l->t('paypal_instanceUrl')); ?>:</label>
        <input type="text" id="paypal_instanceUrl" value="<?php p($_['paypal_instanceUrl']); ?>" style="width: 300px;"/>
        <br>
    </div>
</div>

<div class="section">
    <h2><?php p($l->t('Salesforce Settings')); ?></h2>
    <div>
        <label for="salesforce_client_id"><?php p($l->t('salesforce_client_id')); ?>:</label>
        <input type="text" id="salesforce_client_id" value="<?php p($_['salesforce_client_id']); ?>" style="width: 600px;"/>
        <br>
        <label for="salesforce_client_secret"><?php p($l->t('salesforce_client_secret')); ?>:</label>
        <input type="text" id="salesforce_client_secret" value="<?php p($_['salesforce_client_secret']); ?>" style="width: 600px;"/>
        <br>
        <label for="salesforce_username"><?php p($l->t('salesforce_username')); ?>:</label>
        <input type="text" id="salesforce_username" value="<?php p($_['salesforce_username']); ?>" style="width: 300px;"/>
        <br>
        <label for="salesforce_password"><?php p($l->t('salesforce_password')); ?>:</label>
        <input type="text" id="salesforce_password" value="<?php p($_['salesforce_password']); ?>" style="width: 300px;"/>
    </div>
</div>