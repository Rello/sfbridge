/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** global: OCA */
/** global: OCP */
/** global: OC */
'use strict';

if (!OCA.SFbridge) {
    /**
     * @namespace
     */
    OCA.SFbridge = {};
}

/**
 * @namespace OCA.SFbridge.Notification
 */
OCA.SFbridge.Notification = {
    dialog: function (title, text, type) {
        OC.dialogs.message(
            text,
            title,
            type,
            OC.dialogs.OK_BUTTON,
            function () {
            },
            true,
            true
        );
    },

    confirm: function (title, text, callback) {
        OC.dialogs.confirmHtml(
            text,
            title,
            function (e) {
                if (e === true) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            true
        );
    },

    notification: function (type, message) {
        if (parseInt(OC.config.versionstring.substr(0, 2)) >= 17) {
            if (type === 'success') {
                OCP.Toast.success(message)
            } else if (type === 'error') {
                OCP.Toast.error(message)
            } else {
                OCP.Toast.info(message)
            }
        } else {
            OC.Notification.showTemporary(message);
        }
    },

}
