<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Salesforce\Authentication;

use OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException;
use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class PasswordAuthentication implements AuthenticationInterface
{

    /**
     * @var string
     */
    protected $client;

    /**
     * @var string
     */
    protected $endPoint;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $instanceUrl;

    /**
     * PasswordAuthentication constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        $this->endPoint = 'https://login.salesforce.com/';
        $this->options = $options;
    }

    /**
     * @throws SalesforceAuthenticationException
     * @throws SalesforceException
     * @throws GuzzleException
     */
    public function authenticate()
    {
        $client = new Client();

        try {
            $request = $client->request(
                "post",
                "{$this->endPoint}services/oauth2/token",
                ['form_params' => $this->options]
            );
        } catch (ClientException $e) {
            throw SalesforceAuthenticationException::fromClientException($e);
        }

        $response = json_decode($request->getBody(), true);

        if ($response) {
            $this->accessToken = $response['access_token'];
            $this->instanceUrl = $response['instance_url'];
        }
    }

    /**
     * @param string $endPoint
     */
    public function setEndpoint($endPoint)
    {
        $this->endPoint = $endPoint;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getInstanceUrl()
    {
        return $this->instanceUrl;
    }
}
