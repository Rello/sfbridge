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

use OCA\SFbridge\Service\StoreService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller
{
    private $logger;
    private $StoreService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        StoreService $StoreService
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->StoreService = $StoreService;
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function background($background = false)
    {
        return new DataResponse($this->StoreService->set('background', $background, false));
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function setParameterPaypal($client_id, $client_secret, $instanceUrl)
    {
        $parameter = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'instanceUrl' => $instanceUrl,
        ];
        return new DataResponse($this->StoreService->setSecureParameter('paypal', $parameter));
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function getParameterPaypal()
    {
        return new DataResponse($this->StoreService->getSecureParameter('paypal'));
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function setParameterSalesforce($client_id, $client_secret, $username, $password)
    {
        $parameter = [
            'grant_type' => 'password',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $username,
            'password' => $password,
        ];
        return new DataResponse($this->StoreService->setSecureParameter('salesforce', $parameter));
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function getParameterSalesforce()
    {
        return new DataResponse($this->StoreService->getSecureParameter('salesforce'));
    }

}