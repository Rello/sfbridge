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

namespace OCA\SFbridge\BackgroundJob;

use GuzzleHttp\Exception\GuzzleException;
use OCA\SFbridge\Service\CompareService;
use OCA\SFbridge\Service\StoreService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCP\IUserManager;

class Daily extends TimedJob
{
    private $logger;
    private $CompareService;
    private $StoreService;
    /** @var IUserSession */
    private $UserSession;
    /** @var IUserManager */
    private $UserManager;

    public function __construct(ITimeFactory $time,
                                LoggerInterface $logger,
                                CompareService $CompareService,
                                StoreService $StoreService,
                                IUserSession $UserSession,
                                IUserManager $UserManager
    )
    {
        parent::__construct($time);
        $this->setInterval((60 * 60 * 2) - 120); // 2 minutes because exact times would drift to the next cron execution
        $this->logger = $logger;
        $this->CompareService = $CompareService;
        $this->StoreService = $StoreService;
        $this->UserSession = $UserSession;
        $this->UserManager = $UserManager;
    }

    /**
     * @throws GuzzleException
     */
    public function run($arguments)
    {
        try {
            $user = $this->UserManager->get('admin');
            $this->UserSession->setUser($user);
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