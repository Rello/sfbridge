<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCP\Util;

Util::addScript('sfbridge', 'settings/admin');
Util::addScript('sfbridge', 'userGuidance');
?>

<div class="section">
    <input type="checkbox" id="sfBackground" class="checkbox"><label for="sfBackground"><?php p($l->t('Run checks in background')); ?></label>
    <br><br>
    <input type="checkbox" id="sfBackgroundUpdate" class="checkbox"><label for="sfBackgroundUpdate"><?php p($l->t('Update Salesforce during background check')); ?></label>
</div>

<div class="section">
    <h2><?php p($l->t('Talk settings for log messages')); ?></h2>
    <input type="text" id="talkRoom" value="<?php p($_['talkRoom']); ?>" style="width: 300px;">
    <p>
        <em><?php p($l->t('A valid talk room is required')); ?></em>
    </p>
    <br>
    <input type="text" id="talkUser" value="<?php p($_['talkUser']); ?>" style="width: 300px;">
    <p>
        <em><?php p($l->t('A valid user id is required who is member of the talk room')); ?></em>
    </p>
    <br><br>
    <button id="saveTalk" type="button" class="primary">
		<?php p($l->t('Save')); ?>
    </button>
</div>

<div class="section">
    <h2><?php p($l->t('Paypal Settings')); ?></h2>
    <div style="display: table;">
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="paypal_client_id"><?php p($l->t('Paypal Client Id')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="paypal_client_id" value="<?php p($_['paypal_client_id']); ?>"
                       style="width: 600px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">

                <label for="paypal_client_secret"><?php p($l->t('Paypal Client Secret')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="paypal_client_secret" value="<?php p($_['paypal_client_secret']); ?>"
                       style="width: 600px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="paypal_instanceUrl"><?php p($l->t('Paypal Instance URL')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="paypal_instanceUrl" value="<?php p($_['paypal_instanceUrl']); ?>"
                       style="width: 300px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="paypal_excludeTypes"><?php p($l->t('Excluded Transaction Types')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="paypal_excludeTypes" value="<?php p($_['paypal_excludeTypes']); ?>"
                       style="width: 300px;"/>
                <br>
                <em><?php p($l->t('Separate with ","')); ?> </em>
                    <br>
                <em>https://developer.paypal.com/docs/reports/reference/tcodes/
                </em>
            </div>
        </div>
    </div>
    <button id="savePaypal" type="button" class="primary">
        <?php p($l->t('Save')); ?>
    </button>
</div>

<div class="section">
    <h2><?php p($l->t('Salesforce Settings')); ?></h2>
    <div style="display: table;">
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="salesforce_client_id"><?php p($l->t('Salesforce Client Id')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="salesforce_client_id" value="<?php p($_['salesforce_client_id']); ?>"
                       style="width: 600px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="salesforce_client_secret"><?php p($l->t('Salesforce Client Secret')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="salesforce_client_secret" value="<?php p($_['salesforce_client_secret']); ?>"
                       style="width: 600px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="salesforce_username"><?php p($l->t('Salesforce Username')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="salesforce_username" value="<?php p($_['salesforce_username']); ?>"
                       style="width: 300px;"/>
            </div>
        </div>
        <div style="display: table-row;">
            <div style="display: table-cell; width: 200px;">
                <label for="salesforce_password"><?php p($l->t('Salesforce Password')); ?></label>
            </div>
            <div style="display: table-cell;">
                <input type="text" id="salesforce_password" value="<?php p($_['salesforce_password']); ?>"
                       style="width: 300px;"/>
                Password + Security Token!
            </div>
        </div>
    </div>
    <button id="saveSalesforce" type="button" class="primary">
        <?php p($l->t('Save')); ?>
    </button>
</div>

<div class="section">
    <h2><?php p($l->t('Bank Settings')); ?></h2>
    <input type="text" id="sfBankExcludes" value="<?php p($_['bank_excludes']); ?>" style="width: 300px;">
    <p>
        <em><?php p($l->t('A list of bank transaction senders, which will be excluded from processing. Separate them with ";"')); ?></em>
    </p>
    <br>
    <input type="text" id="sfBankName" value="<?php p($_['bank_replaceName']); ?>" style="width: 300px;">
    <p>
        <em><?php p($l->t('Name of the Bank that will be used, when the following transaction text is found:')); ?></em>
    </p>
    <input type="text" id="sfBankTexts" value="<?php p($_['bank_searchText']); ?>" style="width: 300px;">
    <br><br>
    <button id="saveBank" type="button" class="primary">
        <?php p($l->t('Save')); ?>
    </button>
</div>



