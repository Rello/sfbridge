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

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Salesforce
        ['name' => 'salesforce#auth', 'url' => '/auth/salesforce', 'verb' => 'GET'],

        ['name' => 'salesforce#contactCreate', 'url' => '/contact', 'verb' => 'POST'],
        ['name' => 'salesforce#contactSearch', 'url' => '/contacts/search', 'verb' => 'POST'],
        ['name' => 'salesforce#contactIndex', 'url' => '/contacts', 'verb' => 'GET'],


        ['name' => 'salesforce#opportunitySearch', 'url' => '/opportunity/search', 'verb' => 'POST'],
        ['name' => 'salesforce#opportunityCreate', 'url' => '/opportunity', 'verb' => 'POST'],

        ['name' => 'salesforce#paymentSearch', 'url' => '/payment/search', 'verb' => 'POST'],

        ['name' => 'paypal#auth', 'url' => '/auth/paypal', 'verb' => 'GET'],
        ['name' => 'paypal#transactions', 'url' => '/transactions', 'verb' => 'GET'],

        ['name' => 'compare#compare', 'url' => '/compare', 'verb' => 'POST'],

        ['name' => 'settings#background', 'url' => '/background', 'verb' => 'POST'],

    ]
];
