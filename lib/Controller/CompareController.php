<?php
/**
 * Salesforce Bridge
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <sfbridge@scherello.de>
 * @copyright 2021-2023 Marcel Scherello
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
            $status = Http::STATUS_OK;
        }
        catch (SalesforceException $e) {
            $return = "exception: " . json_encode($e->getErrors());
            $status = $e->getCode();
        }
        return new DataResponse($return, $status);
    }

}