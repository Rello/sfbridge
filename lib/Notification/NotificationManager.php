<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Notification;

use OCP\Notification\IManager as INotificationManager;
use Psr\Log\LoggerInterface;
use OCP\App\IAppManager;
use OCP\IGroupManager;
use OCP\IGroup;

class NotificationManager
{
    const NEW_TRANSACTION = 'new_transaction';
    /** @var INotificationManager */
    protected $notificationManager;
    private $logger;
    private $appManager;
    private $groupManager;

    public function __construct(
        LoggerInterface      $logger,
        INotificationManager $notificationManager,
        IAppManager          $appManager,
        IGroupManager        $groupManager
    )
    {
        $this->logger = $logger;
        $this->notificationManager = $notificationManager;
        $this->appManager = $appManager;
        $this->groupManager = $groupManager;
    }

    /**
     * @param string $object_type
     * @param int $object_id
     * @param $subject
     * @param array $subject_parameter
     */
    public function triggerNotification($object_type, $object_id, $subject, $subject_parameter)
    {
        //$this->clearNotifications($object_type, $object_id);
        $users = $this->getUsersToNotify();
        if (!$users) return;

        $notification = $this->notificationManager->createNotification();
        $notification->setApp('sfbridge')
            ->setObject($object_type, $object_id)
            ->setSubject($subject, $subject_parameter);

        if ($this->notificationManager->getCount($notification) === 0) {
            foreach ($users as $uid) {
                $notification->setUser($uid);
                $notification->setDateTime(new \DateTime());
                $this->notificationManager->notify($notification);
            }
        }
    }

    /**
     * Remove notifications
     */
    public function clearNotifications($object_type, $object_id)
    {
        $notification = $this->notificationManager->createNotification();
        try {
            $notification->setApp('sfbridge')
                ->setObject($object_type, $object_id);
        } catch (\InvalidArgumentException $e) {
            return;
        }
        $this->notificationManager->markProcessed($notification);
    }

    private function getUsersToNotify()
    {
        $restrictions = $this->appManager->getAppRestriction('sfbridge');

        if ($restrictions === []) {
            return [];
        }
        $allMembers = [];
        foreach ($restrictions as $groupId) {
            $group = $this->groupManager->get($groupId);
            if ($group instanceof IGroup) {
                foreach ($group->getUsers() as $user) {
                    $allMembers[] = $user->getUID();
                }
            }
        }
        $allMembers = array_unique($allMembers);
        return $allMembers;
    }
}