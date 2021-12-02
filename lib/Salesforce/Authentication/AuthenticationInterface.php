<?php

namespace OCA\SFbridge\Salesforce\Authentication;

interface AuthenticationInterface
{

    /**
     * @return mixed
     */
    public function getAccessToken();

    /**
     * @return mixed
     */
    public function getInstanceUrl();
}
