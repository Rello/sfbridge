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

namespace OCA\SFbridge\Service;

use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCP\IConfig;
use OCP\Security\ICredentialsManager;

class StoreService
{
    /** @var IConfig */
    protected $config;
    /** @var IUserSession */
    private $UserSession;
    /** @var ICredentialsManager */
    protected $credentialsManager;
    private $userId;
    private $logger;

    const appName = 'sfbridge';

    public function __construct(
        $userId,
        LoggerInterface $logger,
        IConfig $config,
        IUserSession $UserSession,
        ICredentialsManager $credentialsManager
    )
    {
        $this->userId = $userId;
        $this->logger = $logger;
        $this->config = $config;
        $this->UserSession = $UserSession;
        $this->credentialsManager = $credentialsManager;
    }

    //
    // Secure Tokens - they are stored with a validity
    //

    /**
     * get the valid token; will return false when the token validity i > 2hrs
     *
     * @param $application
     * @return false|mixed
     */
    public function getSecureToken($application)
    {
        $user = $this->UserSession->getUser();
        $auth = $this->credentialsManager->retrieve($user->getUID(), self::appName . ':' . $application . 'Token');
        $compare = time() - (60 * 60 * 2);
        if ($auth['validity'] > $compare) {
            return $auth;
        } else {
            return false;
        }
    }

    /**
     * store token with current timestamp
     *
     * @param $application
     * @param $token
     * @param $instanceUrl
     * @return bool
     */
    public function setSecureToken($application, $token, $instanceUrl)
    {
        $user = $this->UserSession->getUser();
        $this->credentialsManager->store($user->getUID(), self::appName . ':' . $application . 'Token', [
            'accessToken' => $token,
            'instanceUrl' => $instanceUrl,
            'validity' => time(),
        ]);
        return true;
    }


    //
    // Get standard connection parameters
    //

    /**
     * get parameters for an application
     *
     * @param $application
     * @return mixed
     */
    public function getSecureParameter($application)
    {
        $user = $this->UserSession->getUser();
        return $this->credentialsManager->retrieve($user->getUID(), self::appName . ':' . $application);
    }

    /**
     * set parameters for an application
     *
     * @param $application
     * @param $parameter
     * @return mixed
     */
    public function setSecureParameter($application, $parameter)
    {
        $user = $this->UserSession->getUser();
        $this->credentialsManager->store($user->getUID(), self::appName . ':' . $application, $parameter);
        return true;
    }

    /**
     * set non-secure user parameter
     * used for the background execution status
     *
     * @param $token
     * @param $value
     * @return mixed
     * @throws \OCP\PreConditionNotMetException
     */
    public function set($token, $value)
    {
        $user = $this->UserSession->getUser();
        $this->config->setUserValue($user->getUID(), 'sfbridge', $token, var_export($value, true));
        return true;
    }

    /**
     * backup; not used
     */
    private function getSecureSalesforce()
    {
        $user = $this->UserSession->getUser();
        $auth = $this->credentialsManager->retrieve($user->getUID(), self::appName . '.' . 'salesforce');
        return $auth;
    }

    /**
     * backup; not used
     */
    private function setSecureSalesforce($client_id, $client_secret, $instanceUrl)
    {
        $user = $this->UserSession->getUser();
        $this->credentialsManager->store($user->getUID(), self::appName . '.' . 'salesforce', [
            'grant_type' => 'password',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $instanceUrl,
            'password' => $instanceUrl,
        ]);
        return true;
    }
}