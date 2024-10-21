/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

'use strict';
if (!OCA.SFbridge) {
    /**
     * @namespace
     */
    OCA.SFbridge = {};
}

OCA.SFbridge.Settings = {
    setPaypal: function () {
        let params = 'client_id=' + document.getElementById('paypal_client_id').value
            + '&client_secret=' + document.getElementById('paypal_client_secret').value
            + '&instanceUrl=' + document.getElementById('paypal_instanceUrl').value
            + '&excludeTypes=' + document.getElementById('paypal_excludeTypes').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/settings/paypal', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    OCA.SFbridge.Notification.notification('success', t('sfbridge', 'Saved'));
                } else {
                    OCA.SFbridge.Notification.notification('error', t('sfbridge', 'Error'));
                }
            }
        };
        xhr.send(params);
    },

    getPaypal_backup: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/settings/paypal', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    let data = JSON.parse(xhr.response);
                }
            }
        };
        xhr.send();
    },

    setSalesforce: function () {
        let params = 'client_id=' + document.getElementById('salesforce_client_id').value
            + '&client_secret=' + document.getElementById('salesforce_client_secret').value
            + '&username=' + document.getElementById('salesforce_username').value
            + '&password=' + document.getElementById('salesforce_password').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/settings/salesforce', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    OCA.SFbridge.Notification.notification('success', t('sfbridge', 'Saved'));
                } else {
                    OCA.SFbridge.Notification.notification('error', t('sfbridge', 'Error'));
                }
            }
        };
        xhr.send(params);
    },

    setBank: function () {
        let params = 'excludes=' + document.getElementById('sfBankExcludes').value
            + '&searchText=' + document.getElementById('sfBankTexts').value
            + '&replaceName=' + document.getElementById('sfBankName').value;
        ;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/settings/bank', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    OCA.SFbridge.Notification.notification('success', t('sfbridge', 'Saved'));
                } else {
                    OCA.SFbridge.Notification.notification('error', t('sfbridge', 'Error'));
                }
            }
        };
        xhr.send(params);
    },

    setTalk: function () {
        let params = 'talkRoom=' + document.getElementById('talkRoom').value
            + '&talkUser=' + document.getElementById('talkUser').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/settings/talk', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    OCA.SFbridge.Notification.notification('success', t('sfbridge', 'Saved'));
                } else {
                    OCA.SFbridge.Notification.notification('error', t('sfbridge', 'Error'));
                }
            }
        };
        xhr.send(params);
    },

    background: function () {
        let background = document.getElementById('sfBackground').checked;

        let params = 'background=' + background;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/background', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    OCA.SFbridge.Notification.notification('success', t('sfbridge', 'Saved'));
                } else {
                    OCA.SFbridge.Notification.notification('error', t('sfbridge', 'Error'));
                }
            }
        };
        xhr.send(params);
    },

    getInitialState: function (key) {
        const app = 'sfbridge';
        const elem = document.querySelector(`#initial-state-${app}-${key}`)
        if (elem === null) {
            return false;
        }
        return JSON.parse(atob(elem.value))
    }

}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('savePaypal').addEventListener('click', OCA.SFbridge.Settings.setPaypal);
    document.getElementById('saveSalesforce').addEventListener('click', OCA.SFbridge.Settings.setSalesforce);
    document.getElementById('saveBank').addEventListener('click', OCA.SFbridge.Settings.setBank);
    document.getElementById('saveTalk').addEventListener('click', OCA.SFbridge.Settings.setTalk);
    document.getElementById('sfBackground').addEventListener('click', OCA.SFbridge.Settings.background);
    document.getElementById('sfBackground').checked = OCA.SFbridge.Settings.getInitialState('background') === '1';
});