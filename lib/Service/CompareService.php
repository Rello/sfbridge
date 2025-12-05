<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Service;

use GuzzleHttp\Exception\GuzzleException;
use OCA\SFbridge\Notification\NotificationManager;
use OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException;
use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use OCP\AppFramework\Http;
use OCP\Security\RateLimiting\IRateLimitExceededException;
use Psr\Log\LoggerInterface;

class CompareService {
	private $logger;
	private $PaypalService;
	private $SalesforceService;
	private $StoreService;
	private $NotificationManager;
	private $TalkService;
	private $update = false;
	private $transactionsCount = 0;
	private $transactionsNewCount = 0;
	private $transactionsTotalAmount = 0;
	private $contactsNewCount = 0;
	private $opportunitiesNewCount = 0;
	private $opportunitiesUpdateCount = 0;
	private $campaignCount = 0;

	public function __construct(
		LoggerInterface     $logger,
		PaypalService       $PaypalService,
		NotificationManager $NotificationManager,
		SalesforceService   $SalesforceService,
		StoreService        $StoreService,
		TalkService         $TalkService
	) {
		$this->logger = $logger;
		$this->PaypalService = $PaypalService;
		$this->NotificationManager = $NotificationManager;
		$this->SalesforceService = $SalesforceService;
		$this->StoreService = $StoreService;
		$this->TalkService = $TalkService;
	}

	/**
	 * get paypal transactions and start the compare process
	 *
	 * @param $update
	 * @param $from
	 * @param $to
	 * @param bool $isBackgroundJob
	 * @return array|false
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
	 * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
	 * @throws IRateLimitExceededException
	 * @throws \Exception
	 */
	public function paypal($update, $from, $to, bool $isBackgroundJob = false): false|array {

		$start = $from . ':00-0000';
		$end = $to . ':00-0000';

		if ($update === 'true') {
			$this->update = true;
		}

		// get paypal transactions
		$transactions = $this->PaypalService->transactions($start, $end, null);
		$transactionsLined = $this->harmonizePaypalTransactions($transactions);
		$transactionsLined = $this->excludePaypalTransactions($transactionsLined);

		try {
			$return['content'] = $this->processTransactions($transactions, $transactionsLined, $isBackgroundJob);
			$return['status'] = Http::STATUS_OK;
		} catch (SalesforceException $e) {
			if ($isBackgroundJob) {
				$this->NotificationManager->triggerNotification(NotificationManager::ERROR, 0, 0, [
					'subject' => 0,
					'amount' => 0
				]);
			}
			$return['content'] = "exception: " . json_encode($e->getErrors());
			$return['status'] = $e->getCode();
		}

		return $return;
	}

	/**
	 * get paypal transactions and start the compare process
	 *
	 * @param $transactions
	 * @param $delimiter
	 * @param bool $isBackgroundJob
	 * @return array
	 * @throws GuzzleException
	 * @throws SalesforceAuthenticationException
	 * @throws SalesforceException
	 */
	public function bank($content, bool $isBackgroundJob = false) {
		$this->update = true;

		$transactions = str_getcsv($content, "\n");  // split rows
		$delimiter = $this->detectDelimiter($transactions[0]); // first row
		$transactions = array_slice($transactions, 1); // remove header

		$transactionsLined = $this->harmonizeBankTransactions($transactions, $delimiter);
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
	public function processTransactions($transactions, $transactionsLined, bool $isBackgroundJob = false): array {
		$this->transactionsCount = count($transactionsLined);

		// get existing Salesforce payment IDs
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

		// compare for existing opportunity
		// create opportunity
		$validateOpportunities = $this->validateOpportunities($transactionsNew);
		$transactionsNew = $validateOpportunities['transactions'];

		$processed = [
			'counts' => [
				'Transactions in timeframe' => $this->transactionsCount,
				'- new transactions' => $this->transactionsNewCount,
				'- new contacts' => $this->contactsNewCount,
				'- new opportunities' => $this->opportunitiesNewCount,
				'- updated pledges or opportunities' => $this->opportunitiesUpdateCount,
			],
			'all transactions' => $transactions,
			'new transactions' => $transactionsNew,
			'new contacts' => $validateContacts['contacts'],
			'new opportunities' => $validateOpportunities['opportunities'],
			'updated opportunities' => $validateOpportunities['opportunitiesUpdate'],
		];

		// Decision on when to notify where: Talk or Notifications
		if ($this->transactionsNewCount !== 0) {
			// New transaction is found
			if ($isBackgroundJob) {
				// Check was executed in background
				if (!$this->update) {
					// Background check only job, just do a notification
					$this->NotificationManager->triggerNotification(NotificationManager::NEW_TRANSACTION, 0, $this->transactionsNewCount, [
						'subject' => $this->transactionsNewCount,
						'amount' => $this->transactionsTotalAmount
					]);
				} else {
					// Background update job, do a notification if no talk is configured
					if (!$this->TalkService->isConfigures()) {
						$this->NotificationManager->triggerNotification(NotificationManager::NEW_TRANSACTION, 0, $this->transactionsNewCount, [
							'subject' => $this->transactionsNewCount,
							'amount' => $this->transactionsTotalAmount
						]);
					}
				}
			}

			if ($this->update && $this->TalkService->isConfigures()) {
				// when Talk is configured, post updated transactions in any mode
				$this->TalkService->postMessage($processed);
			}
		}

		// when an update is performed, remove all existing notifications for everyone
		if ($this->update) {
			$this->NotificationManager->clearNotifications(NotificationManager::NEW_TRANSACTION, 0);
		}

		return $processed;
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
	private function validateOpportunities($transactionsNew): array {
		$opportunitiesNew = $opportunitiesUpdate = array();

		foreach ($transactionsNew as $transaction) {
			// search for existing Opp: contactId & amount & status "Pledged"
			$opportunityPledgeId = $this->SalesforceService->opportunityPledgeSearch($transaction['contactId'], $transaction['transactionAmount']);

			if ($opportunityPledgeId) {
				// Opportunity is a recurring pledge. Update Status "Closed Won"
				$this->logger->debug('Pledge to be updated: ' . $opportunityPledgeId['Name'] . $opportunityPledgeId['Id']);
				$this->opportunitiesUpdateCount++;

				// plain texts for the info output
				$opportunitiesUpdate[] = $opportunityPledgeId['Name'];

				if ($this->update) {
					$this->SalesforceService->opportunityPledgeUpdate($opportunityPledgeId['Id'], $transaction['transactionDate']);
					$paymentId = $this->SalesforceService->paymentByOpportunityId($opportunityPledgeId['Id']);
					$this->SalesforceService->paymentUpdateReference($paymentId, $transaction['transactionId'], $transaction['paymentMethod']);
					$this->SalesforceService->allocationCreate($opportunityPledgeId['Id'], $transaction['transactionFee']);
				}
			} else {
				// create new Opp
				$this->opportunitiesNewCount++;

				// plain texts for the info output
				//$dateTime = new \DateTime($transaction['transactionDate']);
				//$readableFormat = $dateTime->format('d.m.Y H:i');
				$opportunitiesNew[] = $transaction['payerAlternateName'] . ': ' . $transaction['transactionAmount'] . ' ' . $transaction['transactionAmountCurrency'];

				if ($this->update) {
					$this->SalesforceService->opportunityCreate($transaction['contactId'], $transaction['payerAlternateName'], $transaction['accountId'], $transaction['transactionAmount'], $transaction['transactionFee'], $transaction['transactionDate'], $transaction['transactionId'], $transaction['isNewContact'], $transaction['campaignId'] ?? null, $transaction['transactionNote'], $transaction['paymentMethod']);
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
	private function validateContacts($transactionsNew): array {
		$contactsNew = array();
		foreach ($transactionsNew as &$transaction) {
			if ($transaction['payerEmail']) {
				$contact = $this->SalesforceService->contactSearch('Email', $transaction['payerEmail']);
			} else {
				$contact = $this->SalesforceService->contactSearch('Name', $transaction['payerAlternateName']);
				$this->logger->debug('Search by AlternateName: ' . $transaction['payerAlternateName']);
				$this->logger->debug('Number of matches: ' . $contact['totalSize']);

				if ($contact['totalSize'] === 0) {
					$contact = $this->SalesforceService->contactSearch('Bankaccountholder__c', $transaction['payerAlternateName']);
					$this->logger->debug('Search Bankaccount field by AlternateName: ' . $transaction['payerAlternateName']);
					$this->logger->debug('Number of matches: ' . $contact['totalSize']);
				}
			}

			// enable for testing
			// $contact['totalSize'] = 0;

			if ($contact['totalSize'] === 0) {
				$this->contactsNewCount++;
				if ($this->update) {
					$this->logger->debug('New Contact to be created: ' . $transaction['payerGivenName'] . '-' . $transaction['payerSurName'] . '-' . $transaction['payerAlternateName']);
					$newContact = $this->SalesforceService->contactCreate(
						$transaction['payerGivenName'],
						$transaction['payerSurName'],
						$transaction['payerAlternateName'],
						$transaction['payerEmail'],
						$transaction['addressStreet'],
						$transaction['addressCity'],
						$transaction['addressPostalCode'],
						$transaction['addressCountryCode']);
					$transaction['contactId'] = $newContact['contactId'];
					$transaction['accountId'] = $newContact['accountId'];
					$transaction['isNewContact'] = true;
				} else {
					// set dummy values to make the simulation of opportunities "not find" anything existing
					$transaction['contactId'] = '000000000000000000';
					$transaction['accountId'] = '000000000000000000';
				}
				// plain texts for the info output
				$contactsNew[] = $transaction['payerAlternateName'];
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
	private function getCampaigns($transactionsNew): array {
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
	private function filterTransaction($transactions, $payments): array {
		foreach ($transactions as $key => &$transaction) {
			if (in_array($transaction['transactionId'], $payments)) {
				unset($transactions[$key]);
			} else {
				$this->transactionsTotalAmount += $transaction['transactionAmount'];
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
	private function harmonizePaypalTransactions($transactions): array {
		$transactionsLined = array();
		foreach ($transactions as $transaction) {
			$transactionInfo = $transaction['transaction_info'];
			$line['transactionId'] = $transactionInfo['transaction_id'];
			$line['transactionType'] = $transactionInfo['transaction_event_code'];
			$line['transactionDate'] = $transactionInfo['transaction_initiation_date'];
			$line['transactionAmount'] = $transactionInfo['transaction_amount']['value'];
			$line['transactionAmountCurrency'] = $transactionInfo['transaction_amount']['currency_code'];
			$line['transactionFee'] = isset($transactionInfo['fee_amount']) ? ltrim($transactionInfo['fee_amount']['value'], '-') : null;
			$line['transactionNote'] = $transactionInfo['transaction_note'] ?? null;

			$payerInfo = $transaction['payer_info'];
			$line['payerEmail'] = $payerInfo['email_address'] ?? null;
			$line['payerGivenName'] = isset($payerInfo['payer_name']['given_name']) ? substr($payerInfo['payer_name']['given_name'], 0, 40) : null;
			$line['payerSurName'] = isset($payerInfo['payer_name']['surname']) ? substr($payerInfo['payer_name']['surname'], 0, 40) : null;
			$line['payerAlternateName'] = $payerInfo['payer_name']['alternate_full_name'] ?? null;
			$line['payerIBAN'] = null;

			$itemInfo = $transaction['cart_info'] ?? null;
			if ($itemInfo) {
				$itemInfo = $itemInfo['item_details'][0] ?? null;
			}
			$line['itemCode'] = $itemInfo['item_code'] ?? null;
			$line['paymentMethod'] = 'Paypal';

			$shippingInfo = $transaction['shipping_info'] ?? null;
			$line['addressStreet'] = $shippingInfo['address']['line1'] ?? null;
			$line['addressCity'] = $shippingInfo['address']['city'] ?? null;
			$line['addressPostalCode'] = $shippingInfo['address']['postal_code'] ?? null;

			$code = strtoupper($shippingInfo['address']['country_code'] ?? '');
			$countryMap = [
				'DE' => 'Deutschland',
				'AT' => 'Österreich',
			];
			$line['addressCountryName'] = $countryMap[$code] ?? $code;

			$transactionsLined[] = $line;
		}
		return $transactionsLined;
	}

	/**
	 * create one dimensional transaction records with just the required fields
	 *
	 * @param $transactions
	 * @param $delimiter
	 * @return array
	 */
	private function harmonizeBankTransactions($transactions, $delimiter): array {
		$transactionsLined = array();
		$this->logger->debug('Using data record delimiter: ' . $delimiter);
		foreach ($transactions as $transaction) {
			$row = str_getcsv($transaction, $delimiter, '"');

			//$this->logger->info('Data set raw: ' . json_encode($row));
			$dateDelimiter = $this->detectDelimiter($row[1]);
			//$this->logger->info('Using date delimiter: ' . $dateDelimiter);
			if ($dateDelimiter === '/') {
				// US date format
				//$this->logger->info('US date');
				$date = explode($dateDelimiter, $row[1]);
				$date = $date[2] . '-' . $date[0] . '-' . $date[1];
			} elseif ($dateDelimiter === '.') {
				// DE date format
				$date = explode($dateDelimiter, $row[1]);
				$date = $date[2] . '-' . $date[1] . '-' . $date[0];
				//$this->logger->info('German date');
			} else {
				throwException();
			}

			$date = date("Y-m-d", strtotime($date));
			$nameArray = explode(' ', $row[3]);

			if (str_contains($row[4], 'Mandate:')) {
				$method = 'SEPA Lastschrift';
			} else {
				$method = 'Banküberweisung';
			}

			$line['transactionId'] = hash('md5', $date . $row[3] . $row[4] . $row[5] . $row[7]);
			$line['transactionType'] = null;
			$line['transactionDate'] = $date;
			$line['transactionAmount'] = str_replace(',', '.', $row[7]);
			$line['transactionFee'] = null;
			$line['transactionNote'] = $row[4];

			$line['payerEmail'] = null;
			$line['payerSurName'] = (int)$line['transactionAmount'] > 0 ? substr(array_pop($nameArray), 0, 40) : null;
			$line['payerGivenName'] = (int)$line['transactionAmount'] > 0 ? substr(implode(' ', $nameArray), 0, 40) : null;
			$line['payerAlternateName'] = $row[3];
			$line['payerIBAN'] = $row[5];

			$line['itemCode'] = null;
			$line['paymentMethod'] = $method;

			$line['addressStreet'] = null;
			$line['addressCity'] = null;
			$line['addressPostalCode'] = null;
			$line['addressCountryCode'] = null;

			$transactionsLined[] = $line;
		}
		return $transactionsLined;
	}

	/**
	 * exclude transactions which contain excluded payers
	 *
	 * @param $transactions
	 * @return array
	 * @throws \Exception
	 */
	private function excludeBankTransactions($transactions): array {
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
	 * @throws \Exception
	 */
	private function replaceBankTransactions($transactions): array {
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

	/**
	 * exclude transactions which contain excluded transaction types
	 *
	 * @param $transactions
	 * @return array
	 * @throws \Exception
	 */
	private function excludePaypalTransactions($transactions): array {
		$bank = $this->StoreService->getSecureParameter('paypal');
		$excludes = explode(',', $bank['excludeTypes']);

		foreach ($transactions as $key => &$transaction) {
			if (in_array($transaction['transactionType'], $excludes)) {
				unset($transactions[$key]);
			}
		}
		return $transactions;
	}

	private function detectDelimiter($row): string {
		$delimiters = ["\t", ";", "|", ",", ".", "/"];
		$data_2 = array();
		$delimiter = $delimiters[0];
		foreach ($delimiters as $d) {
			$data_1 = str_getcsv($row, $d);
			if (sizeof($data_1) > sizeof($data_2)) {
				$delimiter = $d;
				$data_2 = $data_1;
			}
		}
		return $delimiter;
	}
}