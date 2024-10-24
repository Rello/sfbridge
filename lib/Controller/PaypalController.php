<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Controller;

use OCA\SFbridge\Service\PaypalService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class PaypalController extends Controller
{
    private $logger;
    private $PaypalService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        PaypalService $PaypalService
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->PaypalService = $PaypalService;
    }

    /**
     * initiate the authentication process
     * used for testing
     *
     * @NoAdminRequired
     * @return DataResponse
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function auth(): DataResponse
    {
        return new DataResponse($this->PaypalService->auth());
    }

    /**
     * get all transactions
     * used for testing
     *
     * @NoAdminRequired
     * @return DataResponse
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function transactions(): DataResponse
    {
        $start = '2021-12-06T00:00:00-0700';
        $end = '2021-12-10T23:59:59-0700';
        return new DataResponse($this->PaypalService->transactions($start, $end, null));
    }

}