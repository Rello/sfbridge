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

namespace OCA\SFbridge\Controller;

use OCA\SFbridge\Service\CompareService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Constants;
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
        $transactions = str_getcsv($content, "\n");  // split rows
        $transactions = array_slice($transactions, 1); // remove header
        $return = $this->CompareService->bank($transactions);

        return $this->requestResponse(
            true,
            Http::STATUS_OK,
            json_encode($return));
    }

    /**
     * create one dimensional transaction records with just the required fields
     *
     * @param $transactions
     * @return array
     */
    private function harmonizeTransactions($transactions): array
    {
        $transactionsLined = array();
        foreach ($transactions as $transaction) {
            $row = str_getcsv($transaction, ';');

            $line['transactionId'] = hash('md5', $row[0].$row[3].$row[4].$row[5].$row[7]);
            $line['transactionType'] = null;
            $line['transactionDate'] = $row[0];
            $line['transactionAmount'] = $row[7];
            $line['transactionFee'] = null;
            $line['transactionNote'] = $row[4];

            $line['payerEmail'] = null;
            $line['payerGivenName'] = null;
            $line['payerSurName'] = null;
            $line['payerAlternateName'] = $row[3];
            $line['payerIBAN'] = $row[5];

            $line['itemCode'] = null;
            $line['paymentMethod'] = 'Bank';

            $transactionsLined[] = $line;
        }
        return $transactionsLined;
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
    // curl -u Admin:2sroW-SxRcK-AmdsF-RYMJ5-CKSyf -d '{"dimension1": "x", "dimension2": "x", "dimension3": "333,3"}' -X POST -H "Content-Type: application/json" http://ncxx/nextcloud/apps/analytics/api/1.0/adddata/158
    // curl -u Admin:2sroW-SxRcK-AmdsF-RYMJ5-CKSyf -d '[{"Spalte 1": "x", "Spalte 2": "x", "toller wert": "333,3"}]' -X POST -H "Content-Type: application/json" http://ncxx/nextcloud/apps/analytics/api/2.0/adddata/158
    // curl -u Admin:2sroW-SxRcK-AmdsF-RYMJ5-CKSyf -d '{"data":[{"Spalte 1": "a", "Spalte 2": "a", "toller wert": "1"}, {"dimension1": "b", "dimension2": "b", "value": "2"}]}' -X POST -H "Content-Type: application/json" http://ncxx/nextcloud/apps/analytics/api/2.0/adddata/158

    // curl -u Admin:2sroW-SxRcK-AmdsF-RYMJ5-CKSyf -d '{"delete":[{"dimension1": "a", "dimension2": "a"}]}' -X POST -H "Content-Type: application/json" http://ncxx/nextcloud/apps/analytics/api/2.0/deletedata/158
    // curl -u Admin:2sroW-SxRcK-AmdsF-RYMJ5-CKSyf -d '{"del":[{"dimension1": "a", "dimension2": "a"}]}' -X POST -H "Content-Type: application/json" http://ncxx/nextcloud/apps/analytics/api/2.0/deletedata/158
    // curl -u admin:cZMLJ-DTpYA-Ci5QM-M4ZRy-KBcTp -X GET -H "Content-Type: application/json" https://ncxx/nextcloud/apps/analytics/api/3.0/data/52 --insecure
    // curl -u admin:i6iX2-DdLX6-TT7T7-GFi9c-GfSec -X GET -H "Content-Type: application/json" https://nc22/nextcloud/apps/analytics/api/3.0/data/21 --insecure
    // curl -u admin:cZMLJ-DTpYA-Ci5QM-M4ZRy-KBcTp -X GET -H "Content-Type: application/json" https://ncxx/nextcloud/apps/analytics/api/3.0/reports --insecure
}
