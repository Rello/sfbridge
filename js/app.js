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
    OCA.SFbridge = {
    };
}

/**
 * @namespace OCA.SFbridge.SF
 */
OCA.SFbridge.SF = {

    auth: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/auth/salesforce', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
                document.getElementById('sfAuthToken').innerText = data['token'];
                document.getElementById('sfAuthUrl').innerText = data['url'];
            }
        };
        xhr.send();
    },

    contactIndex: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/contacts', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
                document.getElementById('sfAccounts').innerText = data;
            }
        };
        xhr.send();
    },

    contactSearch: function () {
        let params = 'search=' + document.getElementById('sfPaypalEmailInput').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/contacts/search', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
                if(parseInt(data['totalSize']) === 0) {
                    document.getElementById('sfConactNameInput').value = 'kein Kontakt';
                    document.getElementById('sfContactIdInput').value = '';
                    document.getElementById('sfContactAccountIdInput').value = '';
                } else {
                    let contact = data['records'][0];
                    document.getElementById('sfConactNameInput').value = contact['Name'];
                    document.getElementById('sfContactIdInput').value = contact['Id'];
                    document.getElementById('sfContactAccountIdInput').value = contact['AccountId'];

                    OCA.SFbridge.SF.opportunitySearch();
                }
            }
        };
        xhr.send(params);
    },

    contactCreate: function () {
        let params = 'givenName=' + document.getElementById('sfPaypalGivenNameInput').value
            + '&surName=' + document.getElementById('sfPaypalSurNameInput').value
            + '&alternateName=' + document.getElementById('sfPaypalAlternateNameInput').value
            + '&email=' + document.getElementById('sfPaypalEmailInput').value
        ;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/contact', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                OCA.SFbridge.SF.contactSearch();
            }
        };
        xhr.send(params);
    },

    opportunitySearch: function () {
        let params = 'search=' + document.getElementById('sfContactIdInput').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/opportunity/search', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
                if(parseInt(data['totalSize']) === 0) {
                    document.getElementById('sfOpportunity').innerText = 'keine Opp';
                } else {
                    let contacts = '';
                    for (let contact of data['records']) {
                        contacts = contacts + 'OpportunityId: ' + contact['Id'] + ' Amount: ' + contact['Amount'] + '<br>';
                    }
                    document.getElementById('sfOpportunity').innerHTML = contacts;
                }
            }
        };
        xhr.send(params);
    },

    opportunityCreate: function () {
        let params = 'contactId=' + document.getElementById('sfContactIdInput').value
            + '&name=' + document.getElementById('sfConactNameInput').value
            + '&accountId=' + document.getElementById('sfContactAccountIdInput').value
            + '&amount=' + document.getElementById('sfPaypalDonationInput').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/opportunity', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
            }
        };
        xhr.send(params);
    },

    paymentSearch: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/payment/search', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
            }
        };
        xhr.send();
    },

};

OCA.SFbridge.Paypal = {

    auth: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/auth/paypal', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
            }
        };
        xhr.send();
    },

    transactions: function () {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', OC.generateUrl('apps/sfbridge/transactions', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let data = JSON.parse(xhr.response);
            }
        };
        xhr.send();
    },

}

OCA.SFbridge.Compare = {

    compare: function () {
        let update = document.getElementById('sfLiveRun').checked;
        const button = document.getElementById('sfCompare');
        button.classList.add('loading');
        button.disabled = true;

        let params = 'update=' + update
            + '&from=' + document.getElementById('sfFrom').value
            + '&to=' + document.getElementById('sfTo').value;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/sfbridge/compare', true));
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    let data = JSON.parse(xhr.response);
                    let string = '';
                    for (let key in data['counts']) {
                        string = string + key + ': ' + data['counts'][key] + '\n';
                    }
                    document.getElementById('sfCounts').innerText = string;
                    document.getElementById('sfTransactions').value = JSON.stringify(data['all transactions'], undefined, 4);
                    document.getElementById('sfTransactionsNew').value = JSON.stringify(data['new transactions'], undefined, 4);
                    document.getElementById('sfContactsNew').value = JSON.stringify(data['new contacts'], undefined, 4);
                    document.getElementById('sfOpportunitiesNew').value = JSON.stringify(data['new opportunities'], undefined, 4);
                    document.getElementById('sfOpportunitiesUpdate').value = JSON.stringify(data['updated opportunities'], undefined, 4);
                } else {
                    document.getElementById('sfCounts').innerText = 'Fehler ';
                    document.getElementById('sfTransactions').value = xhr.response;
                }

                button.classList.remove('loading');
                button.disabled = false;
                document.getElementById('sfLiveRun').checked = false
            }
        };
        xhr.send(params);
    },
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('sfCompare').addEventListener('click', OCA.SFbridge.Compare.compare);
});