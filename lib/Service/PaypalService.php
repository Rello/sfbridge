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

    private $userId;
    private $logger;
    private $StoreService;
    private $accessToken;
    private $instanceUrl;

    const APPLICATION = 'paypal';

    public function __construct(
        $userId,
        LoggerInterface $logger,
        StoreService $StoreService
    )
    {
        $this->userId = $userId;
        $this->logger = $logger;
        $this->StoreService = $StoreService;
    }

    /**
     * get all reports
     *
     * @return mixed
     */
    public function auth()
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
        return ['accessToken' => $response['access_token']];
    }

    private function authCheck()
    {
        $token = $this->StoreService->getSecureToken(self::APPLICATION);
        $parameter = $this->StoreService->getSecureParameter(self::APPLICATION);
        $this->accessToken = $token['accessToken'];
        $this->instanceUrl = $parameter['instanceUrl'];
        if (!$this->accessToken) {
            $newToken = $this->auth();
            $this->StoreService->setSecureToken(self::APPLICATION, $newToken['accessToken'], null);
            $this->accessToken = $newToken['accessToken'];
        }
    }

    /**
     * @return \Exception[]|ClientException[]|GuzzleException[]|mixed
     */
    public function transactions($start, $end, $type)
    {
        $this->authCheck();

        $params = [
            'start_date' => $start,
            'end_date' => $end,
            'fields' => 'payer_info',
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