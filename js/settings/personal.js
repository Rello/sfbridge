/**
 * Salesforce Bridge
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <sfbridge@scherello.de>
 * @copyright 2021 Marcel Scherello
 */

'use strict';
if (!OCA.SFbridge) {
    /**
     * @namespace
     */
    OCA.SFbridge = {
    };
}

OCA.SFbridge.Settings = {
    setPaypal: function () {
        let params = 'client_id=' + document.getElementById('paypal_client_id').value
            + '&client_secret=' + document.getElementById('paypal_client_secret').value
            + '&instanceUrl=' + document.getElementById('paypal_instanceUrl').value;

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

    getPaypal: function () {
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

    getSalesforce: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/settings/salesforce', true));
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
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('paypal_save').addEventListener('click', OCA.SFbridge.Settings.setPaypal);
    document.getElementById('salesforce_save').addEventListener('click', OCA.SFbridge.Settings.setSalesforce);
    document.getElementById('sfBackground').addEventListener('click', OCA.SFbridge.Settings.background);
    document.getElementById('sfBackground').checked = OCA.SFbridge.Settings.getInitialState('background') === 'true';
});