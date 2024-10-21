<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
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

        ['name' => 'settings#setParameterPaypal', 'url' => '/settings/paypal', 'verb' => 'POST'],
        ['name' => 'settings#setParameterSalesforce', 'url' => '/settings/salesforce', 'verb' => 'POST'],
        ['name' => 'settings#setParameterBank', 'url' => '/settings/bank', 'verb' => 'POST'],
		['name' => 'settings#setParameterTalk', 'url' => '/settings/talk', 'verb' => 'POST'],

        // API
        // V1
        ['name' => 'ApiData#preflighted_cors', 'url' => '/api/1.0/{path}', 'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
        ['name' => 'ApiData#addData', 'url' => '/api/1.0/adddata', 'verb' => 'POST'],

    ]
];
