<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\AppInfo;

use OCA\SFbridge\Notification\Notifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'sfbridge';

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        $this->registerNotifications();
    }

    public function boot(IBootContext $context): void
    {
    }

    protected function registerNotifications(): void
    {
        $notificationManager = \OC::$server->get(\OCP\Notification\IManager::class);
        $notificationManager->registerNotifierService(Notifier::class);
    }
}