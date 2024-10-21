<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Service;

use OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class PaypalService
{
    const TYPE_SUBSCRIPTION = 'T0002';
    const TYPE_PAYMENT = 'T0003';
    const TYPE_DONATION = 'T0013';

    private $logger;
    private $StoreService;
    private $accessToken;
    private $instanceUrl;

    const APPLICATION = 'paypal';

    public function __construct(
        LoggerInterface $logger,
        StoreService $StoreService
    )
    {
        $this->logger = $logger;
        $this->StoreService = $StoreService;
    }

    /**
     * initiate the authentication process
     *
     * @return array
     */
    public function auth(): array
    {
        $parameter = $this->StoreService->getSecureParameter(self::APPLICATION);

        $client = new Client();
        try {
            $request = $client->request(
                "post",
                "{$parameter['instanceUrl']}v1/oauth2/token",
                [
                    'headers' => [
                        'Accept' => "application/json",
                        'Accept-Language' => 'en_US',
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'auth' => [
                        $parameter['client_id'],
                        $parameter['client_secret']
                    ],
                    'form_params' => ['grant_type' => 'client_credentials']
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceAuthenticationException::fromClientException($e);
        }
        $response = json_decode($request->getBody(), true);
        return ['accessToken' => $response['access_token'],
            'expires_in' => $response['expires_in']];
    }

    /**
     * check if the existing token is still valid and renew
     *
     * @throws SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function authCheck()
    {
        $token = $this->StoreService->getSecureToken(self::APPLICATION);
        $parameter = $this->StoreService->getSecureParameter(self::APPLICATION);
        $this->instanceUrl = $parameter['instanceUrl'];
        if ($token !== false) {
            $this->logger->info('Paypal token still valid');
            $this->accessToken = $token['accessToken'];
        } else {
            $this->logger->info('Paypal token renew requested');
            $newToken = $this->auth();
            $validity = time() + $newToken['expires_in'];
            $this->StoreService->setSecureToken(self::APPLICATION, $newToken['accessToken'], null, $validity);
            $this->accessToken = $newToken['accessToken'];
        }
        return true;
    }

    /**
     * get all transactions
     *
     * @param $start
     * @param $end
     * @param $type
     * @return \Exception[]|ClientException[]|GuzzleException[]|mixed
     * @throws SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function transactions($start, $end, $type)
    {
        $auth = $this->authCheck();

        $params = [
            'start_date' => $start,
            'end_date' => $end,
            'fields' => 'payer_info,cart_info',
            'transaction_status' => 'S',
            'transaction_type' => $type
        ];
        $client = new Client();
        try {
            $request = $client->request(
                "GET",
                "{$this->instanceUrl}v1/reporting/transactions",
                [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'query' => $params
                ]
            );
        } catch (ClientException | GuzzleException $e) {
            return ['error' => $e];
        }
        $response = json_decode($request->getBody(), true);
        return (array)$response['transaction_details'];
    }
}