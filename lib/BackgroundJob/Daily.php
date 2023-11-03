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

namespace OCA\SFbridge\BackgroundJob;

use GuzzleHttp\Exception\GuzzleException;
use OCA\SFbridge\Service\CompareService;
use OCA\SFbridge\Service\StoreService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

class Daily extends TimedJob
{
    private $logger;
    private $CompareService;
    private $StoreService;

    public function __construct(ITimeFactory $time,
                                LoggerInterface $logger,
                                CompareService $CompareService,
                                StoreService $StoreService
    )
    {
        parent::__construct($time);
        $this->setInterval((60 * 60 * 2) - 120); // 2 minutes because exact times would drift to the next cron execution
        $this->logger = $logger;
        $this->CompareService = $CompareService;
        $this->StoreService = $StoreService;
    }

    /**
     * @throws GuzzleException
     */
    public function run($arguments)
    {
        try {
            $scheduled = $this->StoreService->getBackground();
            if ($scheduled) {
                $from = date('Y-m-d\T00:00', strtotime("-3 days"));
                $to = date('Y-m-d\T00:00', strtotime("+1 day"));
                $this->CompareService->paypal(false, $from, $to, true);
            }
        } catch (\Exception $e) {
            // no action
        }
    }
}