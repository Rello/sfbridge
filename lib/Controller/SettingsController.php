<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
     * enable or disable the background job scheduling
     *
     * @NoAdminRequired
     * @param bool $background
     * @return DataResponse
     */
    public function background($background = false)
    {
        return new DataResponse($this->StoreService->setParameter('backgroundJob', $background));
    }

	/**
	 * enable or disable the background update scheduling
	 *
	 * @NoAdminRequired
	 * @param bool $backgroundUpdate
	 * @return DataResponse
	 */
	public function backgroundUpdate($backgroundUpdate = false)
	{
		return new DataResponse($this->StoreService->setParameter('backgroundUpdate', $backgroundUpdate));
	}

	/**
     * set Paypal parameters
     *
     * @NoAdminRequired
     * @param $client_id
     * @param $client_secret
     * @param $instanceUrl
     * @param $excludeTypes
     * @return DataResponse
     */
    public function setParameterPaypal($client_id, $client_secret, $instanceUrl, $excludeTypes)
    {
        $parameter = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'instanceUrl' => $instanceUrl,
            'excludeTypes' => $excludeTypes,
        ];
        return new DataResponse($this->StoreService->setSecureParameter('paypal', $parameter));
    }

    /**
     * set Salesforce parameters
     *
     * @NoAdminRequired
     * @param $client_id
     * @param $client_secret
     * @param $username
     * @param $password
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
     * set Bank parameters
     *
     * @NoAdminRequired
     * @param $excludes
     * @param $searchText
     * @param $replaceName
     * @return DataResponse
     */
    public function setParameterBank($excludes, $searchText, $replaceName)
    {
        $parameter = [
            'excludes' => $excludes,
            'searchText' => $searchText,
            'replaceName' => $replaceName,
        ];
        return new DataResponse($this->StoreService->setSecureParameter('bank', $parameter));
    }

	/**
	 * set Bank parameters
	 *
	 * @NoAdminRequired
	 * @param $talkRoom
	 * @param $talkUser
	 * @return DataResponse
	 */
	public function setParameterTalk($talkRoom, $talkUser)
	{
		$this->StoreService->setParameter('talkRoom', $talkRoom);
		$this->StoreService->setParameter('talkUser', $talkUser);

		return new DataResponse(true);
	}

}