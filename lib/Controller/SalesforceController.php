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