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
    private $NotificationManager;
    private $update = false;
    private $transactionsCount = 0;
    private $transactionsNewCount = 0;
    private $contactsNewCount = 0;
    private $opportunitiesNewCount = 0;
    private $opportunitiesUpdateCount = 0;

    public function __construct(
        LoggerInterface $logger,
        PaypalService $PaypalService,
        NotificationManager $NotificationManager,
        SalesforceService $SalesforceService
    )
    {
        $this->logger = $logger;
        $this->PaypalService = $PaypalService;
        $this->NotificationManager = $NotificationManager;
        $this->SalesforceService = $SalesforceService;
    }

    /**
     * start the compare process
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
    public function compare($update, $from, $to, $isBackgroundJob = false): array
    {
        $start = $from . ':00-0000';
        $end = $to . ':00-0000';

        if ($update === 'true') {
            $this->update = true;
        }

        // get paypal transactions
        $transactions = $this->PaypalService->transactions($start, $end, null);
        $transactionsLined = $this->harmonizeTransactions($transactions);
        $this->transactionsCount = count($transactionsLined);

        // get existing Saleforce payment IDs
        $transactionIds = array_column($transactionsLined, 'transactionId');
        $existingPayments = $this->SalesforceService->paymentsByReference($transactionIds);

        // compare => keep missing paypal transactions
        $transactionsNew = $this->filterTransaction($transactionsLined, $existingPayments);
        $this->transactionsNewCount = count($transactionsNew);

        // compare for existing contact
        // create missing contacts
        $validateContacts = $this->validateContacts($transactionsNew);
        $transactionsNew = $validateContacts['transactions'];

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
                $this->opportunitiesUpdateCount++;
                array_push($opportunitiesUpdate, $opportunityPledgeId['Name']);
                if ($this->update) {
                    $this->SalesforceService->opportunityPledgeUpdate($opportunityPledgeId['Id']);
                    $paymentId = $this->SalesforceService->paymentByOpportunityId($opportunityPledgeId['Id']);
                    $this->SalesforceService->paymentUpdateReference($paymentId, $transaction['transactionId']);
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
                        , $transaction['isNewContact']);
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
            $contact = $this->SalesforceService->contactSearch('Email', $transaction['payerEmail']);
            if ($contact['totalSize'] === 0) {
                $this->contactsNewCount++;
                array_push($contactsNew, $transaction['payerAlternateName'] . ' ' . $transaction['payerEmail']);
                if ($this->update) {
                    $newContact = $this->SalesforceService->contactCreate($transaction['payerGivenName'], $transaction['payerSurName'], $transaction['payerAlternateName'], $transaction['payerEmail']);
                    $transaction['contactId'] = $newContact['contactId'];
                    $transaction['accountId'] = $newContact['accountId'];
                    $transaction['isNewContact'] = true;
                } else {
                    // set dummy values to make the simulation of opportunities "not find" anything existing
                    $transaction['contactId'] = '000000000000000000';
                    $transaction['accountId'] = '000000000000000000';
                }
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
    private function harmonizeTransactions($transactions): array
    {
        $transactionsLined = array();
        foreach ($transactions as $transaction) {
            $transactionInfo = $transaction['transaction_info'];
            $line['transactionId'] = $transactionInfo['transaction_id'];
            $line['transactionType'] = $transactionInfo['transaction_event_code'];
            $line['transactionDate'] = $transactionInfo['transaction_initiation_date'];
            $line['transactionAmount'] = $transactionInfo['transaction_amount']['value'];
            $line['transactionFee'] = isset($transactionInfo['fee_amount']) ? ltrim($transactionInfo['fee_amount']['value'], '-') : null;
            // "transactionType": "T1105",

            $payerInfo = $transaction['payer_info'];
            $line['payerEmail'] = $payerInfo['email_address'] ?? null;
            $line['payerGivenName'] = $payerInfo['payer_name']['given_name'] ?? null;
            $line['payerSurName'] = $payerInfo['payer_name']['surname'] ?? null;
            $line['payerAlternateName'] = $payerInfo['payer_name']['alternate_full_name'] ?? null;

            if ($line['transactionType'] !== 'T1105' && $line['transactionType'] !== 'T0400') {
                array_push($transactionsLined, $line);
            }
        }
        return $transactionsLined;
    }
}