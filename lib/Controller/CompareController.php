<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Controller;

use OCA\SFbridge\Service\CompareService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class CompareController extends Controller
{
    private $logger;
    private $CompareService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        CompareService $CompareService
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->CompareService = $CompareService;
    }

    /**
     * start the compare process
     *
     * @NoAdminRequired
     * @param $update
     * @param $from
     * @param $to
     * @return DataResponse
     */
    public function compare($update, $from, $to): DataResponse
    {
		$return = $this->CompareService->paypal($update, $from, $to);
        return new DataResponse($return['content'], $return['status']);
    }

}