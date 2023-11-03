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

namespace OCA\SFbridge\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Services\IInitialState;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * Controller class for main page.
 */
class PageController extends Controller
{
    /** @var IConfig */
    protected $config;
    /** @var IUserSession */
    private $userSession;
    /** @var IInitialState */
    protected $initialState;
    private $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        LoggerInterface $logger,
        IUserSession $userSession,
        IConfig $config,
        IInitialState $initialState
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->userSession = $userSession;
        $this->initialState = $initialState;
        $this->config = $config;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        $user = $this->userSession->getUser();
        $this->initialState->provideInitialState(
            'background',
            $this->config->getUserValue($user->getUID(), 'sfbridge', 'background', false)
        );
        $params = array();
        return new TemplateResponse($this->appName, 'main', $params);
    }
}