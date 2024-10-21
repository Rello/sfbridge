<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Salesforce\Exception;

use Exception;
use GuzzleHttp\Exception\ClientException;

class SalesforceException extends Exception
{

    /**
     * @var string
     */
    private static $errorMessage = "Salesforce request error";

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param ClientException $e
     * @return SalesforceException
     */
    public static function fromClientException(ClientException $e)
    {
        $responseString = $e->getResponse()->getBody()->getContents();
        $responseData = json_decode($responseString, true);
        $ret = new self(self::$errorMessage . ': ' . $e->getMessage(), $e->getResponse()->getStatusCode());
        $ret->setErrors($responseData);
        return $ret;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
}
