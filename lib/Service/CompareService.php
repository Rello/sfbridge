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

use OCA\SFbridge\Notification\NotificationManager;
use Psr\Log\LoggerInterface;

class CompareService
{
    private $logger;
    private $PaypalService;
    private $SalesforceService;
    private $StoreService;
    private $NotificationManager;
    private $update = false;
    private $transactionsCount = 0;
    private $transactionsNewCount = 0;
    private $contactsNewCount = 0;
    private $opportunitiesNewCount = 0;
    private $opportunitiesUpdateCount = 0;
    private $campaignCount = 0;

    public function __construct(
        LoggerInterface $logger,
        PaypalService $PaypalService,
        NotificationManager $NotificationManager,
        SalesforceService $SalesforceService,
        StoreService $StoreService
    )
    {
        $this->logger = $logger;
        $this->PaypalService = $PaypalService;
        $this->NotificationManager = $NotificationManager;
        $this->SalesforceService = $SalesforceService;
        $this->StoreService = $StoreService;
    }

    /**
     * get paypal transactions and start the compare process
     *
     * @param $update
     * @param $from
     * @param $to
     * @param bool $isBackgroundJob
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function paypal($update, $from, $to, $isBackgroundJob = false): array
    {
        $start = $from . ':00-0000';
        $end = $to . ':00-0000';

        if ($update === 'true') {
            $this->update = true;
        }

        // get paypal transactions
        $transactions = $this->PaypalService->transactions($start, $end, null);
        $transactionsLined = $this->harmonizePaypalTransactions($transactions);

        return $this->processTransactions($transactions, $transactionsLined, $isBackgroundJob);
    }

    /**
     * get paypal transactions and start the compare process
     *
     * @param $update
     * @param $from
     * @param $to
     * @param bool $isBackgroundJob
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function bank($transactions, $isBackgroundJob = false)
    {
        $this->update = true;

        $transactionsLined = $this->harmonizeBankTransactions($transactions);
        $transactionsLined = $this->excludeBankTransactions($transactionsLined);
        $transactionsLined = $this->replaceBankTransactions($transactionsLined);

        //$this->logger->error(json_encode($transactionsLined));
        //return json_encode($transactionsLined);
        return $this->processTransactions($transactions, $transactionsLined, $isBackgroundJob)['counts'];
    }

    /**
     * Process transactions
     *
     * @param $transactions
     * @param $transactionsLined
     * @param bool $isBackgroundJob
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function processTransactions($transactions, $transactionsLined, $isBackgroundJob = false): array
    {
        $this->transactionsCount = count($transactionsLined);

        // get existing Saleforce payment IDs
        $transactionIds = array_column($transactionsLined, 'transactionId');
        $existingPayments = $this->SalesforceService->paymentsByReference($transactionIds);

        // compare => keep new transactions which are not yet known
        $transactionsNew = $this->filterTransaction($transactionsLined, $existingPayments);
        $this->transactionsNewCount = count($transactionsNew);

        // compare for existing contact
        // create missing contacts
        $validateContacts = $this->validateContacts($transactionsNew);
        $transactionsNew = $validateContacts['transactions'];

        // check if transaction is linked to a campaign
        $getCampaigns = $this->getCampaigns($transactionsNew);
        $transactionsNew = $getCampaigns['transactions'];

        // compare for existing opportunitiy
        // create opportunity
        $validateOpportunities = $this->validateOpportunities($transactionsNew);
        $transactionsNew = $validateOpportunities['transactions'];

        if ($this->transactionsNewCount !== 0 && $isBackgroundJob) {
            $this->NotificationManager->triggerNotification(NotificationManager::NEW_TRANSACTION, 0, $this->transactionsNewCount, ['subject' => $this->transactionsNewCount], 'admin');
        }

        return [
            'counts' => [
                'Transactions in timeframe' => $this->transactionsCount,
                '-> new transactions' => $this->transactionsNewCount,
                '-> new contacts' => $this->contactsNewCount,
                '-> new opportunities' => $this->opportunitiesNewCount,
                '-> updated pledges/opportunities' => $this->opportunitiesUpdateCount,
            ],
            'all transactions' => $transactions,
            'new transactions' => $transactionsNew,
            'new contacts' => $validateContacts['contacts'],
            'new opportunities' => $validateOpportunities['opportunities'],
            'updated opportunities' => $validateOpportunities['opportunitiesUpdate'],
        ];
    }

    /**
     * validate opportunities
     * check for existing opportunities for the Paypal transaction id
     * If no opportunity is existing, a new one is created
     *
     * @param $transactionsNew
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function validateOpportunities($transactionsNew): array
    {
        $opportunitiesNew = $opportunitiesUpdate = array();

        foreach ($transactionsNew as $transaction) {
            // search for existing Opp: contactId & amount & status "Pledged"
            $opportunityPledgeId = $this->SalesforceService->opportunityPledgeSearch($transaction['contactId'], $transaction['transactionAmount']);

            if ($opportunityPledgeId) {
                // Opportunity is a recurring pledge. Update Status "Closed Won"
                $this->logger->info('Pledge to be updated: '. $opportunityPledgeId['Name'].$opportunityPledgeId['Id']);
                $this->opportunitiesUpdateCount++;
                array_push($opportunitiesUpdate, $opportunityPledgeId['Name']);
                if ($this->update) {
                    $this->SalesforceService->opportunityPledgeUpdate($opportunityPledgeId['Id'], $transaction['transactionDate']);
                    $paymentId = $this->SalesforceService->paymentByOpportunityId($opportunityPledgeId['Id']);
                    $this->SalesforceService->paymentUpdateReference($paymentId, $transaction['transactionId'], $transaction['paymentMethod']);
                    $this->SalesforceService->allocationCreate($opportunityPledgeId['Id'],$transaction['transactionFee']);
                }
            } else {
                // create new Opp
                $this->opportunitiesNewCount++;
                array_push($opportunitiesNew, $transaction['payerAlternateName'] . ' ' . $transaction['transactionAmount'] . ' ' . $transaction['transactionDate']);
                if ($this->update) {
                    $this->SalesforceService->opportunityCreate($transaction['contactId']
                        , $transaction['payerAlternateName']
                        , $transaction['accountId']
                        , $transaction['transactionAmount']
                        , $transaction['transactionFee']
                        , $transaction['transactionDate']
                        , $transaction['transactionId']
                        , $transaction['isNewContact']
                        , $transaction['campaignId'] ?? null
                        , $transaction['transactionNote']
                        , $transaction['paymentMethod']
                    );
                }
            }
        }
        return [
            'transactions' => $transactionsNew,
            'opportunities' => $opportunitiesNew,
            'opportunitiesUpdate' => $opportunitiesUpdate
        ];
    }

    /**
     * validate contacts
     * check for existing contacts by the Paypal email
     * If no contact is existing, a new one is created
     *
     * @param $transactionsNew
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function validateContacts($transactionsNew): array
    {
        $contactsNew = array();
        foreach ($transactionsNew as &$transaction) {
            if ($transaction['payerEmail']) {
                $contact = $this->SalesforceService->contactSearch('Email', $transaction['payerEmail']);
            } else {
                $contact = $this->SalesforceService->contactSearch('Name', $transaction['payerAlternateName']);
                $this->logger->info('Search by AlternateName: '.$transaction['payerAlternateName']);
                $this->logger->info('Number of matches: '.$contact['totalSize']);
            }
            //$contact['totalSize'] = 0;
            if ($contact['totalSize'] === 0) {
                $this->contactsNewCount++;
                if ($this->update) {
                    $this->logger->info('New Contact to be created: '.$transaction['payerGivenName'] . '-' . $transaction['payerSurName'] . '-' . $transaction['payerAlternateName']);
                    $newContact = $this->SalesforceService->contactCreate($transaction['payerGivenName'], $transaction['payerSurName'], $transaction['payerAlternateName'], $transaction['payerEmail']);
                    $transaction['contactId'] = $newContact['contactId'];
                    $transaction['accountId'] = $newContact['accountId'];
                    $transaction['isNewContact'] = true;
                } else {
                    // set dummy values to make the simulation of opportunities "not find" anything existing
                    $transaction['contactId'] = '000000000000000000';
                    $transaction['accountId'] = '000000000000000000';
                }
                // plain texts for the info text box
                array_push($contactsNew, $transaction['payerAlternateName'] . ' ' . $transaction['payerEmail']);
            } else {
                $transaction['contactId'] = $contact['records'][0]['Id'];
                $transaction['accountId'] = $contact['records'][0]['AccountId'];
                $transaction['isNewContact'] = false;
            }
        }
        return [
            'transactions' => $transactionsNew,
            'contacts' => $contactsNew
        ];
    }

    /**
     * get campaigns if the paypal payment has a cart item
     *
     * @param $transactionsNew
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function getCampaigns($transactionsNew): array
    {
        foreach ($transactionsNew as &$transaction) {
            if ($transaction['itemCode'] !== null) {
                $campaign = $this->SalesforceService->campaignByPaypalItem($transaction['itemCode']);
                if ($campaign['totalSize'] !== 0) {
                    $transaction['campaignName'] = $campaign['records'][0]['Name'];
                    $transaction['campaignId'] = $campaign['records'][0]['Id'];
               }
            }
        }
        return [
            'transactions' => $transactionsNew,
        ];
    }

    /**
     * Filter all transactions and only return new/unknown ones
     *
     * @param $transactions
     * @param $payments
     * @return array
     */
    private function filterTransaction($transactions, $payments): array
    {
        foreach ($transactions as $key => &$transaction) {
            if (in_array($transaction['transactionId'], $payments)) {
                unset($transactions[$key]);
            }
        }
        return $transactions;
    }

    /**
     * create one dimensional transaction records with just the required fields
     *
     * @param $transactions
     * @return array
     */
    private function harmonizePaypalTransactions($transactions): array
    {
        $transactionsLined = array();
        foreach ($transactions as $transaction) {
            $transactionInfo = $transaction['transaction_info'];
            $line['transactionId'] = $transactionInfo['transaction_id'];
            $line['transactionType'] = $transactionInfo['transaction_event_code'];
            $line['transactionDate'] = $transactionInfo['transaction_initiation_date'];
            $line['transactionAmount'] = $transactionInfo['transaction_amount']['value'];
            $line['transactionFee'] = isset($transactionInfo['fee_amount']) ? ltrim($transactionInfo['fee_amount']['value'], '-') : null;
            $line['transactionNote'] = $transactionInfo['transaction_note'] ?? null;

            $payerInfo = $transaction['payer_info'];
            $line['payerEmail'] = $payerInfo['email_address'] ?? null;
            $line['payerGivenName'] = substr($payerInfo['payer_name']['given_name'],0,40) ?? null;
            $line['payerSurName'] = substr($payerInfo['payer_name']['surname'],0,40) ?? null;
            $line['payerAlternateName'] = $payerInfo['payer_name']['alternate_full_name'] ?? null;
            $line['payerIBAN'] = null;

            $itemInfo = $transaction['cart_info'] ?? null;
            if ($itemInfo) {
                $itemInfo = $itemInfo['item_details'][0] ?? null;
            }
            $line['itemCode'] = $itemInfo['item_code'] ?? null;
            $line['paymentMethod'] = 'Paypal';

            if ($line['transactionType'] !== 'T1105' && $line['transactionType'] !== 'T0400') {
                $transactionsLined[] = $line;
            }
        }
        return $transactionsLined;
    }

    /**
     * create one dimensional transaction records with just the required fields
     *
     * @param $transactions
     * @return array
     */
    private function harmonizeBankTransactions($transactions): array
    {
        $transactionsLined = array();
        foreach ($transactions as $transaction) {
            // de $row = str_getcsv($transaction, ';');
            $row = str_getcsv($transaction, ',', '"');

            //$this->logger->info('Data set raw: ' . json_encode($row));
            // de $date = explode('.', $row[0]);
            $date = explode('/', $row[0]);
            // de $date = $date[2].'-'.$date[1].'-'.$date[0];
            $date = $date[2].'-'.$date[0].'-'.$date[1];

            $nameArray = explode(' ', $row[3]);

            if (strpos($row[4], 'Mandate:') !== false) {
                $method = 'SEPA Lastschrift';
            } else {
                $method = 'Banküberweisung';
            }

            $line['transactionId'] = hash('md5', $row[0].$row[3].$row[4].$row[5].$row[7]);
            $line['transactionType'] = null;
            $line['transactionDate'] = date("Y-m-d", strtotime($date));
            $line['transactionAmount'] = str_replace(',', '.', $row[7]);
            $line['transactionFee'] = null;
            $line['transactionNote'] = $row[4];

            $line['payerEmail'] = null;
            $line['payerSurName'] = (int)$line['transactionAmount'] > 0 ? substr(array_pop($nameArray),0,40) : null;
            $line['payerGivenName'] = (int)$line['transactionAmount'] > 0 ? substr(implode(' ', $nameArray),0,40) : null;
            $line['payerAlternateName'] = $row[3];
            $line['payerIBAN'] = $row[5];

            $line['itemCode'] = null;
            $line['paymentMethod'] = $method;

            $transactionsLined[] = $line;
        }
        return $transactionsLined;
    }

    /**
     * exclude transactions which contain excluded payers
     *
     * @param $transactions
     * @return array
     */
    private function excludeBankTransactions($transactions): array
    {
        $bank = $this->StoreService->getSecureParameter('bank');
        $excludes = explode(';', $bank['excludes']);

        foreach ($transactions as $key => &$transaction) {
            if (in_array($transaction['payerAlternateName'], $excludes)) {
                unset($transactions[$key]);
            }
        }
        return $transactions;
    }

    /**
     * search for a matching text and replace the account name
     *
     * @param $transactions
     * @return array
     */
    private function replaceBankTransactions($transactions): array
    {
        $bank = $this->StoreService->getSecureParameter('bank');
        $searchText = $bank['searchText'];
        $replaceName = $bank['replaceName'];

        foreach ($transactions as &$transaction) {
            if ($transaction['transactionNote'] === $searchText) {
                $transaction['payerAlternateName'] = $replaceName;
            }
        }
        return $transactions;
    }
}