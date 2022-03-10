<?php
/**
 * Salesforce Bridge
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <sfbridge@scherello.de>
 * @copyright 2021 Marcel Scherello
 */
?>
<div id="sfbridge-content" style="width:100%; padding: 20px 5%;">
    <h2 id="reportHeader">Salesforce Bridge</h2>

<!--    <button id="sfAuthButton" type="button" class="primary">
        <?php /*p($l->t('SF Auth')); */?>
    </button>
    <div id="sfAuthToken"></div>
    <div id="sfAuthUrl"></div>
    <br>

    <h2>Spende:</h2>
    Email:<input id="sfPaypalEmailInput" value="test2@scherello.de"><br>
    Vorname:<input id="sfPaypalGivenNameInput" value="Vorname"><br>
    Nachname:<input id="sfPaypalSurNameInput" value="Nachname"><br>
    AlternateName:<input id="sfPaypalAlternateNameInput" value="Alt Name"><br>
    Spende:<input id="sfPaypalDonationInput" value="15"><br>
    <br>

    <button id="sfContactSearchButton" type="button" class="primary">
        <?php /*p($l->t('Search Contact')); */?>
    </button>
    <button id="sfContactCreateButton" type="button" class="primary">
        <?php /*p($l->t('Create Contact')); */?>
    </button>
    <h2>Contact:</h2>
    Name:<input id="sfConactNameInput" value=""><br>
    Contact Id:<input id="sfContactIdInput" value=""><br>
    Account Id:<input id="sfContactAccountIdInput" value=""><br>
    <br>


    <button id="sfOpportunitySearchButton" type="button" class="primary">
        <?php /*p($l->t('Search Opportunity')); */?>
    </button>
    <button id="sfOpportunityCreateButton" type="button" class="primary">
        <?php /*p($l->t('Create Opportunity')); */?>
    </button>
    <div id="sfOpportunity"></div>
-->

    <input id="sfFrom" type="datetime-local" value="<?php echo date('Y-m-d\T00:00', strtotime("-3 days")); ?>" style="width: 200px;">
    <input id="sfTo" type="datetime-local" value="<?php echo date('Y-m-d\T00:00', strtotime("+1 day")); ?>" style="width: 200px;">
    <button id="sfCompare" type="button" class="primary">
        <?php p($l->t('Start')); ?>
    </button> <input type="checkbox" id="sfLiveRun" class="checkbox"><label for="sfLiveRun">Update!</label>
    <br>
    <br>
    <div id="sfCounts" name="Text1" cols="100" rows="7" style="width: 500px;"></div>
    <br>New Transactions:<br>
    <textarea id="sfTransactionsNew" name="Text1" cols="100" rows="10" style="width: 500px;"></textarea>
    <br>New Contacts:<br>
    <textarea id="sfContactsNew" name="Text1" cols="100" rows="5" style="width: 500px;"></textarea>
    <br>New Opportunities:<br>
    <textarea id="sfOpportunitiesNew" name="Text1" cols="100" rows="5" style="width: 500px;"></textarea>
    <br>Updated  Opportunities:<br>
    <textarea id="sfOpportunitiesUpdate" name="Text1" cols="100" rows="5" style="width: 500px;"></textarea>
    <br>Transactions:<br>
    <textarea id="sfTransactions" name="Text1" cols="100" rows="5" style="width: 500px;"></textarea>

</div>
<div id="analytics-loading" style="width:100%; padding: 100px 5%;" hidden>
    <div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>
</div>
