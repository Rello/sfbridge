<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
        $this->setInterval(60 * 60);
        $this->logger = $logger;
        $this->CompareService = $CompareService;
        $this->StoreService = $StoreService;
    }

    /**
     * @throws GuzzleException
     */
    public function run($argument)
    {
        try {
            $scheduled = $this->StoreService->getParameter('backgroundJob');
			$update = $this->StoreService->getParameter('backgroundUpdate') == 1 ? 'true' : 'false';
            if ($scheduled) {
				$this->logger->info("Running Salesforce Bridge");
                $from = date('Y-m-d\T00:00', strtotime("-3 days"));
                $to = date('Y-m-d\T00:00', strtotime("+1 day"));
                $this->CompareService->paypal($update, $from, $to, true);
            }
        } catch (\Exception $e) {
            // no action
        }
    }
}