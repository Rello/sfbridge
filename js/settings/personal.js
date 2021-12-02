/**
 * Audio Player
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <audioplayer@scherello.de>
 * @copyright 2016-2021 Marcel Scherello
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
                    let data = JSON.parse(xhr.response);
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
                    let data = JSON.parse(xhr.response);
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


}

document.addEventListener('DOMContentLoaded', function () {
});