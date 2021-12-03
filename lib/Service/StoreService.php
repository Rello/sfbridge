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
    // Secure Token - any application
    //

    public function getSecureToken($application)
    {
        $user = $this->UserSession->getUser();
        $auth = $this->credentialsManager->retrieve($user->getUID(), self::appName . ':' . $application . 'Token');
        $compare = time() - (60 * 60);
        if ($auth['validity'] > $compare) {
            return $auth;
        } else {
            return false;
        }

    }

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
    // get all parameters
    //

    public function getSecureParameter($application)
    {
        $user = $this->UserSession->getUser();
        return $this->credentialsManager->retrieve($user->getUID(), self::appName . ':' . $application);
    }

    public function setSecureParameter($application, $parameter)
    {
        $user = $this->UserSession->getUser();
        $this->credentialsManager->store($user->getUID(), self::appName . ':' . $application, $parameter);
        return true;
    }



    //
    // Secure Salesforce
    //

    public function getSecureSalesforce()
    {
        $user = $this->UserSession->getUser();
        $auth = $this->credentialsManager->retrieve($user->getUID(), self::appName . '.' . 'salesforce');
        return $auth;
    }


    public function setSecureSalesforce($client_id, $client_secret, $instanceUrl)
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
