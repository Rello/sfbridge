<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Controller;

use OCA\SFbridge\Service\SalesforceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SalesforceController extends Controller
{
    private $logger;
    private $SalesforceService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        SalesforceService $SalesforceService
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->SalesforceService = $SalesforceService;
    }

    /**
     * initiate the authentication process
     * used for testing
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function auth()
    {
        return new DataResponse($this->SalesforceService->auth());
    }

    /**
     * create new blank report
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function contactIndex()
    {
        return new DataResponse($this->SalesforceService->contactIndex());
    }

    /**
     * create new blank report
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function contactSearch($search)
    {
        return new DataResponse($this->SalesforceService->contactSearch('Email', $search));
    }

    /**
     * create new blank report
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function contactCreate($givenName, $surName, $alternateName, $email)
    {
        return new DataResponse($this->SalesforceService->contactCreate($givenName, $surName, $alternateName, $email));
    }

    /**
     * create new blank report
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function opportunitySearch($search)
    {
        return new DataResponse($this->SalesforceService->opportunitySearch('ContactId', $search));
    }

    public function opportunityCreate($contactId, $name, $accountId, $amount)
    {
        return new DataResponse($this->SalesforceService->opportunityCreate($contactId, $name, $accountId, $amount));
    }

    /**
     * create new blank report
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function paymentSearch()
    {
        return new DataResponse($this->SalesforceService->paymentSearch());
    }


}