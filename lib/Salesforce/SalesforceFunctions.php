<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Salesforce;

use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class SalesforceFunctions
{

    /**
     * @var string
     */
    const apiVersion = "v48.0";

    /**
     * @var string
     */
    protected $instanceUrl;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $apiVersion = "v48.0";

    /**
     * SalesforceFunctions constructor.
     *
     * @param null $instanceUrl
     * @param null $accessToken
     * @param string $apiVersion Default API version is used from constant
     */
    public function __construct($instanceUrl = null, $accessToken = null, $apiVersion = self::apiVersion)
    {
        $this->apiVersion = $apiVersion;

        if ($instanceUrl) {
            $this->setInstanceUrl($instanceUrl);
        }

        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }

    /**
     * @return string
     */
    public function getInstanceUrl()
    {
        return $this->instanceUrl;
    }

    /**
     * @param string $instanceUrl
     */
    public function setInstanceUrl($instanceUrl)
    {
        $this->instanceUrl = $instanceUrl;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * @param string $query
     * @return mixed Array or exception
     * @throws GuzzleException
     */
    public function query($query)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/query";

        $client = new Client();
        try {
            $request = $client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}"
                    ],
                    'query' => [
                        'q' => $query
                    ]
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        return json_decode($request->getBody(), true);
    }

    /**
     * @param $object
     * @param $field
     * @param $id
     * @return mixed
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function retrieve($object, $field, $id)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/{$field}/{$id}";

        $client = new Client();

        try {
            $request = $client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                        'Content-type' => 'application/json'
                    ],
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        $status = $request->getStatusCode();

        if ($status !== 200) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return json_decode($request->getBody(), true);
    }

    /**
     * @param $object
     * @param $data
     * @return mixed
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function create($object, $data)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/";

        $client = new Client();

        try {
            $request = $client->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                        'Content-type' => 'application/json'
                    ],
                    'json' => $data
                ]
            );

            $status = $request->getStatusCode();
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        if ($status !== 201) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        $response = json_decode($request->getBody(), true);
        return $response["id"];
    }

    /**
     * @param $object
     * @param $id
     * @param $data
     * @return int
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function update($object, $id, $data)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/{$id}";

        $client = new Client();

        try {
            $request = $client->request(
                'PATCH',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth $this->accessToken",
                        'Content-type' => 'application/json'
                    ],
                    'json' => $data
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        $status = $request->getStatusCode();

        if ($status !== 204) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $status;
    }

    /**
     * @param $object
     * @param $field
     * @param $id
     * @param $data
     * @return int
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function upsert($object, $field, $id, $data)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/{$field}/{$id}";

        $client = new Client();

        try {
            $request = $client->request(
                'PATCH',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                        'Content-type' => 'application/json'
                    ],
                    'json' => $data
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        $status = $request->getStatusCode();

        if ($status !== 204 && $status !== 201) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $status;
    }

    /**
     * @param $object
     * @param $id
     * @return bool
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function delete($object, $id)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/{$id}";

        try {
            $client = new Client();
            $request = $client->request(
                'DELETE',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                    ]
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        $status = $request->getStatusCode();

        if ($status !== 204) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return true;
    }

    /**
     * @param $object
     * @return mixed
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function describe($object)
    {
        $url = "{$this->instanceUrl}/services/data/{$this->apiVersion}/sobjects/{$object}/describe/";

        $client = new Client();

        try {
            $request = $client->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                        'Content-type' => 'application/json'
                    ],
                ]
            );
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        $status = $request->getStatusCode();

        if ($status !== 200) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return json_decode($request->getBody(), true);
    }

    /**
     * @param string $customEndpoint all behind /services/
     * @param $data
     * @param int $successStatusCode
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function customEndpoint($customEndpoint, $data, $successStatusCode = 200)
    {
        /* customEndpoint could be all behind /services/ */
        $url = "{$this->instanceUrl}/services/{$customEndpoint}";

        $client = new Client();

        try {
            $request = $client->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'Authorization' => "OAuth {$this->accessToken}",
                        'Content-type' => 'application/json'
                    ],
                    'json' => $data
                ]
            );

            $status = $request->getStatusCode();
        } catch (ClientException $e) {
            throw SalesforceException::fromClientException($e);
        }

        if ($status !== $successStatusCode) {
            throw new SalesforceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $request;
    }
}
