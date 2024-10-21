<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Salesforce\Authentication;

interface AuthenticationInterface
{

    /**
     * @return mixed
     */
    public function getAccessToken();

    /**
     * @return mixed
     */
    public function getInstanceUrl();
}
