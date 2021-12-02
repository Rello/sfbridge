<?php
declare(strict_types=1);
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

use InvalidArgumentException;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\AlreadyProcessedException;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use Psr\Log\LoggerInterface;

class Notifier implements INotifier
{

    /** @var IFactory */
    protected $l10nFactory;

    /** @var IUserManager */
    protected $userManager;

    /** @var IURLGenerator */
    protected $urlGenerator;

    private $logger;

    public function __construct(IFactory $l10nFactory,
                                IUserManager $userManager,
                                LoggerInterface $logger,
                                IURLGenerator $urlGenerator)
    {
        $this->l10nFactory = $l10nFactory;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Identifier of the notifier, only use [a-z0-9_]
     *
     * @return string
     * @since 17.0.0
     */
    public function getID(): string
    {
        return 'sfbridge';
    }

    /**
     * Human readable name describing the notifier
     *
     * @return string
     * @since 17.0.0
     */
    public function getName(): string
    {
        return $this->l10nFactory->get('sfbridge')->t('New Transactions');
    }

    /**
     * @param INotification $notification
     * @param string $languageCode The code of the language that should be used to prepare the notification
     * @return INotification
     * @throws InvalidArgumentException When the notification was not prepared by a notifier
     * @throws AlreadyProcessedException When the notification is not needed anymore and should be deleted
     * @since 9.0.0
     */
    public function prepare(INotification $notification, string $languageCode): INotification
    {
        if ($notification->getApp() !== 'sfbridge') {
            // Not my app => throw
            throw new InvalidArgumentException('Unknown app');
        }

        // Read the language from the notification
        $l = $this->l10nFactory->get('sfbridge', $languageCode);
        $parsedSubject = '';

        switch ($notification->getObjectType()) {
            case NotificationManager::NEW_TRANSACTION:
                $parsedSubject = $l->t("{subject} new transactions available in Paypal");
                break;
            default: // legacy due to switch to subject field filled with an id for notification removal
                $parsedSubject = '';
        }

        $link = $this->urlGenerator->linkToRouteAbsolute('sfbridge.page.index') . '#/r/' . $notification->getObjectId();

        $parameters = $notification->getSubjectParameters();
        $notification->setRichSubject(
            $parsedSubject,
            [
                'subject' => [
                    'type' => 'highlight',
                    'id' => $notification->getObjectId(),
                    'name' => $parameters['subject'],
                ],
            ]
        );
        $notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('sfbridge', 'app-dark.svg')));
        $this->setParsedSubjectFromRichSubject($notification);
        return $notification;
    }

    // This is a little helper function which automatically sets the simple parsed subject
    // based on the rich subject you set.
    protected function setParsedSubjectFromRichSubject(INotification $notification)
    {
        $placeholders = $replacements = [];
        foreach ($notification->getRichSubjectParameters() as $placeholder => $parameter) {
            $placeholders[] = '{' . $placeholder . '}';
            if ($parameter['type'] === 'file') {
                $replacements[] = $parameter['path'];
            } else {
                $replacements[] = $parameter['name'];
            }
        }

        $notification->setParsedSubject(str_replace($placeholders, $replacements, $notification->getRichSubject()));
    }

}