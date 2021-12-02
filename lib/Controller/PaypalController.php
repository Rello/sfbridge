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
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function auth()
    {
        return new DataResponse($this->PaypalService->auth());
    }

    /**
     * get all reports
     *
     * @NoAdminRequired
     * @return DataResponse
     */
    public function transactions()
    {
        $start = '2021-11-04T00:00:00-0700';
        $end = '2021-11-10T23:59:59-0700';

        return new DataResponse($this->PaypalService->transactions($start, $end, null));
    }

}