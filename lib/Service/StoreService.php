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

namespace OCA\SFbridge\Service;

use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCP\IConfig;
use OCP\Security\ICrypto;
use OCP\Security\ICredentialsManager;

class StoreService
{
    /** @var IConfig */
    protected $config;
    /** @var IUserSession */
    private $UserSession;
    /** @var ICredentialsManager */
    protected $credentialsManager;
    /** @var ICrypto */
    protected $crypto;
    private $userId;
    private $logger;

    const appName = 'sfbridge';

    public function __construct(
        $userId,
        LoggerInterface $logger,
        IConfig $config,
        IUserSession $UserSession,
        ICrypto $crypto,
        ICredentialsManager $credentialsManager
    )
    {
        $this->userId = $userId;
        $this->logger = $logger;
        $this->config = $config;
        $this->UserSession = $UserSession;
        $this->credentialsManager = $credentialsManager;
        $this->crypto = $crypto;
    }

    /**
     * Secure token stored with a validity
     * get the valid token; will return false when the token validity i > 2hrs
     *
     * @param $application
     * @return false|mixed
     * @throws \Exception
     */
    public function getSecureToken($application)
    {
        $value = $this->config->getAppValue(self::appName, $application . 'Token');
        if (!$value) return false;
        $auth = json_decode($this->crypto->decrypt($value), true);

        if (isset($auth['validity']) && (int)$auth['validity'] > time()) {
            return $auth;
        } else {
            return false;
        }
    }

    /**
     * Secure token stored with a validity
     * set the token with current timestamp + 2hrs
     *
     * @param $application
     * @param $token
     * @param $instanceUrl
     * @param $validity
     * @return bool
     */
    public function setSecureToken($application, $token, $instanceUrl, $validity = false)
    {
        if (!$validity) {
            $validity = time() + (60 * 60 * 2);
        }

        $value = [
            'accessToken' => $token,
            'instanceUrl' => $instanceUrl,
            'validity' => $validity,
        ];

        $encrypted = $this->crypto->encrypt(json_encode($value));
        $this->config->setAppValue(self::appName, $application . 'Token', $encrypted);
        return true;
    }

    /**
     * Connection parameters like secrets and passwords
     * get parameters for an application
     *
     * @param $application
     * @return mixed
     * @throws \Exception
     */
    public function getSecureParameter($application)
    {
        $value = $this->config->getAppValue(self::appName, $application);
        if (!$value) return false;
        return json_decode($this->crypto->decrypt($value), true);
    }

    /**
     * Connection parameters like secrets and passwords
     * set parameters for an application
     *
     * @param $application
     * @param $parameter
     * @return mixed
     */
    public function setSecureParameter($application, $parameter)
    {
        $encrypted = $this->crypto->encrypt(json_encode($parameter));
        $this->config->setAppValue(self::appName, $application, $encrypted);
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
    public function setBackground($value)
    {
        $this->config->setAppValue(self::appName, 'backgroundJob', $value);
        return true;
    }

    public function getBackground()
    {
        return $this->config->getAppValue(self::appName, 'backgroundJob');
    }
}