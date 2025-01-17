<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SFbridge\Service;

use GuzzleHttp\Exception\GuzzleException;
use OCA\SFbridge\Salesforce\Exception\SalesforceException;
use Psr\Log\LoggerInterface;
use OCA\SFbridge\Salesforce\Authentication\PasswordAuthentication;
use OCA\SFbridge\Salesforce\SalesforceFunctions;

class SalesforceService
{
    private $userId;
    private $logger;
    private $endPoint = 'https://login.salesforce.com/';
    private $instanceUrl;
    private $accessToken;
    private $StoreService;

    const APPLICATION = 'salesforce';

    public function __construct(
        $userId,
        LoggerInterface $logger,
        StoreService $StoreService
    )
    {
        $this->userId = $userId;
        $this->logger = $logger;
        $this->StoreService = $StoreService;
    }

    /**
     * initiate the authentication process
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function auth(): array
    {
        $parameter = $this->StoreService->getSecureParameter(self::APPLICATION);

        $salesforce = new PasswordAuthentication($parameter);
        $salesforce->setEndpoint($this->endPoint);
        $salesforce->authenticate();

        /* if you need access token or instance url */
        $accessToken = $salesforce->getAccessToken();
        $instanceUrl = $salesforce->getInstanceUrl();
        return ['accessToken' => $accessToken,
            'instanceUrl' => $instanceUrl
        ];
    }

    /**
     * check if the existing token is still valid and renew
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function authCheck()
    {
        $token = $this->StoreService->getSecureToken(self::APPLICATION);
        if ($token !== false) {
            $this->logger->debug('Salesforce token still valid');
            $this->accessToken = $token['accessToken'];
            $this->instanceUrl = $token['instanceUrl'];
        } else {
            $this->logger->debug('Salesforce token renew requested');
            $newToken = $this->auth();
            $validity = time() + (60 * 60 * 2);
            $this->StoreService->setSecureToken(self::APPLICATION, $newToken['accessToken'], $newToken['instanceUrl'], $validity);
            $this->accessToken = $newToken['accessToken'];
            $this->instanceUrl = $newToken['instanceUrl'];
        }
    }

    /**
     * create contact
     *
     * @param $givenName
     * @param $surName
     * @param $alternateName
     * @param $email
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function contactCreate($givenName, $surName, $alternateName, $email): array
    {
        if (!$givenName && !$surName) {
            // is corporate account
            $type = 'Corporate';
            $recordTypeId = '01209000001L14XAAS';
            $surName = $alternateName;
        } else {
            $type = 'Household';
            $recordTypeId = '01209000001L14WAAS';
            $alternateName = $alternateName . ' Haushalt';
        }

        $data = [
            'Name' => $alternateName,
            'Type' => $type,
            'RecordTypeId' => $recordTypeId
        ];
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $accountId = $salesforceFunctions->create('Account', $data);

        $data = [
            'AccountId' => $accountId,
            'FirstName' => $givenName,
            'LastName' => $surName,
            'Email' => $email,
        ];
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $contactId = $salesforceFunctions->create('Contact', $data);

        return [
            'accountId' => $accountId,
            'contactId' => $contactId
        ];
    }

    /**
     * get all contacts
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contactIndex()
    {

        $query = 'SELECT Id, AccountId, Name, FirstName, LastName FROM Contact LIMIT 100';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        return $salesforceFunctions->query($query);
    }

    /**
     * search contact by field & keyword
     *
     * @param $field
     * @param $keyword
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contactSearch($field, $keyword)
    {
        $query = 'SELECT Id, Name, AccountId FROM Contact WHERE ' . $field . ' = \'' . $keyword . '\'';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        return $salesforceFunctions->query($query);
    }

    /**
     * search opportunity by field & keyword
     *
     * @param $field
     * @param $keyword
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function opportunitySearch($field, $keyword)
    {
        $query = 'SELECT Id, OpportunityId, Amount FROM Opportunity WHERE ' . $field . ' = \'' . $keyword . '\'';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $data = $salesforceFunctions->query($query);
        return $data;
    }

    /**
     * search for open opportunities of type pledge
     *
     * @param $contactId
     * @param $amount
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function opportunityPledgeSearch($contactId, $amount)
    {
        // in case several pledges already exist for the future, the oldest one will be used
        $query = 'SELECT Id, Name FROM Opportunity WHERE StageName = \'Pledged\' AND ContactId = \'' . $contactId . '\' AND Amount = ' . $amount . ' ORDER BY CloseDate ASC';
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $opportunity = $salesforceFunctions->query($query);
        if ($opportunity['totalSize'] !== 0) {
            return $opportunity['records'][0];
        } else {
            return false;
        }
    }

    /**
     * update opportunity type pledge
     *
     * @param $id
     * @param $date
     * @return mixed
     * @throws GuzzleException
     * @throws SalesforceException
     */
    public function opportunityPledgeUpdate($id, $date)
    {
        // Update pledge to status Closed Won
        $data = [
            'StageName' => 'Closed Won',
            'CloseDate' => $date,
        ];
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $pledgeId = $salesforceFunctions->update('Opportunity', $id, $data);
        return $pledgeId;
    }

    /**
     * create opportunity
     *
     * @param $contactId
     * @param $name
     * @param $accountId
     * @param $amount
     * @param $fee
     * @param $date
     * @param $paypalId
     * @param $isNewContact
     * @param $campaignId
     * @param $transactionNote
     * @param $paymentMethod
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function opportunityCreate($contactId, $name, $accountId, $amount, $fee, $date, $paypalId, $isNewContact, $campaignId, $transactionNote, $paymentMethod)
    {

        $data = [
            'contactId' => $contactId,
            'name' => $name,
            'accountId' => $accountId,
            'amount' => $amount,
            'RecordTypeId' => '01209000001L14YAAS',
            'StageName' => 'Closed Won',
            'CloseDate' => $date,
            'Description' => $transactionNote
        ];
        if ($isNewContact && $amount > 0) {
            $data['npsp__Acknowledgment_Status__c'] = 'To Be Acknowledged';
            //$data['npsp__Acknowledgment_Date__c'] = $date;
        }

        if ($campaignId) {
            $data['CampaignId'] = $campaignId;
        }

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $opportunityId = $salesforceFunctions->create('Opportunity', $data);

        // Get PaymentId from OpportunityId; it is available for pre-created Opps like recurring
        $paymentId = $this->paymentByOpportunityId($opportunityId);

        if ($paymentId) {
            // payment is existing => update the Paypal reference number
            $this->paymentUpdateReference($paymentId, $paypalId, $paymentMethod);
            // Create GAU transaction with paypal fee for Opportunity
            $this->allocationCreate($opportunityId, $fee);
        } else {
            // payment not existing. must be an expense booking
            $this->paymentCreate($opportunityId, $paypalId, $amount, $date, $paymentMethod);
        }
        return $opportunityId;
    }

    /**
     * get payment by opportunity id
     *
     * @param $opportunityId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paymentByOpportunityId($opportunityId)
    {
        $query = 'SELECT Id FROM npe01__OppPayment__c WHERE npe01__Opportunity__c = \'' . $opportunityId . '\'';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $paymentId = $salesforceFunctions->query($query);
        if ($paymentId) {
            return $paymentId['records'][0]['Id'];
        } else {
            return false;
        }
    }

    /**
     * create allocation to track transaction fees
     *
     * @param $opportunityId
     * @param $fee
     * @return false|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function allocationCreate($opportunityId, $fee)
    {
        if ($fee) {
            $data = [
                'npsp__Amount__c' => $fee,
                'npsp__Opportunity__c' => $opportunityId,
                'npsp__General_Accounting_Unit__c' => 'a0e09000000Hdj5AAC',
            ];
            $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
            return $salesforceFunctions->create('npsp__Allocation__c', $data);
        }
        return false;
    }

    /**
     * create payment
     *
     * @param $opportunityId
     * @param $paypalId
     * @param $amount
     * @param $date
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    private function paymentCreate($opportunityId, $paypalId, $amount, $date, $method)
    {
        $data = [
            'npe01__Opportunity__c' => $opportunityId,
            'npe01__Check_Reference_Number__c' => $paypalId,
            'npe01__Payment_Method__c' => $method,
            'npe01__Payment_Amount__c' => $amount,
            'npe01__Paid__c' => 'true',
            'npe01__Payment_Date__c' => $date
        ];
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        return $salesforceFunctions->create('npe01__OppPayment__c', $data);
    }

    /**
     * update paypal transaction id in existing payment
     *
     * @param $paymentId
     * @param $referenceId
     * @param $method
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function paymentUpdateReference($paymentId, $referenceId, $method)
    {
        $data = [
            'npe01__Check_Reference_Number__c' => $referenceId,
            'npe01__Payment_Method__c' => $method,
        ];
        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        return $salesforceFunctions->update('npe01__OppPayment__c', $paymentId, $data);
    }

    /**
     * search payment by paypal transaction id
     * @param $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceAuthenticationException
     * @throws \OCA\SFbridge\Salesforce\Exception\SalesforceException
     */
    public function paymentsByReference($data)
    {
        $auth = $this->authCheck();
        $references = implode("','", $data);
        $query = 'SELECT Id, npe01__Check_Reference_Number__c FROM npe01__OppPayment__c WHERE npe01__Check_Reference_Number__c IN (\'' . $references . '\')';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        $paymentList = $salesforceFunctions->query($query);

        $paymentIds = array_column($paymentList['records'], 'npe01__Check_Reference_Number__c');
        return $paymentIds;
    }

    /**
     * search campains by paypal article item code
     *
     * @param $field
     * @param $keyword
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function campaignByPaypalItem($item)
    {
        $query = 'SELECT Id, Name FROM Campaign WHERE PaypalArtikelName__c = \'' . $item . '\'';

        $salesforceFunctions = new SalesforceFunctions($this->instanceUrl, $this->accessToken);
        return $salesforceFunctions->query($query);
    }

}