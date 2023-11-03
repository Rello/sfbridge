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

use OCA\SFbridge\Service\CompareService;
use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class ApiDataController extends ApiController
{
    const UNKNOWN = 9001;

    protected $errors = [];
    private $logger;
    private $userSession;
    private $CompareService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        IUserSession $userSession,
        CompareService $CompareService
    )
    {
        parent::__construct(
            $appName,
            $request,
            'POST'
        );
        $this->logger = $logger;
        $this->userSession = $userSession;
        $this->CompareService = $CompareService;
    }

    /**
     * add data via there database names
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     * @return DataResponse
     * @throws \Exception
     */
    public function addData()
    {
        $file = $this->request->getUploadedFile('file');
        $content = file_get_contents($file['tmp_name']);
        //$this->logger->error(json_encode($content));

        try {
            $return = $this->CompareService->bank($content);
            return $this->requestResponse(
                true,
                Http::STATUS_OK,
                json_encode($return));
        }
        catch (SalesforceException $e) {
            return $this->requestResponse(
                false,
                $e->getCode(),
                json_encode($e->getErrors())
            );
        }
    }

    /**
     * @param bool $success
     * @param int|null $code
     * @param string|null $message
     * @return DataResponse
     */
    protected function requestResponse($success, $code = null, $message = null)
    {
        if (!$success) {
            if ($code === null) {
                $code = self::UNKNOWN;
            }
            $array = [
                'success' => false,
                'error' => ['code' => $code,
                    'message' => $message
                ]
            ];
        } else {
            $array = [
                'success' => true,
                'message' => $message
            ];
        }
        $response = new DataResponse();
        $response->setData($array)->render();
        return $response;
    }

}
