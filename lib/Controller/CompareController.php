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
     * @return DataResponse
     */
    public function compare($update, $from, $to): DataResponse
    {
        return new DataResponse($this->CompareService->compare($update, $from, $to));
    }

}