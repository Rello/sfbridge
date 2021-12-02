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

namespace OCA\SFbridge\Notification;

use OCP\Notification\IManager as INotificationManager;
use Psr\Log\LoggerInterface;

class NotificationManager
{
    const NEW_TRANSACTION = 'new_transaction';
    /** @var INotificationManager */
    protected $notificationManager;
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        INotificationManager $notificationManager
    )
    {
        $this->logger = $logger;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @param string $object_type
     * @param int $object_id
     * @param $subject
     * @param array $subject_parameter
     * @param $user_id
     */
    public function triggerNotification($object_type, $object_id, $subject, $subject_parameter, $user_id)
    {
        $notification = $this->notificationManager->createNotification();
        $notification->setApp('sfbridge')
            ->setObject($object_type, $object_id)
            ->setSubject($subject)
            ->setUser($user_id);
        $this->notificationManager->markProcessed($notification);

        $notification = $this->notificationManager->createNotification();
        $notification->setApp('sfbridge')
            ->setDateTime(new \DateTime())
            ->setObject($object_type, $object_id)
            ->setSubject($subject, $subject_parameter)
            ->setUser($user_id);
        $this->notificationManager->notify($notification);
    }
}