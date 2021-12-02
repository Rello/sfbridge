<?php

namespace OCA\SFbridge\Salesforce\Exception;

use GuzzleHttp\Exception\ClientException;

class SalesforceAuthenticationException extends SalesforceException
{

    /**
     * @var string
     */
    private static $errorMessage = "Salesforce authentication request error";

    /**
     * @param ClientException $e
     * @return SalesforceAuthenticationException|SalesforceException
     */
    public static function fromClientException(ClientException $e)
    {
        $responseString = $e->getResponse()->getBody()->getContents();
        $responseData = json_decode($responseString, true);
        $ret = new self(self::$errorMessage . ': ' . $e->getMessage(), $e->getResponse()->getStatusCode());
        $ret->setErrors($responseData);
        return $ret;
    }
}
