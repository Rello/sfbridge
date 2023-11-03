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

namespace OCA\SFbridge\Settings;

use OCA\SFbridge\Service\StoreService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IUserSession;
use OCP\Settings\IDelegatedSettings;
use OCP\IConfig;

class Admin implements IDelegatedSettings
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
     * @throws \Exception
     * @since 9.1
     */
    public function getForm()
    {
        $paypal = $this->StoreService->getSecureParameter('paypal');
        $salesforce = $this->StoreService->getSecureParameter('salesforce');
        $bank = $this->StoreService->getSecureParameter('bank');

        $this->initialState->provideInitialState(
            'background',
            $this->StoreService->getBackground()
        );

        $parameters = [
            'paypal_client_id' => $paypal['client_id']?? null,
            'paypal_client_secret' => $paypal['client_secret']?? null,
            'paypal_instanceUrl' => $paypal['instanceUrl']?? null,

            'salesforce_client_id' => $salesforce['client_id']?? null,
            'salesforce_client_secret' => $salesforce['client_secret']?? null,
            'salesforce_username' => $salesforce['username']?? null,
            'salesforce_password' => $salesforce['password']?? null,

            'bank_replaceName' => $bank['replaceName']?? null,
            'bank_excludes' => $bank['excludes']?? null,
            'bank_searchText' => $bank['searchText']?? null,
        ];
        return new TemplateResponse('sfbridge', 'settings/admin', $parameters, '');
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     * @since 9.1
     */
    public function getSection()
    {
        return 'sfbridge';
    }

    public function getName(): ?string {
        return null;
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

    public function getAuthorizedAppConfig(): array {
        return [];
    }
}
