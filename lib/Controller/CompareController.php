<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Controller;

use OCA\SFbridge\Service\CompareService;
use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function compare($update, $from, $to): DataResponse
    {
        try {
            $return = $this->CompareService->paypal($update, $from, $to);
			if ($return) {
				$status = Http::STATUS_OK;
			} else {
				$status = Http::STATUS_INTERNAL_SERVER_ERROR;
				$return = 'Please configure the Talk Settings';
			}
        }
        catch (SalesforceException $e) {
            $return = "exception: " . json_encode($e->getErrors());
            $status = $e->getCode();
        }
        return new DataResponse($return, $status);
    }

}