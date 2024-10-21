<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

?>
<div id="sfbridge-content" style="width:100%; padding: 20px 5%;">
    <h2 id="reportHeader">Salesforce Bridge</h2>

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
