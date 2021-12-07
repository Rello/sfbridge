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

namespace OCA\SFbridge\Settings;

use OCA\SFbridge\Service\StoreService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IUserSession;
use OCP\Settings\ISettings;
use OCP\IConfig;

class Personal implements ISettings
{
    private $userId;
    /** @var IConfig */
    protected $config;
    private $StoreService;
    /** @var IUserSession */
    private $userSession;
    /** @var IInitialState */
    protected $initialState;

    public function __construct(
        $userId,
        IConfig $config,
        StoreService $StoreService,
        IUserSession $userSession,
        IInitialState $initialState
    )
    {
        $this->userId = $userId;
        $this->config = $config;
        $this->StoreService = $StoreService;
        $this->userSession = $userSession;
        $this->initialState = $initialState;
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     * @since 9.1
     */
    public function getForm()
    {
        $paypal = $this->StoreService->getSecureParameter('paypal');
        $salesforce = $this->StoreService->getSecureParameter('salesforce');

        $user = $this->userSession->getUser();
        $this->initialState->provideInitialState(
            'background',
            $this->config->getUserValue($user->getUID(), 'sfbridge', 'background', false)
        );

        $parameters = [
            'paypal_client_id' => $paypal['client_id']?? null,
            'paypal_client_secret' => $paypal['client_secret']?? null,
            'paypal_instanceUrl' => $paypal['instanceUrl']?? null,

            'salesforce_client_id' => $salesforce['client_id']?? null,
            'salesforce_client_secret' => $salesforce['client_secret']?? null,
            'salesforce_username' => $salesforce['username']?? null,
            'salesforce_password' => $salesforce['password']?? null,
        ];
        return new TemplateResponse('sfbridge', 'settings/personal', $parameters, '');
    }

    /**
     * Print config section (ownCloud 10)
     *
     * @return TemplateResponse
     */
    public function getPanel()
    {
        return $this->getForm();
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     * @since 9.1
     */
    public function getSection()
    {
        return 'sfbridge';
    }

    /**
     * Get section ID (ownCloud 10)
     *
     * @return string
     */
    public function getSectionID()
    {
        return 'sfbridge';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     * @since 9.1
     */
    public function getPriority()
    {
        return 10;
    }
}
